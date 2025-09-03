<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait TenantAware
{
    /**
     * Boot the trait
     */
    public static function bootTenantAware(): void
    {
        // Auto-set tenant_id when creating
        static::creating(function (Model $model) {
            if (! $model->tenant_id) {
                $model->tenant_id = app('current_tenant')?->id;
            }
        });

        // Auto-scope queries to current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('current_tenant') && app('current_tenant')) {
                $builder->where('tenant_id', app('current_tenant')->id);
            }
        });
    }

    /**
     * Get the tenant this model belongs to
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope query to specific tenant
     */
    public function scopeForTenant(Builder $query, Tenant $tenant): Builder
    {
        return $query->where('tenant_id', $tenant->id);
    }

    /**
     * Check if model belongs to current tenant
     */
    public function belongsToCurrentTenant(): bool
    {
        return $this->tenant_id === app('current_tenant')?->id;
    }
}