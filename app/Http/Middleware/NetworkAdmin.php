<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NetworkAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user is a network admin (not tenant-scoped)
        if (!$user->is_network_admin) {
            abort(403, 'Access denied. Network admin access required.');
        }

        return $next($request);
    }
}