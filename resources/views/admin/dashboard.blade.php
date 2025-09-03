@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-md-3 mb-4">
        <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-0">{{ number_format($stats['posts']) }}</h3>
                    <p class="mb-0">Posts & Pages</p>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-0">{{ number_format($stats['programs']) }}</h3>
                    <p class="mb-0">Programs</p>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-mortarboard"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-0">{{ number_format($stats['jobs']) }}</h3>
                    <p class="mb-0">Job Listings</p>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-briefcase"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card card-stats">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <h3 class="mb-0">{{ number_format($stats['users']) }}</h3>
                    <p class="mb-0">Users</p>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ad Performance Row -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <h4 class="text-info">{{ number_format($adStats['total_impressions']) }}</h4>
                <p class="mb-0">Total Impressions</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h4 class="text-success">{{ number_format($adStats['total_clicks']) }}</h4>
                <p class="mb-0">Total Clicks</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h4 class="text-warning">{{ number_format($adStats['click_rate'], 2) }}%</h4>
                <p class="mb-0">Click Rate</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h4 class="text-primary">{{ number_format($adStats['active_ads']) }}</h4>
                <p class="mb-0">Active Ads</p>
            </div>
        </div>
    </div>
</div>

<!-- Content Management Section -->
<div class="row">
    <!-- Recent Posts -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Posts</h5>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($recentPosts as $post)
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ Str::limit($post->title, 40) }}</h6>
                            <small class="text-muted">
                                by {{ $post->creator->name ?? 'Unknown' }} • 
                                {{ $post->created_at->diffForHumans() }}
                            </small>
                            <div>
                                <span class="badge bg-{{ $post->status === 'published' ? 'success' : 'warning' }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @if(!$loop->last)<hr>@endif
                @empty
                    <p class="text-muted mb-0">No posts yet. <a href="{{ route('admin.posts.create') }}">Create your first post</a>.</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Recent Programs -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Programs</h5>
                <a href="{{ route('admin.programs.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($recentPrograms as $program)
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ Str::limit($program->title, 40) }}</h6>
                            <small class="text-muted">
                                {{ $program->program_type }} • 
                                {{ $program->created_at->diffForHumans() }}
                            </small>
                            <div>
                                <span class="badge bg-{{ $program->status === 'published' ? 'success' : 'warning' }}">
                                    {{ ucfirst($program->status) }}
                                </span>
                                @if($program->application_deadline)
                                    <span class="badge bg-info">
                                        Deadline: {{ $program->application_deadline->format('M d, Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if(!$loop->last)<hr>@endif
                @empty
                    <p class="text-muted mb-0">No programs yet. <a href="{{ route('admin.programs.create') }}">Create your first program</a>.</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Recent Jobs -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Jobs</h5>
                <a href="{{ route('admin.jobs.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($recentJobs as $job)
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ Str::limit($job->title, 40) }}</h6>
                            <small class="text-muted">
                                {{ $job->company_name }} • {{ $job->location }} • 
                                {{ $job->created_at->diffForHumans() }}
                            </small>
                            <div>
                                <span class="badge bg-{{ $job->status === 'published' ? 'success' : 'warning' }}">
                                    {{ ucfirst($job->status) }}
                                </span>
                                @if($job->application_deadline)
                                    <span class="badge bg-info">
                                        Deadline: {{ $job->application_deadline->format('M d, Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if(!$loop->last)<hr>@endif
                @empty
                    <p class="text-muted mb-0">No jobs yet. <a href="{{ route('admin.jobs.create') }}">Create your first job listing</a>.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Popular Content -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Popular Content</h5>
            </div>
            <div class="card-body">
                @if($popularPosts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Views</th>
                                    <th>Published</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($popularPosts as $post)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.posts.show', $post) }}">
                                                {{ $post->title }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($post->type) }}</span>
                                        </td>
                                        <td>{{ number_format($post->views ?? 0) }}</td>
                                        <td>{{ $post->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No content data available yet.</p>
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
                        <a href="{{ route('admin.posts.create') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>New Post
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.programs.create') }}" class="btn btn-outline-success w-100">
                            <i class="bi bi-plus-circle me-2"></i>New Program
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.jobs.create') }}" class="btn btn-outline-info w-100">
                            <i class="bi bi-plus-circle me-2"></i>New Job
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.ads.create') }}" class="btn btn-outline-warning w-100">
                            <i class="bi bi-plus-circle me-2"></i>New Ad
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
    // Add any dashboard-specific JavaScript here
    document.addEventListener('DOMContentLoaded', function() {
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