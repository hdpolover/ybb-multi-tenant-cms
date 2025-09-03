<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Post;
use App\Models\Program;
use App\Models\Job;
use App\Models\Ad;
use App\Models\AdImpression;
use App\Models\AdClick;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Show system analytics overview
     */
    public function index(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        
        // System metrics
        $metrics = [
            'total_tenants' => Tenant::count(),
            'new_tenants' => Tenant::whereBetween('created_at', $dateRange)->count(),
            'total_users' => User::count(),
            'new_users' => User::whereBetween('created_at', $dateRange)->count(),
            'total_content' => Post::count() + Program::count() + Job::count(),
            'new_content' => $this->getNewContentCount($dateRange),
            'ad_performance' => $this->getAdPerformanceMetrics($dateRange),
        ];

        // Growth trends
        $tenantGrowth = $this->getTenantGrowthData($dateRange);
        $userGrowth = $this->getUserGrowthData($dateRange);
        $contentGrowth = $this->getContentGrowthData($dateRange);

        // Top performing tenants
        $topTenants = $this->getTopPerformingTenants();

        return view('network.analytics.index', compact(
            'metrics',
            'tenantGrowth',
            'userGrowth',
            'contentGrowth',
            'topTenants'
        ));
    }

    /**
     * Show tenant-specific analytics
     */
    public function tenants(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        
        // Get tenant analytics
        $tenantAnalytics = Tenant::with(['users', 'posts', 'programs', 'jobs'])
            ->withCount(['users', 'posts', 'programs', 'jobs'])
            ->get()
            ->map(function ($tenant) use ($dateRange) {
                return [
                    'tenant' => $tenant,
                    'stats' => [
                        'total_users' => $tenant->users_count,
                        'new_users' => $tenant->users()->whereBetween('created_at', $dateRange)->count(),
                        'total_content' => $tenant->posts_count + $tenant->programs_count + $tenant->jobs_count,
                        'new_content' => $this->getTenantNewContent($tenant, $dateRange),
                        'last_activity' => $this->getTenantLastActivity($tenant),
                    ]
                ];
            });

        return view('network.analytics.tenants', compact('tenantAnalytics'));
    }

    /**
     * Show system performance analytics
     */
    public function performance(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        
        // System performance metrics
        $performance = [
            'response_times' => $this->getAverageResponseTimes(),
            'error_rates' => $this->getErrorRates($dateRange),
            'database_performance' => $this->getDatabasePerformance(),
            'storage_metrics' => $this->getStorageMetrics(),
            'ad_performance' => $this->getDetailedAdPerformance($dateRange),
        ];

        return view('network.analytics.performance', compact('performance'));
    }

    /**
     * Get date range from request
     */
    private function getDateRange(Request $request): array
    {
        $period = $request->get('period', '30days');
        
        switch ($period) {
            case '7days':
                return [now()->subDays(7), now()];
            case '30days':
                return [now()->subDays(30), now()];
            case '90days':
                return [now()->subDays(90), now()];
            case '1year':
                return [now()->subYear(), now()];
            default:
                return [now()->subDays(30), now()];
        }
    }

    /**
     * Get new content count for date range
     */
    private function getNewContentCount(array $dateRange): int
    {
        $posts = Post::whereBetween('created_at', $dateRange)->count();
        $programs = Program::whereBetween('created_at', $dateRange)->count();
        $jobs = Job::whereBetween('created_at', $dateRange)->count();
        
        return $posts + $programs + $jobs;
    }

    /**
     * Get ad performance metrics
     */
    private function getAdPerformanceMetrics(array $dateRange): array
    {
        $impressions = AdImpression::whereBetween('viewed_at', $dateRange)->count();
        $clicks = AdClick::whereBetween('clicked_at', $dateRange)->count();
        
        return [
            'impressions' => $impressions,
            'clicks' => $clicks,
            'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
            'active_ads' => Ad::where('is_active', true)->count(),
        ];
    }

    /**
     * Get tenant growth data
     */
    private function getTenantGrowthData(array $dateRange): \Illuminate\Support\Collection
    {
        return Tenant::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get user growth data
     */
    private function getUserGrowthData(array $dateRange): \Illuminate\Support\Collection
    {
        return User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get content growth data
     */
    private function getContentGrowthData(array $dateRange): \Illuminate\Support\Collection
    {
        $posts = Post::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date');

        $programs = Program::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date');

        $jobs = Job::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date');

        // Combine all content types
        return collect()
            ->merge($posts->get())
            ->merge($programs->get())
            ->merge($jobs->get())
            ->groupBy('date')
            ->map(function ($items) {
                return [
                    'date' => $items->first()->date,
                    'count' => $items->sum('count')
                ];
            })
            ->values();
    }

    /**
     * Get top performing tenants
     */
    private function getTopPerformingTenants(): \Illuminate\Support\Collection
    {
        return Tenant::withCount(['users', 'posts', 'programs', 'jobs'])
            ->orderByDesc('users_count')
            ->limit(10)
            ->get();
    }

    /**
     * Get tenant new content count
     */
    private function getTenantNewContent(Tenant $tenant, array $dateRange): int
    {
        $posts = $tenant->posts()->whereBetween('created_at', $dateRange)->count();
        $programs = $tenant->programs()->whereBetween('created_at', $dateRange)->count();
        $jobs = $tenant->jobs()->whereBetween('created_at', $dateRange)->count();
        
        return $posts + $programs + $jobs;
    }

    /**
     * Get tenant last activity
     */
    private function getTenantLastActivity(Tenant $tenant): ?\Carbon\Carbon
    {
        $lastUser = $tenant->users()->latest()->first();
        $lastPost = $tenant->posts()->latest()->first();
        
        $dates = collect([$lastUser?->created_at, $lastPost?->created_at])
            ->filter()
            ->sort()
            ->last();
            
        return $dates;
    }

    /**
     * Mock methods for system performance (implement with actual monitoring)
     */
    private function getAverageResponseTimes(): array
    {
        return [
            'average' => 150, // ms
            'p95' => 300,
            'p99' => 500,
        ];
    }

    private function getErrorRates(array $dateRange): array
    {
        return [
            '4xx_errors' => 0.1, // %
            '5xx_errors' => 0.01,
            'total_requests' => 10000,
        ];
    }

    private function getDatabasePerformance(): array
    {
        return [
            'query_time' => 5.2, // ms average
            'slow_queries' => 3,
            'connections' => 15,
        ];
    }

    private function getStorageMetrics(): array
    {
        return [
            'total_size' => '2.5 GB',
            'media_size' => '1.8 GB',
            'database_size' => '0.7 GB',
            'growth_rate' => '5% per month',
        ];
    }

    private function getDetailedAdPerformance(array $dateRange): array
    {
        return [
            'top_performing_ads' => Ad::orderBy('click_rate', 'desc')->limit(5)->get(),
            'placement_performance' => $this->getPlacementPerformance($dateRange),
            'hourly_performance' => $this->getHourlyAdPerformance($dateRange),
        ];
    }

    private function getPlacementPerformance(array $dateRange): \Illuminate\Support\Collection
    {
        return Ad::selectRaw('placement, AVG(click_rate) as avg_ctr, SUM(current_impressions) as impressions, SUM(current_clicks) as clicks')
            ->groupBy('placement')
            ->get();
    }

    private function getHourlyAdPerformance(array $dateRange): \Illuminate\Support\Collection
    {
        return AdImpression::selectRaw('HOUR(viewed_at) as hour, COUNT(*) as impressions')
            ->whereBetween('viewed_at', $dateRange)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }
}