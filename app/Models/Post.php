<?php

namespace App\Models;

use App\Traits\TenantAware;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use HasFactory, HasUuids, TenantAware, HasSlug;

    protected $fillable = [
        'tenant_id',
        'kind',
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'cover_image_url',
        'meta_title',
        'meta_description',
        'og_image_url',
        'canonical_url',
        'published_at',
        'scheduled_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the user who created this post
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this post
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the program data (if this is a program post)
     */
    public function program(): HasOne
    {
        return $this->hasOne(\App\Models\PostTypes\Program::class);
    }

    /**
     * Get the job data (if this is a job post)
     */
    public function job(): HasOne
    {
        return $this->hasOne(\App\Models\PostTypes\Job::class);
    }

    /**
     * Get the terms associated with this post
     */
    public function terms(): BelongsToMany
    {
        return $this->belongsToMany(Term::class, 'term_post');
    }

    /**
     * Get the media associated with this post
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Scope to published posts
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope to specific kind
     */
    public function scopeOfKind($query, string $kind)
    {
        return $query->where('kind', $kind);
    }

    /**
     * Scope to search posts
     */
    public function scopeSearch($query, string $search)
    {
        return $query->whereRaw(
            "MATCH(title, excerpt, content) AGAINST(? IN NATURAL LANGUAGE MODE)",
            [$search]
        );
    }

    /**
     * Check if post is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && 
               $this->published_at && 
               $this->published_at <= now();
    }

    /**
     * Check if post is scheduled
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && 
               $this->scheduled_at && 
               $this->scheduled_at > now();
    }

    /**
     * Get the URL for this post
     */
    public function getUrlAttribute(): string
    {
        $tenant = $this->tenant;
        $baseUrl = $tenant ? $tenant->url : config('app.url');
        
        switch ($this->kind) {
            case 'program':
                return $baseUrl . '/opportunities/' . $this->slug;
            case 'job':
                return $baseUrl . '/jobs/' . $this->slug;
            default:
                return $baseUrl . '/' . $this->slug;
        }
    }

    /**
     * Get SEO title (falls back to title)
     */
    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: $this->title;
    }

    /**
     * Get SEO description (falls back to excerpt)
     */
    public function getSeoDescriptionAttribute(): string
    {
        return $this->meta_description ?: $this->excerpt ?: '';
    }

    /**
     * Get the post type data (program, job, etc.)
     */
    public function getTypeDataAttribute()
    {
        switch ($this->kind) {
            case 'program':
                return $this->program;
            case 'job':
                return $this->job;
            default:
                return null;
        }
    }
}