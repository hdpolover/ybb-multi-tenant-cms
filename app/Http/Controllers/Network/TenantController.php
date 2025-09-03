<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Display a listing of tenants
     */
    public function index(Request $request)
    {
        $query = Tenant::with(['users' => function($q) {
            $q->latest()->limit(1);
        }])->withCount(['users', 'posts', 'programs', 'jobs']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('domain', 'like', '%' . $request->search . '%')
                  ->orWhere('subdomain', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tenants = $query->paginate(20);

        return view('network.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant
     */
    public function create()
    {
        return view('network.tenants.create');
    }

    /**
     * Store a newly created tenant
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants,domain',
            'subdomain' => 'nullable|string|max:255|unique:tenants,subdomain',
            'description' => 'nullable|string',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string',
            'settings' => 'nullable|array',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();

        $tenant = $this->tenantService->createTenant($data);

        return redirect()->route('network.tenants.index')
            ->with('success', 'Tenant created successfully.');
    }

    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['users', 'posts', 'programs', 'jobs']);
        
        // Get tenant statistics
        $stats = [
            'total_users' => $tenant->users()->count(),
            'total_posts' => $tenant->posts()->count(),
            'total_programs' => $tenant->programs()->count(),
            'total_jobs' => $tenant->jobs()->count(),
            'recent_activity' => $this->getRecentActivity($tenant),
        ];

        return view('network.tenants.show', compact('tenant', 'stats'));
    }

    /**
     * Show the form for editing the specified tenant
     */
    public function edit(Tenant $tenant)
    {
        return view('network.tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants,domain,' . $tenant->id,
            'subdomain' => 'nullable|string|max:255|unique:tenants,subdomain,' . $tenant->id,
            'description' => 'nullable|string',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string',
            'settings' => 'nullable|array',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $data = $request->all();
        $data['updated_by'] = auth()->id();

        $tenant = $this->tenantService->updateTenant($tenant, $data);

        return redirect()->route('network.tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    /**
     * Remove the specified tenant
     */
    public function destroy(Tenant $tenant)
    {
        if ($tenant->status === 'active') {
            return redirect()->back()
                ->with('error', 'Cannot delete an active tenant. Please suspend it first.');
        }

        $this->tenantService->deleteTenant($tenant);

        return redirect()->route('network.tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }

    /**
     * Toggle tenant status
     */
    public function toggle(Tenant $tenant)
    {
        $newStatus = $tenant->status === 'active' ? 'inactive' : 'active';
        
        $tenant->update([
            'status' => $newStatus,
            'updated_by' => auth()->id(),
        ]);

        $action = $newStatus === 'active' ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Tenant {$action} successfully.");
    }

    /**
     * Suspend tenant
     */
    public function suspend(Tenant $tenant)
    {
        $tenant->update([
            'status' => 'suspended',
            'updated_by' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', 'Tenant suspended successfully.');
    }

    /**
     * Get recent activity for a tenant
     */
    private function getRecentActivity(Tenant $tenant): array
    {
        $activities = [];

        // Recent posts
        $recentPosts = $tenant->posts()->latest()->limit(5)->get();
        foreach ($recentPosts as $post) {
            $activities[] = [
                'type' => 'post',
                'title' => $post->title,
                'date' => $post->created_at,
                'user' => $post->creator->name ?? 'Unknown',
            ];
        }

        // Recent users
        $recentUsers = $tenant->users()->latest()->limit(5)->get();
        foreach ($recentUsers as $user) {
            $activities[] = [
                'type' => 'user_registration',
                'title' => "New user: {$user->name}",
                'date' => $user->created_at,
                'user' => $user->name,
            ];
        }

        // Sort by date
        usort($activities, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return array_slice($activities, 0, 10);
    }
}