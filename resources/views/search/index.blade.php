@extends('layouts.app')

@section('title', 'Search Results - ' . (tenant('name') ?: 'YBB CMS'))
@section('description', 'Search results for "' . $query . '" on ' . (tenant('name') ?: 'YBB CMS'))

@section('content')
<div class="container py-5">
    <!-- Search Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="search-header bg-light rounded p-4">
                <h1 class="h3 mb-3">
                    @if(!empty($query))
                        Search Results for "{{ $query }}"
                    @else
                        Search
                    @endif
                </h1>
                
                <!-- Enhanced Search Form -->
                <form action="{{ route('search') }}" method="GET" class="mb-3">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <div class="position-relative">
                                <input 
                                    type="text" 
                                    name="q" 
                                    class="form-control form-control-lg" 
                                    placeholder="Search opportunities, jobs, or resources..."
                                    value="{{ $query }}"
                                    autocomplete="off"
                                    id="searchInput"
                                >
                                <div id="searchSuggestions" class="position-absolute w-100 bg-white border rounded shadow-sm" style="top: 100%; z-index: 1000; display: none;"></div>
                            </div>
                        </div>
                        
                        <div class="col-lg-2">
                            <select name="type" class="form-select form-select-lg">
                                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All Types</option>
                                <option value="programs" {{ $type === 'programs' ? 'selected' : '' }}>Opportunities</option>
                                <option value="jobs" {{ $type === 'jobs' ? 'selected' : '' }}>Jobs</option>
                                <option value="posts" {{ $type === 'posts' ? 'selected' : '' }}>Articles</option>
                            </select>
                        </div>
                        
                        <div class="col-lg-2">
                            <select name="sort" class="form-select form-select-lg">
                                <option value="relevance" {{ $sort === 'relevance' ? 'selected' : '' }}>Relevance</option>
                                <option value="date" {{ $sort === 'date' ? 'selected' : '' }}>Date</option>
                                <option value="title" {{ $sort === 'title' ? 'selected' : '' }}>Title</option>
                            </select>
                        </div>
                        
                        <div class="col-lg-2">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
                
                @if(!empty($query))
                    <div class="d-flex flex-wrap align-items-center text-muted">
                        <span class="me-3">
                            <strong>{{ $results['total'] }}</strong> results found
                        </span>
                        
                        @if($results['programs']->count() > 0)
                            <span class="badge bg-primary me-2">{{ $results['programs']->count() }} Opportunities</span>
                        @endif
                        
                        @if($results['jobs']->count() > 0)
                            <span class="badge bg-success me-2">{{ $results['jobs']->count() }} Jobs</span>
                        @endif
                        
                        @if($results['posts']->count() > 0)
                            <span class="badge bg-secondary me-2">{{ $results['posts']->count() }} Articles</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(!empty($query))
        @if($results['total'] > 0)
            <!-- Search Results -->
            <div class="row">
                <!-- Main Results -->
                <div class="col-lg-8">
                    @if($type === 'all' || $type === 'programs')
                        @if($results['programs']->count() > 0)
                            <section class="mb-5">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h2 class="h4 mb-0">
                                        <i class="bi bi-mortarboard text-primary me-2"></i>
                                        Opportunities
                                        @if($type === 'all')
                                            <span class="text-muted small">({{ $results['programs']->count() }} results)</span>
                                        @endif
                                    </h2>
                                    @if($type === 'all' && $results['programs']->count() >= 6)
                                        <a href="{{ route('search') }}?q={{ $query }}&type=programs" class="btn btn-outline-primary btn-sm">
                                            View All
                                        </a>
                                    @endif
                                </div>
                                
                                <div class="row">
                                    @foreach($results['programs'] as $program)
                                        <div class="col-md-6 mb-4">
                                            <div class="card h-100">
                                                @if($program->banner_image)
                                                    <img src="{{ asset('storage/' . $program->banner_image) }}" class="card-img-top" alt="{{ $program->title }}" style="height: 180px; object-fit: cover;">
                                                @endif
                                                
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <span class="badge bg-primary">{{ ucfirst($program->type) }}</span>
                                                        @if($program->deadline)
                                                            <small class="text-muted">
                                                                <i class="bi bi-calendar"></i> 
                                                                {{ $program->deadline->format('M j, Y') }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                    
                                                    <h5 class="card-title">{{ $program->title }}</h5>
                                                    
                                                    @if($program->organization)
                                                        <h6 class="card-subtitle mb-2 text-primary">{{ $program->organization }}</h6>
                                                    @endif
                                                    
                                                    <p class="card-text text-muted">
                                                        {{ Str::limit($program->description, 100) }}
                                                    </p>
                                                    
                                                    <div class="mt-auto">
                                                        @if($program->location)
                                                            <small class="text-muted d-block mb-2">
                                                                <i class="bi bi-geo-alt"></i> {{ $program->location }}
                                                            </small>
                                                        @endif
                                                        
                                                        <a href="{{ route('programs.show', $program) }}" class="btn btn-primary btn-sm">
                                                            Learn More <i class="bi bi-arrow-right"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if($type === 'programs' && method_exists($results['programs'], 'links'))
                                    <div class="d-flex justify-content-center">
                                        {{ $results['programs']->links() }}
                                    </div>
                                @endif
                            </section>
                        @endif
                    @endif

                    @if($type === 'all' || $type === 'jobs')
                        @if($results['jobs']->count() > 0)
                            <section class="mb-5">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h2 class="h4 mb-0">
                                        <i class="bi bi-briefcase text-success me-2"></i>
                                        Job Opportunities
                                        @if($type === 'all')
                                            <span class="text-muted small">({{ $results['jobs']->count() }} results)</span>
                                        @endif
                                    </h2>
                                    @if($type === 'all' && $results['jobs']->count() >= 6)
                                        <a href="{{ route('search') }}?q={{ $query }}&type=jobs" class="btn btn-outline-success btn-sm">
                                            View All
                                        </a>
                                    @endif
                                </div>
                                
                                <div class="row">
                                    @foreach($results['jobs'] as $job)
                                        <div class="col-12 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-8">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <span class="badge bg-success me-2">{{ ucfirst($job->type) }}</span>
                                                                @if($job->remote)
                                                                    <span class="badge bg-info me-2">Remote</span>
                                                                @endif
                                                                <small class="text-muted">
                                                                    {{ $job->created_at->diffForHumans() }}
                                                                </small>
                                                            </div>
                                                            
                                                            <h5 class="card-title mb-1">{{ $job->title }}</h5>
                                                            
                                                            @if($job->company)
                                                                <h6 class="card-subtitle mb-2 text-success">{{ $job->company }}</h6>
                                                            @endif
                                                            
                                                            <p class="card-text text-muted mb-2">
                                                                {{ Str::limit($job->description, 120) }}
                                                            </p>
                                                            
                                                            <div class="d-flex flex-wrap gap-3 text-muted small">
                                                                @if($job->location)
                                                                    <span><i class="bi bi-geo-alt"></i> {{ $job->location }}</span>
                                                                @endif
                                                                @if($job->salary_min && $job->salary_max)
                                                                    <span class="text-success">
                                                                        <i class="bi bi-currency-dollar"></i> 
                                                                        ${{ number_format($job->salary_min) }} - ${{ number_format($job->salary_max) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-md-4 text-md-end">
                                                            <a href="{{ route('jobs.show', $job) }}" class="btn btn-success">
                                                                Apply Now <i class="bi bi-arrow-right"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if($type === 'jobs' && method_exists($results['jobs'], 'links'))
                                    <div class="d-flex justify-content-center">
                                        {{ $results['jobs']->links() }}
                                    </div>
                                @endif
                            </section>
                        @endif
                    @endif

                    @if($type === 'all' || $type === 'posts')
                        @if($results['posts']->count() > 0)
                            <section class="mb-5">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h2 class="h4 mb-0">
                                        <i class="bi bi-file-text text-secondary me-2"></i>
                                        Articles & News
                                        @if($type === 'all')
                                            <span class="text-muted small">({{ $results['posts']->count() }} results)</span>
                                        @endif
                                    </h2>
                                    @if($type === 'all' && $results['posts']->count() >= 6)
                                        <a href="{{ route('search') }}?q={{ $query }}&type=posts" class="btn btn-outline-secondary btn-sm">
                                            View All
                                        </a>
                                    @endif
                                </div>
                                
                                <div class="row">
                                    @foreach($results['posts'] as $post)
                                        <div class="col-md-6 mb-4">
                                            <article class="card h-100">
                                                @if($post->featured_image)
                                                    <img src="{{ asset('storage/' . $post->featured_image) }}" class="card-img-top" alt="{{ $post->title }}" style="height: 180px; object-fit: cover;">
                                                @endif
                                                
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        @if($post->category)
                                                            <span class="badge bg-secondary">{{ $post->category }}</span>
                                                        @endif
                                                        <small class="text-muted">
                                                            {{ $post->published_at ? $post->published_at->format('M j, Y') : $post->created_at->format('M j, Y') }}
                                                        </small>
                                                    </div>
                                                    
                                                    <h5 class="card-title">{{ $post->title }}</h5>
                                                    <p class="card-text text-muted">
                                                        {{ Str::limit(strip_tags($post->content), 100) }}
                                                    </p>
                                                    
                                                    <div class="mt-auto">
                                                        @if($post->author)
                                                            <small class="text-muted d-block mb-2">
                                                                <i class="bi bi-person"></i> By {{ $post->author }}
                                                            </small>
                                                        @endif
                                                        
                                                        <a href="/posts/{{ $post->slug }}" class="btn btn-outline-primary btn-sm">
                                                            Read More <i class="bi bi-arrow-right"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </article>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if($type === 'posts' && method_exists($results['posts'], 'links'))
                                    <div class="d-flex justify-content-center">
                                        {{ $results['posts']->links() }}
                                    </div>
                                @endif
                            </section>
                        @endif
                    @endif
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="sticky-top" style="top: 2rem;">
                        <!-- Quick Filters -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-funnel me-2"></i>Quick Filters
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Content Type</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('search') }}?q={{ $query }}&type=all" 
                                           class="btn btn-sm {{ $type === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                                            All
                                        </a>
                                        <a href="{{ route('search') }}?q={{ $query }}&type=programs" 
                                           class="btn btn-sm {{ $type === 'programs' ? 'btn-primary' : 'btn-outline-primary' }}">
                                            Opportunities
                                        </a>
                                        <a href="{{ route('search') }}?q={{ $query }}&type=jobs" 
                                           class="btn btn-sm {{ $type === 'jobs' ? 'btn-success' : 'btn-outline-success' }}">
                                            Jobs
                                        </a>
                                        <a href="{{ route('search') }}?q={{ $query }}&type=posts" 
                                           class="btn btn-sm {{ $type === 'posts' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                            Articles
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search Suggestions -->
                        @if(!empty($suggestions))
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-lightbulb me-2"></i>Related Searches
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($suggestions as $suggestion)
                                            <a href="{{ route('search') }}?q={{ urlencode($suggestion) }}" 
                                               class="btn btn-sm btn-outline-secondary">
                                                {{ $suggestion }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Popular Searches -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-fire me-2"></i>Popular Searches
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    @php
                                        $popularSearches = ['scholarship', 'internship', 'remote work', 'engineering', 'marketing', 'design', 'fellowship', 'finance'];
                                    @endphp
                                    @foreach($popularSearches as $popular)
                                        <a href="{{ route('search') }}?q={{ urlencode($popular) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            {{ $popular }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Ads -->
                        @if(isset($sidebarAds) && $sidebarAds->count() > 0)
                            @foreach($sidebarAds as $ad)
                                <div class="card mb-4">
                                    <div class="card-body text-center">
                                        {!! $ad->content['html'] ?? '' !!}
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @else
            <!-- No Results -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                </div>
                <h2 class="h4 mb-3">No results found for "{{ $query }}"</h2>
                <p class="text-muted mb-4">
                    Try adjusting your search terms or browse our categories below.
                </p>
                
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="{{ route('programs.index') }}" class="btn btn-primary">
                                <i class="bi bi-mortarboard me-2"></i>Browse Opportunities
                            </a>
                            <a href="{{ route('jobs.index') }}" class="btn btn-success">
                                <i class="bi bi-briefcase me-2"></i>Find Jobs
                            </a>
                            <a href="/blog" class="btn btn-secondary">
                                <i class="bi bi-file-text me-2"></i>Read Articles
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- Search Landing Page -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-search text-primary" style="font-size: 4rem;"></i>
            </div>
            <h2 class="h4 mb-3">What are you looking for?</h2>
            <p class="text-muted mb-4">
                Search through thousands of opportunities, jobs, and articles.
            </p>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <i class="bi bi-mortarboard text-primary mb-3" style="font-size: 2rem;"></i>
                                    <h5>Opportunities</h5>
                                    <p class="text-muted small">Scholarships, internships, fellowships</p>
                                    <a href="{{ route('programs.index') }}" class="btn btn-primary btn-sm">Browse</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <i class="bi bi-briefcase text-success mb-3" style="font-size: 2rem;"></i>
                                    <h5>Jobs</h5>
                                    <p class="text-muted small">Full-time, part-time, remote positions</p>
                                    <a href="{{ route('jobs.index') }}" class="btn btn-success btn-sm">Browse</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <i class="bi bi-file-text text-secondary mb-3" style="font-size: 2rem;"></i>
                                    <h5>Articles</h5>
                                    <p class="text-muted small">Career advice, guides, and news</p>
                                    <a href="/blog" class="btn btn-secondary btn-sm">Browse</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Search autocomplete functionality
    const searchInput = document.getElementById('searchInput');
    const suggestionsContainer = document.getElementById('searchSuggestions');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(debounceTimer);
        
        if (query.length < 2) {
            suggestionsContainer.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetchSuggestions(query);
        }, 300);
    });

    function fetchSuggestions(query) {
        fetch(`{{ route('search.autocomplete') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(suggestions => {
                displaySuggestions(suggestions);
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
            });
    }

    function displaySuggestions(suggestions) {
        if (suggestions.length === 0) {
            suggestionsContainer.style.display = 'none';
            return;
        }

        const html = suggestions.map(suggestion => 
            `<div class="suggestion-item p-2 border-bottom" style="cursor: pointer;" onclick="selectSuggestion('${suggestion}')">
                ${suggestion}
            </div>`
        ).join('');

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = 'block';
    }

    function selectSuggestion(suggestion) {
        searchInput.value = suggestion;
        suggestionsContainer.style.display = 'none';
        // Optionally trigger search
        searchInput.closest('form').submit();
    }

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });
</script>
@endpush