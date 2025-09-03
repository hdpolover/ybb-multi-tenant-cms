<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Program;
use App\Models\Job;
use App\Models\User;
use App\Models\Ad;
use App\Models\AdImpression;
use App\Models\AdClick;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard
     */
    public function index(Request $request)
    {
        // Get overview statistics
        $stats = [
            'posts' => Post::count(),
            'programs' => Program::count(),
            'jobs' => Job::count(),
            'users' => User::count(),
            'ads' => Ad::count(),
        ];

        // Get recent activity
        $recentPosts = Post::with('creator')
            ->latest()
            ->limit(5)
            ->get();

        $recentPrograms = Program::with('creator')
            ->latest()
            ->limit(5)
            ->get();

        $recentJobs = Job::with('creator')
            ->latest()
            ->limit(5)
            ->get();

        // Get ad performance
        $adStats = [
            'total_impressions' => AdImpression::count(),
            'total_clicks' => AdClick::count(),
            'active_ads' => Ad::where('is_active', true)->count(),
        ];

        $adStats['click_rate'] = $adStats['total_impressions'] > 0 
            ? ($adStats['total_clicks'] / $adStats['total_impressions']) * 100 
            : 0;

        // Get popular content (by views or impressions)
        $popularPosts = Post::orderBy('views', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentPosts',
            'recentPrograms',
            'recentJobs',
            'adStats',
            'popularPosts'
        ));
    }
}