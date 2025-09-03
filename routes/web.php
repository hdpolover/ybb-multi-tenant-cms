<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Programs (opportunities, scholarships, internships)
Route::get('/opportunities', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/opportunities/{slug}', [ProgramController::class, 'show'])->name('programs.show');

// Jobs
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{slug}', [JobController::class, 'show'])->name('jobs.show');

// Pages, News, Guides (catch-all)
Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show');

// Tenant Admin Routes
Route::prefix('admin')->middleware(['auth', 'tenant.admin'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Posts management
    Route::resource('posts', \App\Http\Controllers\Admin\PostController::class);
    Route::resource('programs', \App\Http\Controllers\Admin\ProgramController::class);
    Route::resource('jobs', \App\Http\Controllers\Admin\JobController::class);
    Route::resource('ads', \App\Http\Controllers\Admin\AdController::class);
    Route::resource('media', \App\Http\Controllers\Admin\MediaController::class);
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    
    // Additional ad routes
    Route::post('ads/{ad}/toggle', [\App\Http\Controllers\Admin\AdController::class, 'toggle'])->name('admin.ads.toggle');
    Route::get('ads/{ad}/analytics', [\App\Http\Controllers\Admin\AdController::class, 'analytics'])->name('admin.ads.analytics');
});

// Network Admin Routes (separate domain/subdomain)
Route::middleware(['auth:admin', 'network.admin'])->prefix('network')->group(function () {
    Route::get('/', [\App\Http\Controllers\Network\DashboardController::class, 'index'])->name('network.dashboard');
    
    // Tenant management
    Route::resource('tenants', \App\Http\Controllers\Network\TenantController::class)->names([
        'index' => 'network.tenants.index',
        'create' => 'network.tenants.create',
        'store' => 'network.tenants.store',
        'show' => 'network.tenants.show',
        'edit' => 'network.tenants.edit',
        'update' => 'network.tenants.update',
        'destroy' => 'network.tenants.destroy',
    ]);
    
    // Additional tenant routes
    Route::post('tenants/{tenant}/toggle', [\App\Http\Controllers\Network\TenantController::class, 'toggle'])->name('network.tenants.toggle');
    Route::post('tenants/{tenant}/suspend', [\App\Http\Controllers\Network\TenantController::class, 'suspend'])->name('network.tenants.suspend');
    
    // Network admin management
    Route::resource('admins', \App\Http\Controllers\Network\AdminController::class)->names([
        'index' => 'network.admins.index',
        'create' => 'network.admins.create',
        'store' => 'network.admins.store',
        'show' => 'network.admins.show',
        'edit' => 'network.admins.edit',
        'update' => 'network.admins.update',
        'destroy' => 'network.admins.destroy',
    ]);
    
    Route::post('admins/{admin}/toggle', [\App\Http\Controllers\Network\AdminController::class, 'toggle'])->name('network.admins.toggle');
    
    // System analytics
    Route::get('analytics', [\App\Http\Controllers\Network\AnalyticsController::class, 'index'])->name('network.analytics.index');
    Route::get('analytics/tenants', [\App\Http\Controllers\Network\AnalyticsController::class, 'tenants'])->name('network.analytics.tenants');
    Route::get('analytics/performance', [\App\Http\Controllers\Network\AnalyticsController::class, 'performance'])->name('network.analytics.performance');
    
    // System settings
    Route::get('settings', [\App\Http\Controllers\Network\SettingsController::class, 'index'])->name('network.settings.index');
    Route::put('settings', [\App\Http\Controllers\Network\SettingsController::class, 'update'])->name('network.settings.update');
    
    // System logs
    Route::get('logs', [\App\Http\Controllers\Network\LogController::class, 'index'])->name('network.logs.index');
    Route::get('logs/{log}', [\App\Http\Controllers\Network\LogController::class, 'show'])->name('network.logs.show');
    
    // Backup management
    Route::get('backups', [\App\Http\Controllers\Network\BackupController::class, 'index'])->name('network.backups.index');
    Route::post('backups/create', [\App\Http\Controllers\Network\BackupController::class, 'create'])->name('network.backups.create');
    Route::get('backups/{backup}/download', [\App\Http\Controllers\Network\BackupController::class, 'download'])->name('network.backups.download');
});

    // Search routes
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

    require __DIR__.'/auth.php';