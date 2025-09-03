@extends('layouts.app')

@section('title', 'Home - ' . (tenant('name') ?: 'YBB CMS'))
@section('description', tenant('description') ?: 'Discover opportunities, jobs, and career development resources on Youth Beyond Borders platform.')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">
                    @if(tenant('name'))
                        Welcome to {{ tenant('name') }}
                    @else
                        Discover Your Next Opportunity
                    @endif
                </h1>
                <p class="lead mb-4">
                    {{ tenant('description') ?: 'Find scholarships, internships, fellowships, and job opportunities that match your goals and aspirations.' }}
                </p>
                
                <!-- Search Bar -->
                <div class="search-container mb-4">
                    <form action="{{ route('search') }}" method="GET" class="d-flex">
                        <input 
                            type="text" 
                            name="q" 
                            class="form-control search-input me-2" 
                            placeholder="Search opportunities, jobs, or resources..."
                            value="{{ request('q') }}"
                        >
                        <button class="btn btn-light" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
                
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('programs.index') }}" class="btn btn-light btn-lg">
                        <i class="bi bi-mortarboard me-2"></i>Browse Opportunities
                    </a>
                    <a href="{{ route('jobs.index') }}" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-briefcase me-2"></i>Find Jobs
                    </a>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <div class="hero-stats">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="bg-white bg-opacity-20 rounded p-3">
                                <h3 class="mb-1">{{ $stats['programs'] ?? 0 }}</h3>
                                <small>Opportunities</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="bg-white bg-opacity-20 rounded p-3">
                                <h3 class="mb-1">{{ $stats['jobs'] ?? 0 }}</h3>
                                <small>Job Listings</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-20 rounded p-3">
                                <h3 class="mb-1">{{ $stats['posts'] ?? 0 }}</h3>
                                <small>Articles</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-20 rounded p-3">
                                <h3 class="mb-1">{{ $stats['users'] ?? 0 }}</h3>
                                <small>Members</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Header Ads Section -->
@if(isset($headerAds) && $headerAds->count() > 0)
    <section class="py-4 bg-light">
        <div class="container">
            @foreach($headerAds as $ad)
                <div class="text-center mb-3">
                    {!! $ad->content['html'] ?? '' !!}
                </div>
            @endforeach
        </div>
    </section>
@endif

<!-- Featured Opportunities Section -->
@if(isset($featuredPrograms) && $featuredPrograms->count() > 0)
<section class="py-5">
    <div class="container">
        <h2 class="section-title">Featured Opportunities</h2>
        <div class="row">
            @foreach($featuredPrograms as $program)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        @if($program->banner_image)
                            <img src="{{ asset('storage/' . $program->banner_image) }}" class="card-img-top" alt="{{ $program->title }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-primary d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-mortarboard text-white" style="font-size: 3rem;"></i>
                            </div>
                        @endif
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary">{{ ucfirst($program->type) }}</span>
                                @if($program->deadline && $program->deadline > now())
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i> 
                                        {{ $program->deadline->format('M j, Y') }}
                                    </small>
                                @endif
                            </div>
                            
                            <h5 class="card-title">{{ $program->title }}</h5>
                            <p class="card-text text-muted">
                                {{ Str::limit($program->description, 120) }}
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
        
        <div class="text-center mt-4">
            <a href="{{ route('programs.index') }}" class="btn btn-outline-primary btn-lg">
                View All Opportunities <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- Sidebar Ads Section (between sections) -->
@if(isset($sidebarAds) && $sidebarAds->count() > 0)
    <section class="py-3 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    @foreach($sidebarAds as $ad)
                        <div class="text-center mb-3">
                            {!! $ad->content['html'] ?? '' !!}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

<!-- Featured Jobs Section -->
@if(isset($featuredJobs) && $featuredJobs->count() > 0)
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title">Latest Job Opportunities</h2>
        <div class="row">
            @foreach($featuredJobs as $job)
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-success">{{ ucfirst($job->type) }}</span>
                                    @if($job->remote)
                                        <span class="badge bg-info ms-1">Remote</span>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ $job->created_at->diffForHumans() }}
                                </small>
                            </div>
                            
                            <h5 class="card-title">{{ $job->title }}</h5>
                            
                            @if($job->company)
                                <h6 class="card-subtitle mb-2 text-primary">{{ $job->company }}</h6>
                            @endif
                            
                            <p class="card-text text-muted">
                                {{ Str::limit($job->description, 120) }}
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div>
                                    @if($job->location)
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i> {{ $job->location }}
                                        </small>
                                    @endif
                                    @if($job->salary_min && $job->salary_max)
                                        <small class="text-success d-block">
                                            <i class="bi bi-currency-dollar"></i> 
                                            ${{ number_format($job->salary_min) }} - ${{ number_format($job->salary_max) }}
                                        </small>
                                    @endif
                                </div>
                                
                                <a href="{{ route('jobs.show', $job) }}" class="btn btn-success btn-sm">
                                    Apply Now <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="text-center mt-4">
            <a href="{{ route('jobs.index') }}" class="btn btn-outline-success btn-lg">
                View All Jobs <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- Recent Posts Section -->
@if(isset($recentPosts) && $recentPosts->count() > 0)
<section class="py-5">
    <div class="container">
        <h2 class="section-title">Latest Articles & News</h2>
        <div class="row">
            @foreach($recentPosts as $post)
                <div class="col-lg-4 col-md-6 mb-4">
                    <article class="card h-100">
                        @if($post->featured_image)
                            <img src="{{ asset('storage/' . $post->featured_image) }}" class="card-img-top" alt="{{ $post->title }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-file-text text-white" style="font-size: 3rem;"></i>
                            </div>
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
                                {{ Str::limit(strip_tags($post->content), 120) }}
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
        
        <div class="text-center mt-4">
            <a href="/blog" class="btn btn-outline-primary btn-lg">
                View All Articles <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- Call to Action Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="mb-4">Ready to Take the Next Step?</h2>
                <p class="lead mb-4">
                    Join thousands of young professionals who have found their path through our platform.
                </p>
                
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-light btn-lg">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </a>
                    @else
                        <a href="/dashboard" class="btn btn-light btn-lg">
                            <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                        </a>
                        <a href="{{ route('programs.index') }}" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-search me-2"></i>Browse Opportunities
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Signup -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h3 class="mb-3">Stay Updated</h3>
                <p class="text-muted mb-4">
                    Get the latest opportunities and career insights delivered to your inbox.
                </p>
                
                <form class="d-flex flex-column flex-sm-row gap-2">
                    <input 
                        type="email" 
                        class="form-control" 
                        placeholder="Enter your email address"
                        required
                    >
                    <button type="submit" class="btn btn-primary">
                        Subscribe
                    </button>
                </form>
                
                <small class="text-muted d-block mt-2">
                    We respect your privacy. Unsubscribe at any time.
                </small>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    // Newsletter form handling
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = this.querySelector('input[type="email"]').value;
        
        // Here you would typically send the email to your backend
        alert('Thank you for subscribing! We\'ll keep you updated.');
        
        this.reset();
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
</script>
@endpush