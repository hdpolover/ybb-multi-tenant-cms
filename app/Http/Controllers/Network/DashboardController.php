<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Admin;
use App\Models\User;
use App\Models\Post;
use App\Models\Program;
use App\Models\Job;
use App\Models\Ad;
use App\Models\AdImpression;
use App\Models\AdClick;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the network admin dashboard
     */
    public function index(Request $request)
    {
        // System-wide statistics
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'total_users' => User::count(),
            'total_admins' => Admin::count(),
            'total_content' => Post::count() + Program::count() + Job::count(),
            'total_ads' => Ad::count(),
        ];

        // Tenant growth over time (last 12 months)
        $tenantGrowth = Tenant::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Recent tenant activity
        $recentTenants = Tenant::with(['users' => function($query) {
                $query->latest()->limit(1);
            }])
            ->latest()
            ->limit(10)
            ->get();

        // System performance metrics
        $systemMetrics = [
            'total_impressions' => AdImpression::count(),
            'total_clicks' => AdClick::count(),
            'avg_click_rate' => $this->calculateSystemClickRate(),
            'active_ads' => Ad::where('is_active', true)->count(),
        ];

        // Top performing tenants
        $topTenants = Tenant::withCount(['users', 'posts', 'programs', 'jobs'])
            ->orderBy('users_count', 'desc')
            ->limit(5)
            ->get();

        // System health indicators
        $healthIndicators = $this->getSystemHealthIndicators();

        return view('network.dashboard', compact(
            'stats',
            'tenantGrowth',
            'recentTenants',
            'systemMetrics',
            'topTenants',
            'healthIndicators'
        ));
    }

    /**
     * Calculate system-wide click rate
     */
    private function calculateSystemClickRate(): float
    {
        $totalImpressions = AdImpression::count();
        $totalClicks = AdClick::count();
        
        return $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;
    }

    /**
     * Get system health indicators
     */
    private function getSystemHealthIndicators(): array
    {
        return [
            'storage_usage' => $this->getStorageUsage(),
            'database_size' => $this->getDatabaseSize(),
            'recent_errors' => $this->getRecentErrorCount(),
            'uptime' => $this->getSystemUptime(),
        ];
    }

    /**
     * Get storage usage (mock implementation)
     */
    private function getStorageUsage(): array
    {
        // In production, you'd calculate actual storage usage
        return [
            'used' => '2.3 GB',
            'total' => '100 GB',
            'percentage' => 2.3,
        ];
    }

    /**
     * Get database size (mock implementation)
     */
    private function getDatabaseSize(): string
    {
        // In production, you'd query actual database size
        return '156 MB';
    }

    /**
     * Get recent error count (mock implementation)
     */
    private function getRecentErrorCount(): int
    {
        // In production, you'd check logs or monitoring system
        return 0;
    }

    /**
     * Get system uptime (mock implementation)
     */
    private function getSystemUptime(): string
    {
        // In production, you'd calculate actual uptime
        return '99.9%';
    }
}