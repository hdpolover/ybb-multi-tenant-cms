<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Program;
use App\Models\Job;
use App\Services\AdService;

class HomeController extends Controller
{
    protected $adService;

    public function __construct(AdService $adService)
    {
        $this->adService = $adService;
    }

    /**
     * Display the home page
     */
    public function index(Request $request)
    {
        // Get featured content
        $featuredPrograms = Program::published()
            ->featured()
            ->latest()
            ->limit(6)
            ->get();

        $featuredJobs = Job::published()
            ->featured()
            ->latest()
            ->limit(6)
            ->get();

        // Get recent posts/news
        $recentPosts = Post::published()
            ->where('type', 'post')
            ->latest()
            ->limit(8)
            ->get();

        // Get ads for various placements
        $headerAds = $this->adService->getAdsForPlacement('header', [
            'url' => $request->path(),
            'post_type' => 'home'
        ], 1);

        $sidebarAds = $this->adService->getAdsForPlacement('sidebar', [
            'url' => $request->path(),
            'post_type' => 'home'
        ], 3);

        $footerAds = $this->adService->getAdsForPlacement('footer', [
            'url' => $request->path(),
            'post_type' => 'home'
        ], 1);

        // Record ad impressions
        foreach ($headerAds as $ad) {
            $this->adService->recordImpression($ad, $request);
        }
        foreach ($sidebarAds as $ad) {
            $this->adService->recordImpression($ad, $request);
        }
        foreach ($footerAds as $ad) {
            $this->adService->recordImpression($ad, $request);
        }

        return view('home', compact(
            'featuredPrograms',
            'featuredJobs',
            'recentPosts',
            'headerAds',
            'sidebarAds',
            'footerAds'
        ));
    }
}