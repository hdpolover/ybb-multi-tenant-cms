<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Job;
use App\Services\AdService;

class JobController extends Controller
{
    protected $adService;

    public function __construct(AdService $adService)
    {
        $this->adService = $adService;
    }

    /**
     * Display a listing of jobs
     */
    public function index(Request $request)
    {
        $query = Job::published()->with(['media', 'terms']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%')
                  ->orWhere('company_name', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by location
        if ($request->has('location') && $request->location) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Filter by job type
        if ($request->has('type') && $request->type) {
            $query->where('job_type', $request->type);
        }

        // Filter by experience level
        if ($request->has('experience') && $request->experience) {
            $query->where('experience_level', $request->experience);
        }

        // Filter by remote work
        if ($request->has('remote') && $request->remote) {
            $query->where('is_remote', true);
        }

        // Filter by salary range
        if ($request->has('min_salary') && $request->min_salary) {
            $query->where('salary_min', '>=', $request->min_salary);
        }
        if ($request->has('max_salary') && $request->max_salary) {
            $query->where('salary_max', '<=', $request->max_salary);
        }

        // Filter by category/term
        if ($request->has('category') && $request->category) {
            $query->whereHas('terms', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        switch ($sortBy) {
            case 'title':
                $query->orderBy('title', $sortOrder);
                break;
            case 'company':
                $query->orderBy('company_name', $sortOrder);
                break;
            case 'deadline':
                $query->orderBy('application_deadline', $sortOrder);
                break;
            case 'salary':
                $query->orderBy('salary_max', $sortOrder);
                break;
            case 'created':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }

        $jobs = $query->paginate(12);

        // Get sidebar ads
        $sidebarAds = $this->adService->getAdsForPlacement('sidebar', [
            'url' => $request->path(),
            'post_type' => 'job',
            'categories' => $jobs->pluck('terms.*.slug')->flatten()->unique()->toArray()
        ], 3);

        // Record ad impressions
        foreach ($sidebarAds as $ad) {
            $this->adService->recordImpression($ad, $request);
        }

        return view('jobs.index', compact('jobs', 'sidebarAds'));
    }

    /**
     * Display a specific job
     */
    public function show(Request $request, string $slug)
    {
        $job = Job::published()
            ->with(['media', 'terms', 'creator'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Get related jobs
        $relatedJobs = Job::published()
            ->where('id', '!=', $job->id)
            ->where(function ($query) use ($job) {
                // Same company, location, or shared terms
                $query->where('company_name', $job->company_name)
                      ->orWhere('location', $job->location)
                      ->orWhere('job_type', $job->job_type)
                      ->orWhereHas('terms', function ($q) use ($job) {
                          $q->whereIn('id', $job->terms->pluck('id'));
                      });
            })
            ->limit(4)
            ->get();

        // Get ads for this page
        $contentAds = $this->adService->getAdsForPlacement('inline', [
            'url' => $request->path(),
            'post_type' => 'job',
            'categories' => $job->terms->pluck('slug')->toArray()
        ], 2);

        $sidebarAds = $this->adService->getAdsForPlacement('sidebar', [
            'url' => $request->path(),
            'post_type' => 'job',
            'categories' => $job->terms->pluck('slug')->toArray()
        ], 3);

        // Record ad impressions
        foreach ($contentAds as $ad) {
            $this->adService->recordImpression($ad, $request);
        }
        foreach ($sidebarAds as $ad) {
            $this->adService->recordImpression($ad, $request);
        }

        return view('jobs.show', compact(
            'job',
            'relatedJobs',
            'contentAds',
            'sidebarAds'
        ));
    }
}