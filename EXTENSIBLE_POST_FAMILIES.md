# Extensible Post Families System

This Laravel multi-tenant CMS implements an extensible post families system that allows for easy addition of new structured content types while maintaining consistency and reusability.

## Architecture Overview

### Core Components

1. **BasePostType** - Abstract base class for all post families
2. **Post Model** - Core posts table that all content shares
3. **PostTypeService** - Service layer for managing post types
4. **Individual Post Type Models** - Concrete implementations (Program, Job, etc.)

### Current Post Families

#### Programs (`pt_program`)
- **Purpose**: Scholarships, opportunities, and internships
- **Route**: `/opportunities/{slug}`
- **Fields**: organizer_name, program_type, funding_type, deadline_at, etc.
- **Filters**: program_type, funding_type, country_code, deadline ranges

#### Jobs (`pt_job`)
- **Purpose**: Job postings and career opportunities  
- **Route**: `/jobs/{slug}`
- **Fields**: company_name, employment_type, workplace_type, salary ranges, etc.
- **Filters**: employment_type, workplace_type, experience_level, salary ranges

## Adding New Post Families

### Step 1: Create Migration
Create a new migration following the naming convention `pt_{family_name}`:

```php
// database/migrations/xxxx_create_pt_event_table.php
Schema::create('pt_event', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('tenant_id');
    $table->uuid('post_id');
    
    // Event-specific fields
    $table->datetime('start_date');
    $table->datetime('end_date')->nullable();
    $table->string('venue_name')->nullable();
    $table->string('event_type'); // conference, workshop, webinar
    $table->decimal('ticket_price', 8, 2)->nullable();
    $table->boolean('is_virtual')->default(false);
    
    $table->timestamps();
    
    // Standard indexes
    $table->index(['tenant_id', 'event_type']);
    $table->index(['tenant_id', 'start_date']);
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
    $table->unique(['tenant_id', 'post_id']);
});
```

### Step 2: Create Model
Extend the `BasePostType` class:

```php
// app/Models/PostTypes/Event.php
<?php

namespace App\Models\PostTypes;

use Illuminate\Database\Eloquent\Builder;

class Event extends BasePostType
{
    protected $table = 'pt_event';

    protected $fillable = [
        'tenant_id', 'post_id', 'start_date', 'end_date',
        'venue_name', 'event_type', 'ticket_price', 'is_virtual'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'ticket_price' => 'decimal:2',
        'is_virtual' => 'boolean',
    ];

    public static function getPostFamilyConfig(): array
    {
        return [
            'name' => 'Events',
            'description' => 'Conferences, Workshops, and Meetups',
            'table' => 'pt_event',
            'route_prefix' => 'events',
            'kind' => 'event',
            'icon' => 'calendar',
        ];
    }

    public static function getFilterOptions(): array
    {
        return [
            'event_type' => [
                'conference' => 'Conference',
                'workshop' => 'Workshop',
                'webinar' => 'Webinar',
                'meetup' => 'Meetup',
            ],
            'is_virtual' => [
                '1' => 'Virtual',
                '0' => 'In-Person',
            ],
        ];
    }

    public function scopeWithFilters($query, array $filters = []): Builder
    {
        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (isset($filters['is_virtual'])) {
            $query->where('is_virtual', (bool) $filters['is_virtual']);
        }

        if (isset($filters['date_from'])) {
            $query->where('start_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('start_date', '<=', $filters['date_to']);
        }

        return $query;
    }

    public function getStructuredData(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $this->post->title,
            'description' => $this->post->excerpt,
            'startDate' => $this->start_date->toISOString(),
            'endDate' => $this->end_date?->toISOString(),
            'location' => $this->is_virtual ? 'Virtual' : $this->venue_name,
            'url' => $this->post->url,
        ];
    }

    public function getSearchFacets(): array
    {
        return [
            'event_type' => $this->event_type,
            'is_virtual' => $this->is_virtual,
            'has_fee' => !is_null($this->ticket_price) && $this->ticket_price > 0,
        ];
    }

    public function getDisplayAttributes(): array
    {
        return [
            'Event Type' => ucfirst($this->event_type),
            'Date' => $this->start_date->format('M j, Y'),
            'Venue' => $this->is_virtual ? 'Virtual' : ($this->venue_name ?: 'TBD'),
            'Price' => $this->ticket_price ? '$' . number_format($this->ticket_price, 2) : 'Free',
        ];
    }
}
```

### Step 3: Register in PostTypeService
Add the new post type to the service:

```php
// app/Services/PostTypeService.php
public function getRegisteredTypes(): array
{
    return [
        'program' => Program::class,
        'job' => Job::class,
        'event' => Event::class, // Add new post type
    ];
}
```

### Step 4: Update Configuration
Add to tenant configuration:

```php
// config/tenancy.php
'post_families' => [
    'events' => [
        'name' => 'Events',
        'description' => 'Conferences, Workshops, and Meetups',
        'table' => 'pt_event',
        'model' => 'App\\Models\\PostTypes\\Event',
        'controller' => 'App\\Http\\Controllers\\EventController',
        'route_prefix' => 'events',
        'enabled_by_default' => false,
    ],
],
```

### Step 5: Add Routes
Routes are automatically handled by the pattern in `web.php`, but you can customize:

```php
// routes/web.php
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
```

### Step 6: Update Post Model
Add relationship in Post model:

```php
// app/Models/Post.php
public function event(): HasOne
{
    return $this->hasOne(\App\Models\PostTypes\Event::class);
}
```

## Benefits of This Architecture

1. **Consistency**: All post families share the same base structure and patterns
2. **Extensibility**: Easy to add new post types without changing existing code
3. **Tenant Isolation**: All post families respect tenant boundaries
4. **SEO Ready**: Built-in schema.org structured data support
5. **Admin Friendly**: Standardized display attributes and filters
6. **Search Optimized**: Consistent faceting and filtering system

## Best Practices

1. **Follow Naming Conventions**: Use `pt_{family_name}` for table names
2. **Implement All Abstract Methods**: Required for consistency
3. **Add Proper Indexes**: Include tenant_id and common filter fields
4. **Validate Input**: Add validation rules in PostTypeService
5. **Test Thoroughly**: Ensure tenant isolation and data integrity
6. **Document Fields**: Comment complex fields and relationships

## Migration Path

When adding new post families to existing tenants:

1. Run the migration to create the new table
2. Update tenant `enabled_features` to include the new post family
3. Optionally provide a seeder for sample data
4. Update admin interface to include new post family management

This architecture ensures the CMS can grow and adapt to new content requirements while maintaining code quality and system integrity.