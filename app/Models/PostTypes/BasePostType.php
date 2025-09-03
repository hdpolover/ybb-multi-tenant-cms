<?php

namespace App\Models\PostTypes;

use App\Models\Post;
use App\Traits\TenantAware;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class BasePostType extends Model
{
    use HasFactory, HasUuids, TenantAware;

    /**
     * Get the post this belongs to
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Boot the model and auto-set tenant_id from related post
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->tenant_id && $model->post) {
                $model->tenant_id = $model->post->tenant_id;
            }
        });
    }

    /**
     * Get the post family configuration
     */
    abstract public static function getPostFamilyConfig(): array;

    /**
     * Get the filter options for this post type
     */
    abstract public static function getFilterOptions(): array;

    /**
     * Apply filters to query
     */
    abstract public function scopeWithFilters($query, array $filters = []);

    /**
     * Get the schema.org structured data
     */
    abstract public function getStructuredData(): array;

    /**
     * Get the search facets for this post type
     */
    abstract public function getSearchFacets(): array;

    /**
     * Get displayable attributes for admin interface
     */
    abstract public function getDisplayAttributes(): array;
}