<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', tenant('name') ?: 'YBB CMS')</title>
    <meta name="description" content="@yield('description', tenant('description') ?: 'Youth Beyond Borders platform for opportunities and career development')">
    
    <!-- SEO Meta Tags -->
    <meta property="og:title" content="@yield('og_title', tenant('name') ?: 'YBB CMS')">
    <meta property="og:description" content="@yield('og_description', tenant('description') ?: 'Youth Beyond Borders platform')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', tenant('name') ?: 'YBB CMS')">
    <meta name="twitter:description" content="@yield('twitter_description', tenant('description') ?: 'Youth Beyond Borders platform')">
    <meta name="twitter:image" content="@yield('twitter_image', asset('images/og-default.jpg'))">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --text-dark: #2d3748;
            --text-light: #718096;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Figtree', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }

        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            transform: translateY(-1px);
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 4rem 0;
        }

        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-radius: 12px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }

        .section-title {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 3rem;
            text-align: center;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 2px;
        }

        .footer {
            background: #2d3748;
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .footer a {
            color: #a0aec0;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: white;
        }

        .search-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .search-input {
            border-radius: 50px;
            border: 2px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            color: white;
            padding: 0.75rem 1.5rem;
        }

        .search-input::placeholder {
            color: rgba(255,255,255,0.7);
        }

        .search-input:focus {
            border-color: rgba(255,255,255,0.8);
            background: rgba(255,255,255,0.2);
            box-shadow: none;
            color: white;
        }

        .ad-container {
            margin: 2rem 0;
            text-align: center;
        }

        .ad-banner {
            border-radius: 8px;
            overflow: hidden;
        }

        .breadcrumb {
            background: none;
            padding: 0;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            color: var(--text-light);
        }

        .pagination .page-link {
            border-radius: 8px;
            margin: 0 2px;
            border: none;
            color: var(--primary-color);
        }

        .pagination .page-link:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 0;
            }
            
            .section-title {
                font-size: 1.75rem;
                margin-bottom: 2rem;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Header Ads -->
    @if(isset($headerAds) && $headerAds->count() > 0)
        <div class="ad-container">
            @foreach($headerAds as $ad)
                <div class="ad-banner">
                    {!! $ad->content['html'] ?? '' !!}
                </div>
            @endforeach
        </div>
    @endif

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                {{ tenant('name') ?: 'YBB CMS' }}
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('programs.index') }}">Opportunities</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('jobs.index') }}">Jobs</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Resources
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/guides">Guides</a></li>
                            <li><a class="dropdown-item" href="/news">News</a></li>
                            <li><a class="dropdown-item" href="/blog">Blog</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">Contact</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/dashboard">Dashboard</a></li>
                                <li><a class="dropdown-item" href="/profile">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Sidebar Ads (if not in content area) -->
    @if(isset($sidebarAds) && $sidebarAds->count() > 0 && !isset($showSidebarInContent))
        <div class="container my-4">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    @foreach($sidebarAds as $ad)
                        <div class="ad-container">
                            <div class="ad-banner">
                                {!! $ad->content['html'] ?? '' !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Footer Ads -->
    @if(isset($footerAds) && $footerAds->count() > 0)
        <div class="container my-4">
            <div class="row">
                <div class="col-12">
                    @foreach($footerAds as $ad)
                        <div class="ad-container">
                            <div class="ad-banner">
                                {!! $ad->content['html'] ?? '' !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>{{ tenant('name') ?: 'YBB CMS' }}</h5>
                    <p class="text-muted">
                        {{ tenant('description') ?: 'Empowering youth through global opportunities and career development.' }}
                    </p>
                    <div class="social-links">
                        <a href="#" class="me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="me-3"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="col-md-2 mb-4">
                    <h5>Opportunities</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('programs.index') }}">All Programs</a></li>
                        <li><a href="{{ route('programs.index') }}?type=scholarship">Scholarships</a></li>
                        <li><a href="{{ route('programs.index') }}?type=internship">Internships</a></li>
                        <li><a href="{{ route('programs.index') }}?type=fellowship">Fellowships</a></li>
                    </ul>
                </div>
                
                <div class="col-md-2 mb-4">
                    <h5>Careers</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('jobs.index') }}">All Jobs</a></li>
                        <li><a href="{{ route('jobs.index') }}?type=full-time">Full-time</a></li>
                        <li><a href="{{ route('jobs.index') }}?type=part-time">Part-time</a></li>
                        <li><a href="{{ route('jobs.index') }}?remote=1">Remote</a></li>
                    </ul>
                </div>
                
                <div class="col-md-2 mb-4">
                    <h5>Resources</h5>
                    <ul class="list-unstyled">
                        <li><a href="/guides">Guides</a></li>
                        <li><a href="/news">News</a></li>
                        <li><a href="/blog">Blog</a></li>
                        <li><a href="/faq">FAQ</a></li>
                    </ul>
                </div>
                
                <div class="col-md-2 mb-4">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="/contact">Contact Us</a></li>
                        <li><a href="/about">About</a></li>
                        <li><a href="/privacy">Privacy Policy</a></li>
                        <li><a href="/terms">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">
                        &copy; {{ date('Y') }} {{ tenant('name') ?: 'YBB CMS' }}. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted">
                        Powered by YBB Multi-Tenant CMS
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>