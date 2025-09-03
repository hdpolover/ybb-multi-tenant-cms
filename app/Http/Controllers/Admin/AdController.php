<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Services\AdService;
use Illuminate\Http\Request;

class AdController extends Controller
{
    protected $adService;

    public function __construct(AdService $adService)
    {
        $this->adService = $adService;
    }

    /**
     * Display a listing of ads
     */
    public function index(Request $request)
    {
        $query = Ad::with(['creator', 'updater'])->latest();

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by placement
        if ($request->has('placement') && $request->placement) {
            $query->where('placement', $request->placement);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $ads = $query->paginate(20);

        return view('admin.ads.index', compact('ads'));
    }

    /**
     * Show the form for creating a new ad
     */
    public function create()
    {
        return view('admin.ads.create');
    }

    /**
     * Store a newly created ad
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:banner,popup,sidebar,inline,video',
            'placement' => 'required|string',
            'content' => 'required|array',
            'priority' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'max_impressions' => 'nullable|integer|min:1',
            'max_clicks' => 'nullable|integer|min:1',
            'targeting' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        $ad = $this->adService->createAd($request->all());

        return redirect()->route('admin.ads.index')
            ->with('success', 'Ad created successfully.');
    }

    /**
     * Display the specified ad
     */
    public function show(Ad $ad)
    {
        $ad->load(['creator', 'updater']);
        
        // Get recent analytics
        $analytics = $this->adService->getAnalytics([
            'ad_id' => $ad->id,
            'date_from' => now()->subDays(30),
            'date_to' => now(),
        ]);

        return view('admin.ads.show', compact('ad', 'analytics'));
    }

    /**
     * Show the form for editing the specified ad
     */
    public function edit(Ad $ad)
    {
        return view('admin.ads.edit', compact('ad'));
    }

    /**
     * Update the specified ad
     */
    public function update(Request $request, Ad $ad)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:banner,popup,sidebar,inline,video',
            'placement' => 'required|string',
            'content' => 'required|array',
            'priority' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'max_impressions' => 'nullable|integer|min:1',
            'max_clicks' => 'nullable|integer|min:1',
            'targeting' => 'nullable|array',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $ad = $this->adService->updateAd($ad, $request->all());

        return redirect()->route('admin.ads.index')
            ->with('success', 'Ad updated successfully.');
    }

    /**
     * Remove the specified ad
     */
    public function destroy(Ad $ad)
    {
        $ad->delete();

        return redirect()->route('admin.ads.index')
            ->with('success', 'Ad deleted successfully.');
    }

    /**
     * Toggle ad status
     */
    public function toggle(Ad $ad)
    {
        $ad->update(['is_active' => !$ad->is_active]);

        $status = $ad->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Ad {$status} successfully.");
    }

    /**
     * Get ad analytics
     */
    public function analytics(Request $request, Ad $ad)
    {
        $filters = [
            'ad_id' => $ad->id,
            'date_from' => $request->get('date_from', now()->subDays(30)),
            'date_to' => $request->get('date_to', now()),
        ];

        $analytics = $this->adService->getAnalytics($filters);

        if ($request->expectsJson()) {
            return response()->json($analytics);
        }

        return view('admin.ads.analytics', compact('ad', 'analytics'));
    }
}