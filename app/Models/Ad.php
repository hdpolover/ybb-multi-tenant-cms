<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\TenantAware;
use App\Traits\UuidPrimaryKey;
use Carbon\Carbon;

class Ad extends Model
{
    use HasFactory, TenantAware, UuidPrimaryKey;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'type',
        'placement',
        'content',
        'targeting',
        'settings',
        'is_active',
        'priority',
        'start_date',
        'end_date',
        'max_impressions',
        'max_clicks',
        'current_impressions',
        'current_clicks',
        'click_rate',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'content' => 'array',
        'targeting' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'max_impressions' => 'integer',
        'max_clicks' => 'integer',
        'current_impressions' => 'integer',
        'current_clicks' => 'integer',
        'click_rate' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::updating(function ($ad) {
            // Recalculate click rate when impressions or clicks change
            if ($ad->isDirty(['current_impressions', 'current_clicks'])) {
                $ad->click_rate = $ad->current_impressions > 0 
                    ? ($ad->current_clicks / $ad->current_impressions) * 100 
                    : 0;
            }
            
            // Update status based on limits and dates
            $ad->updateStatus();
        });
    }

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function impressions(): HasMany
    {
        return $this->hasMany(AdImpression::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(AdClick::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    public function scopeForPlacement($query, string $placement)
    {
        return $query->where('placement', $placement);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    public function scopeWithinLimits($query)
    {
        return $query->where(function ($q) {
                        $q->whereNull('max_impressions')
                          ->orWhereColumn('current_impressions', '<', 'max_impressions');
                    })
                    ->where(function ($q) {
                        $q->whereNull('max_clicks')
                          ->orWhereColumn('current_clicks', '<', 'max_clicks');
                    });
    }

    /**
     * Helper Methods
     */
    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function hasReachedImpressionLimit(): bool
    {
        return $this->max_impressions && $this->current_impressions >= $this->max_impressions;
    }

    public function hasReachedClickLimit(): bool
    {
        return $this->max_clicks && $this->current_clicks >= $this->max_clicks;
    }

    public function isScheduled(): bool
    {
        return $this->start_date && $this->start_date->isFuture();
    }

    public function canBeDisplayed(): bool
    {
        return $this->is_active 
            && $this->status === 'active'
            && !$this->isExpired()
            && !$this->hasReachedImpressionLimit()
            && !$this->hasReachedClickLimit()
            && !$this->isScheduled();
    }

    public function updateStatus(): void
    {
        if ($this->isExpired()) {
            $this->status = 'expired';
        } elseif ($this->hasReachedImpressionLimit() || $this->hasReachedClickLimit()) {
            $this->status = 'completed';
        } elseif ($this->isScheduled()) {
            $this->status = 'scheduled';
        } elseif ($this->is_active) {
            $this->status = 'active';
        } else {
            $this->status = 'paused';
        }
    }

    public function recordImpression(array $data = []): AdImpression
    {
        $impression = $this->impressions()->create(array_merge([
            'tenant_id' => $this->tenant_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'page_url' => request()->fullUrl(),
            'referrer' => request()->header('referer'),
            'viewed_at' => now(),
        ], $data));

        $this->increment('current_impressions');
        
        return $impression;
    }

    public function recordClick(?AdImpression $impression = null, array $data = []): AdClick
    {
        $click = $this->clicks()->create(array_merge([
            'tenant_id' => $this->tenant_id,
            'impression_id' => $impression?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'page_url' => request()->fullUrl(),
            'clicked_at' => now(),
        ], $data));

        $this->increment('current_clicks');
        
        return $click;
    }

    public function matchesTargeting(array $context = []): bool
    {
        if (!$this->targeting) {
            return true;
        }

        // URL pattern matching
        if (isset($this->targeting['url_patterns'])) {
            $currentUrl = $context['url'] ?? request()->path();
            foreach ($this->targeting['url_patterns'] as $pattern) {
                if (fnmatch($pattern, $currentUrl)) {
                    return true;
                }
            }
            return false;
        }

        // Post type targeting
        if (isset($this->targeting['post_types']) && isset($context['post_type'])) {
            return in_array($context['post_type'], $this->targeting['post_types']);
        }

        // Category targeting
        if (isset($this->targeting['categories']) && isset($context['categories'])) {
            return !empty(array_intersect($this->targeting['categories'], $context['categories']));
        }

        return true;
    }

    /**
     * Static helpers
     */
    public static function getActiveForPlacement(string $placement, array $context = [])
    {
        return static::active()
            ->forPlacement($placement)
            ->withinLimits()
            ->byPriority()
            ->get()
            ->filter(fn($ad) => $ad->matchesTargeting($context));
    }

    public static function getAnalytics(array $filters = [])
    {
        $query = static::query();
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        return $query->selectRaw('
            COUNT(*) as total_ads,
            SUM(current_impressions) as total_impressions,
            SUM(current_clicks) as total_clicks,
            AVG(click_rate) as avg_click_rate,
            COUNT(CASE WHEN status = "active" THEN 1 END) as active_ads,
            COUNT(CASE WHEN status = "paused" THEN 1 END) as paused_ads,
            COUNT(CASE WHEN status = "expired" THEN 1 END) as expired_ads,
            COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_ads
        ')->first();
    }
}