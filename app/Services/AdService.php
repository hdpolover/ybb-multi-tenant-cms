<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\AdImpression;
use App\Models\AdClick;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class AdService
{
    /**
     * Get active ads for a specific placement
     */
    public function getAdsForPlacement(string $placement, array $context = [], int $limit = null): Collection
    {
        $ads = Ad::getActiveForPlacement($placement, $context);
        
        if ($limit) {
            $ads = $ads->take($limit);
        }
        
        return $ads;
    }

    /**
     * Record an ad impression
     */
    public function recordImpression(Ad $ad, Request $request = null, array $additionalData = []): AdImpression
    {
        $request = $request ?: request();
        
        $data = array_merge([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'page_url' => $request->fullUrl(),
            'referrer' => $request->header('referer'),
            'device_info' => $this->getDeviceInfo($request),
            'location_info' => $this->getLocationInfo($request),
            'viewed_at' => now(),
        ], $additionalData);

        return $ad->recordImpression($data);
    }

    /**
     * Record an ad click
     */
    public function recordClick(Ad $ad, Request $request = null, string $clickUrl = null, AdImpression $impression = null): AdClick
    {
        $request = $request ?: request();
        
        $data = [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'page_url' => $request->fullUrl(),
            'click_url' => $clickUrl,
            'device_info' => $this->getDeviceInfo($request),
            'location_info' => $this->getLocationInfo($request),
            'clicked_at' => now(),
        ];

        return $ad->recordClick($impression, $data);
    }

    /**
     * Get ads analytics for a specific period
     */
    public function getAnalytics(array $filters = []): array
    {
        $baseStats = Ad::getAnalytics($filters);
        
        // Get impression and click trends
        $impressionTrends = $this->getImpressionTrends($filters);
        $clickTrends = $this->getClickTrends($filters);
        $topPerformingAds = $this->getTopPerformingAds($filters);
        
        return [
            'overview' => $baseStats,
            'impression_trends' => $impressionTrends,
            'click_trends' => $clickTrends,
            'top_ads' => $topPerformingAds,
            'conversion_rates' => $this->getConversionRates($filters),
        ];
    }

    /**
     * Create a new ad
     */
    public function createAd(array $data): Ad
    {
        // Set default values
        $data = array_merge([
            'tenant_id' => tenant('id'),
            'is_active' => true,
            'priority' => 0,
            'current_impressions' => 0,
            'current_clicks' => 0,
            'click_rate' => 0.00,
            'status' => 'active',
            'created_by' => auth()->id(),
        ], $data);

        // Validate content based on ad type
        $data['content'] = $this->validateAndFormatContent($data['type'], $data['content'] ?? []);

        return Ad::create($data);
    }

    /**
     * Update an existing ad
     */
    public function updateAd(Ad $ad, array $data): Ad
    {
        if (isset($data['content']) && isset($data['type'])) {
            $data['content'] = $this->validateAndFormatContent($data['type'], $data['content']);
        }

        $data['updated_by'] = auth()->id();
        
        $ad->update($data);
        
        return $ad->fresh();
    }

    /**
     * Validate and format ad content based on type
     */
    private function validateAndFormatContent(string $type, array $content): array
    {
        switch ($type) {
            case 'banner':
                return $this->validateBannerContent($content);
            case 'popup':
                return $this->validatePopupContent($content);
            case 'sidebar':
                return $this->validateSidebarContent($content);
            case 'inline':
                return $this->validateInlineContent($content);
            case 'video':
                return $this->validateVideoContent($content);
            default:
                return $content;
        }
    }

    /**
     * Validate banner ad content
     */
    private function validateBannerContent(array $content): array
    {
        $required = ['image_url', 'link_url'];
        $optional = ['alt_text', 'title', 'width', 'height'];
        
        return $this->extractFields($content, $required, $optional);
    }

    /**
     * Validate popup ad content
     */
    private function validatePopupContent(array $content): array
    {
        $required = ['title', 'message'];
        $optional = ['image_url', 'button_text', 'button_url', 'delay', 'frequency'];
        
        return $this->extractFields($content, $required, $optional);
    }

    /**
     * Validate sidebar ad content
     */
    private function validateSidebarContent(array $content): array
    {
        $required = ['html'];
        $optional = ['css', 'js'];
        
        return $this->extractFields($content, $required, $optional);
    }

    /**
     * Validate inline ad content
     */
    private function validateInlineContent(array $content): array
    {
        $required = ['html'];
        $optional = ['css', 'position']; // position: after_paragraph_1, after_paragraph_3, etc.
        
        return $this->extractFields($content, $required, $optional);
    }

    /**
     * Validate video ad content
     */
    private function validateVideoContent(array $content): array
    {
        $required = ['video_url'];
        $optional = ['poster_url', 'autoplay', 'controls', 'width', 'height'];
        
        return $this->extractFields($content, $required, $optional);
    }

    /**
     * Extract required and optional fields from content
     */
    private function extractFields(array $content, array $required, array $optional = []): array
    {
        $result = [];
        
        // Add required fields
        foreach ($required as $field) {
            if (!isset($content[$field])) {
                throw new \InvalidArgumentException("Required field '{$field}' is missing");
            }
            $result[$field] = $content[$field];
        }
        
        // Add optional fields if present
        foreach ($optional as $field) {
            if (isset($content[$field])) {
                $result[$field] = $content[$field];
            }
        }
        
        return $result;
    }

    /**
     * Get device information from request
     */
    private function getDeviceInfo(Request $request): array
    {
        $userAgent = $request->userAgent();
        
        return [
            'user_agent' => $userAgent,
            'is_mobile' => $this->isMobile($userAgent),
            'is_tablet' => $this->isTablet($userAgent),
            'is_desktop' => $this->isDesktop($userAgent),
            'browser' => $this->getBrowser($userAgent),
            'os' => $this->getOperatingSystem($userAgent),
        ];
    }

    /**
     * Get location information (basic implementation)
     */
    private function getLocationInfo(Request $request): array
    {
        // Basic implementation - in production, you might use a GeoIP service
        return [
            'ip' => $request->ip(),
            'country' => null,
            'city' => null,
            'timezone' => config('app.timezone'),
        ];
    }

    /**
     * Get impression trends
     */
    private function getImpressionTrends(array $filters): Collection
    {
        $query = AdImpression::query();
        
        if (isset($filters['date_from'])) {
            $query->where('viewed_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('viewed_at', '<=', $filters['date_to']);
        }
        
        return $query->selectRaw('DATE(viewed_at) as date, COUNT(*) as impressions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get click trends
     */
    private function getClickTrends(array $filters): Collection
    {
        $query = AdClick::query();
        
        if (isset($filters['date_from'])) {
            $query->where('clicked_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('clicked_at', '<=', $filters['date_to']);
        }
        
        return $query->selectRaw('DATE(clicked_at) as date, COUNT(*) as clicks')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get top performing ads
     */
    private function getTopPerformingAds(array $filters, int $limit = 10): Collection
    {
        $query = Ad::query();
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        return $query->orderBy('click_rate', 'desc')
            ->orderBy('current_clicks', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get conversion rates by placement
     */
    private function getConversionRates(array $filters): Collection
    {
        $query = Ad::query();
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        return $query->selectRaw('
            placement,
            SUM(current_impressions) as total_impressions,
            SUM(current_clicks) as total_clicks,
            AVG(click_rate) as avg_click_rate
        ')
        ->groupBy('placement')
        ->get();
    }

    // Simple device detection methods (you might want to use a more sophisticated library)
    private function isMobile(string $userAgent): bool
    {
        return preg_match('/Mobile|Android|iPhone|iPad/', $userAgent);
    }

    private function isTablet(string $userAgent): bool
    {
        return preg_match('/iPad|Tablet/', $userAgent);
    }

    private function isDesktop(string $userAgent): bool
    {
        return !$this->isMobile($userAgent) && !$this->isTablet($userAgent);
    }

    private function getBrowser(string $userAgent): string
    {
        if (preg_match('/Chrome/', $userAgent)) return 'Chrome';
        if (preg_match('/Firefox/', $userAgent)) return 'Firefox';
        if (preg_match('/Safari/', $userAgent)) return 'Safari';
        if (preg_match('/Edge/', $userAgent)) return 'Edge';
        return 'Other';
    }

    private function getOperatingSystem(string $userAgent): string
    {
        if (preg_match('/Windows/', $userAgent)) return 'Windows';
        if (preg_match('/Mac/', $userAgent)) return 'macOS';
        if (preg_match('/Linux/', $userAgent)) return 'Linux';
        if (preg_match('/Android/', $userAgent)) return 'Android';
        if (preg_match('/iOS/', $userAgent)) return 'iOS';
        return 'Other';
    }
}