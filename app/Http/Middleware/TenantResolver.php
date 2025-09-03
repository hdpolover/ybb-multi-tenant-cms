<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TenantResolver
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $domain = $request->getHost();
        
        // Skip tenant resolution for network admin domain
        $networkAdminDomain = config('app.tenancy.network_admin_domain');
        if ($domain === $networkAdminDomain) {
            return $next($request);
        }

        // Find tenant by domain
        $tenant = Tenant::findByDomain($domain);
        
        if (!$tenant) {
            // Handle tenant not found - could redirect to a default page
            // or show a 404 page
            abort(404, 'Tenant not found for domain: ' . $domain);
        }

        // Bind current tenant to container
        app()->instance('current_tenant', $tenant);
        
        // Set tenant context in view
        view()->share('tenant', $tenant);
        
        // You could also set URL defaults here if needed
        // URL::defaults(['tenant' => $tenant->domain]);

        return $next($request);
    }
}