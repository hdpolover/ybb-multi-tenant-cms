<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Services\AdService;

class PageController extends Controller
{
    protected $adService;

    public function __construct(AdService $adService)
    {
        $this->adService = $adService;
    }

    /**
     * Display a specific page/post by slug
     */
    public function show(Request $request, string $slug)
    {
        $post = Post::published()
            ->with(['media', 'terms', 'creator'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Get related posts
        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->where('type', $post->type)
            ->whereHas('terms', function ($q) use ($post) {
                $q->whereIn('id', $post->terms->pluck('id'));
            })
            ->limit(4)
            ->get();

        // Get ads for this page
        $contentAds = $this->adService->getAdsForPlacement('inline', [
            'url' => $request->path(),
            'post_type' => $post->type,
            'categories' => $post->terms->pluck('slug')->toArray()
        ], 2);

        $sidebarAds = $this->adService->getAdsForPlacement('sidebar', [
            'url' => $request->path(),
            'post_type' => $post->type,
            'categories' => $post->terms->pluck('slug')->toArray()
        ], 3);

        $footerAds = $this->adService->getAdsForPlacement('footer', [
            'url' => $request->path(),
            'post_type' => $post->type,
            'categories' => $post->terms->pluck('slug')->toArray()
        ], 1);

        // Record ad impressions
        foreach ($contentAds as $ad) {
            $this->adService->recordImpression($ad, $request);
        }
        foreach ($sidebarAds as $ad) {
            $this->adService->recordImpression($ad, $request);
        }
        foreach ($footerAds as $ad) {
            $this->adService->recordImpression($ad, $request);
        }

        // Determine the view based on post type
        $viewName = $this->getViewForPostType($post->type);

        return view($viewName, compact(
            'post',
            'relatedPosts',
            'contentAds',
            'sidebarAds',
            'footerAds'
        ));
    }

    /**
     * Get the appropriate view name for a post type
     */
    private function getViewForPostType(string $type): string
    {
        switch ($type) {
            case 'page':
                return 'pages.show';
            case 'news':
                return 'news.show';
            case 'guide':
                return 'guides.show';
            case 'post':
            default:
                return 'posts.show';
        }
    }
}