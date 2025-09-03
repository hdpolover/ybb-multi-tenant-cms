<?php

namespace App\Http\Controllers\Network;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of network admins
     */
    public function index(Request $request)
    {
        $query = Admin::latest();

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $admins = $query->paginate(20);

        return view('network.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin
     */
    public function create()
    {
        return view('network.admins.create');
    }

    /**
     * Store a newly created admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
            'permissions' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'permissions' => $request->permissions ?? [],
            'status' => $request->status,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('network.admins.index')
            ->with('success', 'Admin created successfully.');
    }

    /**
     * Display the specified admin
     */
    public function show(Admin $admin)
    {
        // Get admin activity stats
        $stats = [
            'created_tenants' => $admin->createdTenants()->count(),
            'last_login' => $admin->last_login_at,
            'total_logins' => $admin->login_count ?? 0,
        ];

        return view('network.admins.show', compact('admin', 'stats'));
    }

    /**
     * Show the form for editing the specified admin
     */
    public function edit(Admin $admin)
    {
        return view('network.admins.edit', compact('admin'));
    }

    /**
     * Update the specified admin
     */
    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:8|confirmed',
            'permissions' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'permissions' => $request->permissions ?? [],
            'status' => $request->status,
            'updated_by' => auth()->id(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return redirect()->route('network.admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    /**
     * Remove the specified admin
     */
    public function destroy(Admin $admin)
    {
        // Prevent self-deletion
        if ($admin->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();

        return redirect()->route('network.admins.index')
            ->with('success', 'Admin deleted successfully.');
    }

    /**
     * Toggle admin status
     */
    public function toggle(Admin $admin)
    {
        // Prevent self-deactivation
        if ($admin->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot deactivate your own account.');
        }

        $newStatus = $admin->status === 'active' ? 'inactive' : 'active';
        
        $admin->update([
            'status' => $newStatus,
            'updated_by' => auth()->id(),
        ]);

        $action = $newStatus === 'active' ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Admin {$action} successfully.");
    }
}