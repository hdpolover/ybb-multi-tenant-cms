<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\TenantAware;
use App\Traits\UuidPrimaryKey;

class AdImpression extends Model
{
    use HasFactory, TenantAware, UuidPrimaryKey;

    protected $fillable = [
        'tenant_id',
        'ad_id',
        'ip_address',
        'user_agent',
        'page_url',
        'referrer',
        'device_info',
        'location_info',
        'viewed_at',
    ];

    protected $casts = [
        'device_info' => 'array',
        'location_info' => 'array',
        'viewed_at' => 'datetime',
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

    /**
     * Scopes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('viewed_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('viewed_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('viewed_at', now()->month)
                    ->whereYear('viewed_at', now()->year);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('viewed_at', [$startDate, $endDate]);
    }

    /**
     * Analytics helpers
     */
    public static function getHourlyStats($adId = null, $date = null)
    {
        $query = static::query();
        
        if ($adId) {
            $query->where('ad_id', $adId);
        }
        
        if ($date) {
            $query->whereDate('viewed_at', $date);
        } else {
            $query->whereDate('viewed_at', today());
        }
        
        return $query->selectRaw('
            HOUR(viewed_at) as hour,
            COUNT(*) as impressions
        ')
        ->groupBy('hour')
        ->orderBy('hour')
        ->get();
    }

    public static function getTopPages($adId = null, $limit = 10)
    {
        $query = static::query();
        
        if ($adId) {
            $query->where('ad_id', $adId);
        }
        
        return $query->selectRaw('
            page_url,
            COUNT(*) as impressions
        ')
        ->groupBy('page_url')
        ->orderBy('impressions', 'desc')
        ->limit($limit)
        ->get();
    }
}