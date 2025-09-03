<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Services\AdService;

class ProgramController extends Controller
{
    protected $adService;

    public function __construct(AdService $adService)
    {
        $this->adService = $adService;
    }

    /**
     * Display a listing of programs
     */
    public function index(Request $request)
    {
        $query = Program::published()->with(['media', 'terms']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category/term
        if ($request->has('category') && $request->category) {
            $query->whereHas('terms', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('program_type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('application_status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        switch ($sortBy) {
            case 'title':
                $query->orderBy('title', $sortOrder);
                break;
            case 'deadline':
                $query->orderBy('application_deadline', $sortOrder);
                break;
            case 'created':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }

        $programs = $query->paginate(12);

        // Get sidebar ads
        $sidebarAds = $this->adService->getAdsForPlacement('sidebar', [
            'url' => $request->path(),
            'post_type' => 'program',
            'categories' => $programs->pluck('terms.*.slug')->flatten()->unique()->toArray()
        ], 3);

        // Record ad impressions
        foreach ($sidebarAds as $ad) {
            $this->adService->recordImpression($ad, $request);
        }

        return view('programs.index', compact('programs', 'sidebarAds'));
    }

    /**
     * Display a specific program
     */
    public function show(Request $request, string $slug)
    {
        $program = Program::published()
            ->with(['media', 'terms', 'creator'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Get related programs
        $relatedPrograms = Program::published()
            ->where('id', '!=', $program->id)
            ->where(function ($query) use ($program) {
                // Same type or shared terms
                $query->where('program_type', $program->program_type)
                      ->orWhereHas('terms', function ($q) use ($program) {
                          $q->whereIn('id', $program->terms->pluck('id'));
                      });
            })
            ->limit(4)
            ->get();

        // Get ads for this page
        $contentAds = $this->adService->getAdsForPlacement('inline', [
            'url' => $request->path(),
            'post_type' => 'program',
            'categories' => $program->terms->pluck('slug')->toArray()
        ], 2);

        $sidebarAds = $this->adService->getAdsForPlacement('sidebar', [
            'url' => $request->path(),
            'post_type' => 'program',
            'categories' => $program->terms->pluck('slug')->toArray()
        ], 3);

        // Record ad impressions
        foreach ($contentAds as $ad) {
            $this->adService->recordImpression($ad, $request);
        }
        foreach ($sidebarAds as $ad) {
            $this->adService->recordImpression($ad, $request);
        }

        return view('programs.show', compact(
            'program',
            'relatedPrograms',
            'contentAds',
            'sidebarAds'
        ));
    }
}