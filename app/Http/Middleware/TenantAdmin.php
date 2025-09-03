<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class TenantAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return \Illuminate\Support\Facades\Redirect::route('login');
        }

        // Check if user belongs to current tenant
        $currentTenant = app('current_tenant');
        if (!$currentTenant || $user->tenant_id !== $currentTenant->id) {
            abort(403, 'Access denied. You do not have access to this tenant.');
        }

        // Check if user has admin permissions for this tenant
        if (!$user->hasRole(['TenantOwner', 'Admin', 'Editor', 'Author', 'SEO', 'Moderator', 'Analyst'])) {
            abort(403, 'Access denied. You do not have admin permissions.');
        }

        return $next($request);
    }
}