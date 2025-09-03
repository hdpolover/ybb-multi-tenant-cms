<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenant Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure settings related to multi-tenancy in your
    | application. This includes tenant resolution, domain mapping,
    | and feature management.
    |
    */

    /**
     * How tenants are resolved
     */
    'resolution' => [
        'type' => env('TENANT_RESOLUTION', 'domain'), // domain, subdomain, path
    ],

    /**
     * Network admin configuration
     */
    'network_admin' => [
        'domain' => env('NETWORK_ADMIN_DOMAIN', 'admin.localhost'),
        'enabled' => env('NETWORK_ADMIN_ENABLED', true),
    ],

    /**
     * Default tenant when none is resolved
     */
    'default_tenant' => env('DEFAULT_TENANT', null),

    /**
     * Available post family types that can be enabled per tenant
     */
    'post_families' => [
        'programs' => [
            'name' => 'Programs',
            'description' => 'Scholarships, Opportunities, and Internships',
            'table' => 'pt_program',
            'model' => 'App\\Models\\PostTypes\\Program',
            'controller' => 'App\\Http\\Controllers\\ProgramController',
            'route_prefix' => 'opportunities',
            'enabled_by_default' => true,
        ],
        'jobs' => [
            'name' => 'Jobs',
            'description' => 'Job Postings and Career Opportunities',
            'table' => 'pt_job',
            'model' => 'App\\Models\\PostTypes\\Job',
            'controller' => 'App\\Http\\Controllers\\JobController',
            'route_prefix' => 'jobs',
            'enabled_by_default' => true,
        ],
        // Future families can be added here
        // 'events' => [
        //     'name' => 'Events',
        //     'description' => 'Conferences, Workshops, and Meetups',
        //     'table' => 'pt_event',
        //     'model' => 'App\\Models\\PostTypes\\Event',
        //     'controller' => 'App\\Http\\Controllers\\EventController',
        //     'route_prefix' => 'events',
        //     'enabled_by_default' => false,
        // ],
    ],

    /**
     * Default enabled features for new tenants
     */
    'default_features' => [
        'programs',
        'jobs',
        'news',
        'pages',
        'media_library',
        'seo_tools',
        'ads',
        'analytics',
    ],

    /**
     * Database settings
     */
    'database' => [
        'tenant_column' => 'tenant_id',
        'use_global_scopes' => true,
    ],

    /**
     * Caching settings
     */
    'cache' => [
        'tenant_ttl' => 3600, // Cache tenant data for 1 hour
        'settings_ttl' => 1800, // Cache tenant settings for 30 minutes
    ],

    /**
     * Feature flags
     */
    'features' => [
        'auto_tenant_creation' => env('AUTO_TENANT_CREATION', false),
        'tenant_isolation' => env('TENANT_ISOLATION', true),
        'cross_tenant_access' => env('CROSS_TENANT_ACCESS', false),
    ],

];