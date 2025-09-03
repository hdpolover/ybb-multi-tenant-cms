@extends('layouts.network')

@section('title', 'Network Dashboard')
@section('page-title', 'Network Dashboard')

@section('content')
<!-- System Overview Statistics -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-0">{{ number_format($stats['total_tenants']) }}</h3>
                    <p class="mb-0">Total Tenants</p>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-building"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-0">{{ number_format($stats['active_tenants']) }}</h3>
                    <p class="mb-0">Active Tenants</p>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-0">{{ number_format($stats['total_users']) }}</h3>
                    <p class="mb-0">Total Users</p>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-0">{{ number_format($stats['total_content']) }}</h3>
                    <p class="mb-0">Total Content</p>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-0">{{ number_format($stats['total_ads']) }}</h3>
                    <p class="mb-0">Total Ads</p>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-badge-ad"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-0">{{ number_format($stats['total_admins']) }}</h3>
                    <p class="mb-0">Network Admins</p>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Performance Metrics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <h4 class="text-info">{{ number_format($systemMetrics['total_impressions']) }}</h4>
                <p class="mb-0">Total Ad Impressions</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h4 class="text-success">{{ number_format($systemMetrics['total_clicks']) }}</h4>
                <p class="mb-0">Total Ad Clicks</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h4 class="text-warning">{{ number_format($systemMetrics['avg_click_rate'], 2) }}%</h4>
                <p class="mb-0">Average Click Rate</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h4 class="text-primary">{{ number_format($systemMetrics['active_ads']) }}</h4>
                <p class="mb-0">Active Ads</p>
            </div>
        </div>
    </div>
</div>

<!-- System Health Indicators -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">System Health</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6>Storage Usage</h6>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-info" style="width: {{ $healthIndicators['storage_usage']['percentage'] }}%"></div>
                            </div>
                            <small>{{ $healthIndicators['storage_usage']['used'] }} / {{ $healthIndicators['storage_usage']['total'] }}</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6>Database Size</h6>
                            <h4 class="text-primary">{{ $healthIndicators['database_size'] }}</h4>
                            <small>Current size</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6>Recent Errors</h6>
                            <h4 class="text-{{ $healthIndicators['recent_errors'] > 0 ? 'danger' : 'success' }}">
                                {{ $healthIndicators['recent_errors'] }}
                            </h4>
                            <small>Last 24 hours</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6>System Uptime</h6>
                            <h4 class="text-success">{{ $healthIndicators['uptime'] }}</h4>
                            <small>Availability</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Management Section -->
<div class="row">
    <!-- Tenant Growth Chart -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tenant Growth (Last 12 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="tenantGrowthChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Top Performing Tenants -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Top Tenants</h5>
                <a href="{{ route('network.tenants.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($topTenants as $tenant)
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $tenant->name }}</h6>
                            <small class="text-muted">{{ $tenant->domain }}</small>
                            <div class="mt-1">
                                <small class="badge bg-primary me-1">{{ $tenant->users_count }} users</small>
                                <small class="badge bg-info me-1">{{ $tenant->posts_count + $tenant->programs_count + $tenant->jobs_count }} content</small>
                            </div>
                        </div>
                        <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : 'warning' }}">
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </div>
                    @if(!$loop->last)<hr>@endif
                @empty
                    <p class="text-muted mb-0">No tenants yet. <a href="{{ route('network.tenants.create') }}">Create your first tenant</a>.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Recent Tenant Activity -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Tenant Activity</h5>
            </div>
            <div class="card-body">
                @if($recentTenants->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Domain</th>
                                    <th>Status</th>
                                    <th>Users</th>
                                    <th>Last Activity</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTenants as $tenant)
                                    <tr>
                                        <td>
                                            <a href="{{ route('network.tenants.show', $tenant) }}">
                                                {{ $tenant->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <code>{{ $tenant->domain }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : ($tenant->status === 'suspended' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($tenant->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $tenant->users->count() }}</td>
                                        <td>
                                            @if($tenant->users->first())
                                                {{ $tenant->users->first()->created_at->diffForHumans() }}
                                            @else
                                                No activity
                                            @endif
                                        </td>
                                        <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('network.tenants.show', $tenant) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('network.tenants.edit', $tenant) }}" class="btn btn-outline-secondary btn-sm">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No tenant activity yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('network.tenants.create') }}" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>Add New Tenant
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('network.admins.create') }}" class="btn btn-success w-100">
                            <i class="bi bi-plus-circle me-2"></i>Add Network Admin
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('network.analytics.index') }}" class="btn btn-info w-100">
                            <i class="bi bi-graph-up me-2"></i>View Analytics
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('network.settings.index') }}" class="btn btn-warning w-100">
                            <i class="bi bi-gear me-2"></i>System Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tenant Growth Chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('tenantGrowthChart').getContext('2d');
        const tenantGrowthData = @json($tenantGrowth);
        
        const chartData = {
            labels: tenantGrowthData.map(item => item.month),
            datasets: [{
                label: 'New Tenants',
                data: tenantGrowthData.map(item => item.count),
                borderColor: 'rgb(102, 126, 234)',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
@endpush