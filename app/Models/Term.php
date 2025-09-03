<?php

namespace App\Models;

use App\Traits\TenantAware;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Term extends Model
{
    use HasFactory, HasUuids, TenantAware, HasSlug;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'type',
        'parent_id',
        'color',
        'icon',
        'meta',
        'is_featured',
        'post_count',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_featured' => 'boolean',
        'post_count' => 'integer',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
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
     * Get the parent term
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'parent_id');
    }

    /**
     * Get the child terms
     */
    public function children(): HasMany
    {
        return $this->hasMany(Term::class, 'parent_id');
    }

    /**
     * Get the posts associated with this term
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'term_post')
                    ->withTimestamps();
    }

    /**
     * Scope to specific type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to featured terms
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to root terms (no parent)
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to child terms (has parent)
     */
    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Get all descendants of this term
     */
    public function descendants(): HasMany
    {
        return $this->hasMany(Term::class, 'parent_id')->with('descendants');
    }

    /**
     * Get all ancestors of this term
     */
    public function getAncestorsAttribute()
    {
        $ancestors = collect();
        $current = $this->parent;
        
        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }
        
        return $ancestors;
    }

    /**
     * Get the full path of this term (including ancestors)
     */
    public function getFullPathAttribute(): string
    {
        $path = $this->ancestors->pluck('name')->toArray();
        $path[] = $this->name;
        
        return implode(' > ', $path);
    }

    /**
     * Update post count for this term
     */
    public function updatePostCount(): void
    {
        $this->update([
            'post_count' => $this->posts()->count()
        ]);
    }

    /**
     * Get terms by type
     */
    public static function getByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('type', $type)->orderBy('name')->get();
    }

    /**
     * Get available term types
     */
    public static function getAvailableTypes(): array
    {
        return [
            'category' => 'Categories',
            'tag' => 'Tags',
            'location' => 'Locations',
            'skill' => 'Skills',
            'industry' => 'Industries',
        ];
    }

    /**
     * Get terms for select options
     */
    public static function getSelectOptions(string $type = null): array
    {
        $query = static::query();
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray();
    }

    /**
     * Get hierarchical terms for select options
     */
    public static function getHierarchicalOptions(string $type = null): array
    {
        $query = static::with(['children' => function ($q) {
            $q->orderBy('name');
        }])->roots()->orderBy('name');
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $terms = $query->get();
        $options = [];
        
        foreach ($terms as $term) {
            $options[$term->id] = $term->name;
            
            foreach ($term->children as $child) {
                $options[$child->id] = '— ' . $child->name;
            }
        }
        
        return $options;
    }

    /**
     * Increment post count
     */
    public function incrementPostCount(): void
    {
        $this->increment('post_count');
    }

    /**
     * Decrement post count
     */
    public function decrementPostCount(): void
    {
        $this->decrement('post_count');
    }
}