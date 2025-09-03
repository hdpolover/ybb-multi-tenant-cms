<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Tenant-scoped API routes
Route::middleware(['tenant.resolve'])->group(function () {
    
    // Public API endpoints
    Route::prefix('v1')->group(function () {
        
        // Posts API
        Route::get('/posts', [\App\Http\Controllers\Api\PostController::class, 'index']);
        Route::get('/posts/{slug}', [\App\Http\Controllers\Api\PostController::class, 'show']);
        
        // Programs API
        Route::get('/programs', [\App\Http\Controllers\Api\ProgramController::class, 'index']);
        Route::get('/programs/{slug}', [\App\Http\Controllers\Api\ProgramController::class, 'show']);
        
        // Jobs API
        Route::get('/jobs', [\App\Http\Controllers\Api\JobController::class, 'index']);
        Route::get('/jobs/{slug}', [\App\Http\Controllers\Api\JobController::class, 'show']);
        
        // Terms API
        Route::get('/terms', [\App\Http\Controllers\Api\TermController::class, 'index']);
        Route::get('/terms/{type}', [\App\Http\Controllers\Api\TermController::class, 'byType']);
        
        // Search API
        Route::get('/search', [\App\Http\Controllers\Api\SearchController::class, 'search']);
        
        // Media API (public media only)
        Route::get('/media', [\App\Http\Controllers\Api\MediaController::class, 'index']);
    });
    
    // Protected API endpoints (require authentication)
    Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
        
        // User-specific endpoints
        Route::get('/user/posts', [\App\Http\Controllers\Api\UserController::class, 'posts']);
        Route::get('/user/media', [\App\Http\Controllers\Api\UserController::class, 'media']);
        
        // Admin API endpoints (require admin role)
        Route::middleware(['tenant.admin'])->prefix('admin')->group(function () {
            
            // Posts management
            Route::apiResource('posts', \App\Http\Controllers\Api\Admin\PostController::class);
            Route::apiResource('programs', \App\Http\Controllers\Api\Admin\ProgramController::class);
            Route::apiResource('jobs', \App\Http\Controllers\Api\Admin\JobController::class);
            
            // Media management
            Route::apiResource('media', \App\Http\Controllers\Api\Admin\MediaController::class);
            Route::post('media/upload', [\App\Http\Controllers\Api\Admin\MediaController::class, 'upload']);
            
            // Terms management
            Route::apiResource('terms', \App\Http\Controllers\Api\Admin\TermController::class);
            
            // Users management
            Route::apiResource('users', \App\Http\Controllers\Api\Admin\UserController::class);
            
            // Ads management
            Route::apiResource('ads', \App\Http\Controllers\Api\Admin\AdController::class);
            
            // Analytics
            Route::get('analytics/overview', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'overview']);
            Route::get('analytics/posts', [\App\Http\Controllers\Api\Admin\AnalyticsController::class, 'posts']);
            
            // Settings
            Route::get('settings', [\App\Http\Controllers\Api\Admin\SettingsController::class, 'index']);
            Route::put('settings', [\App\Http\Controllers\Api\Admin\SettingsController::class, 'update']);
        });
    });
});

// Network Admin API (separate domain)
Route::middleware(['network.admin'])->prefix('network/v1')->group(function () {
    
    // Tenants management
    Route::apiResource('tenants', \App\Http\Controllers\Api\Network\TenantController::class);
    
    // Network admins management
    Route::apiResource('admins', \App\Http\Controllers\Api\Network\AdminController::class);
    
    // System analytics
    Route::get('analytics/overview', [\App\Http\Controllers\Api\Network\AnalyticsController::class, 'overview']);
    Route::get('analytics/tenants', [\App\Http\Controllers\Api\Network\AnalyticsController::class, 'tenants']);
    
    // System settings
    Route::get('settings', [\App\Http\Controllers\Api\Network\SettingsController::class, 'index']);
    Route::put('settings', [\App\Http\Controllers\Api\Network\SettingsController::class, 'update']);
});