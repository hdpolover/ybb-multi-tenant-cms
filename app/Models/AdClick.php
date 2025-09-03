<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\TenantAware;
use App\Traits\UuidPrimaryKey;

class AdClick extends Model
{
    use HasFactory, TenantAware, UuidPrimaryKey;

    protected $fillable = [
        'tenant_id',
        'ad_id',
        'impression_id',
        'ip_address',
        'user_agent',
        'page_url',
        'click_url',
        'device_info',
        'location_info',
        'clicked_at',
    ];

    protected $casts = [
        'device_info' => 'array',
        'location_info' => 'array',
        'clicked_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function impression(): BelongsTo
    {
        return $this->belongsTo(AdImpression::class);
    }

    /**
     * Scopes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('clicked_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('clicked_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('clicked_at', now()->month)
                    ->whereYear('clicked_at', now()->year);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('clicked_at', [$startDate, $endDate]);
    }

    /**
     * Analytics helpers
     */
    public static function getConversionRate($adId = null, $startDate = null, $endDate = null)
    {
        $impressionsQuery = AdImpression::query();
        $clicksQuery = static::query();
        
        if ($adId) {
            $impressionsQuery->where('ad_id', $adId);
            $clicksQuery->where('ad_id', $adId);
        }
        
        if ($startDate && $endDate) {
            $impressionsQuery->whereBetween('viewed_at', [$startDate, $endDate]);
            $clicksQuery->whereBetween('clicked_at', [$startDate, $endDate]);
        }
        
        $impressions = $impressionsQuery->count();
        $clicks = $clicksQuery->count();
        
        return $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
    }

    public static function getTopClickUrls($adId = null, $limit = 10)
    {
        $query = static::query();
        
        if ($adId) {
            $query->where('ad_id', $adId);
        }
        
        return $query->selectRaw('
            click_url,
            COUNT(*) as clicks
        ')
        ->whereNotNull('click_url')
        ->groupBy('click_url')
        ->orderBy('clicks', 'desc')
        ->limit($limit)
        ->get();
    }
}