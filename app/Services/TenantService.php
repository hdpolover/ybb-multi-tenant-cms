<?php

namespace App\Services;

use App\Models\Tenant;

class TenantService
{
    /**
     * Get the current tenant
     */
    public function current(): ?Tenant
    {
        return app('current_tenant');
    }

    /**
     * Check if we are in tenant context
     */
    public function hasTenant(): bool
    {
        return app()->bound('current_tenant') && !is_null(app('current_tenant'));
    }

    /**
     * Check if current domain is network admin
     */
    public function isNetworkAdmin(): bool
    {
        $domain = request()->getHost();
        return $domain === config('app.tenancy.network_admin_domain');
    }

    /**
     * Get tenant by domain
     */
    public function findByDomain(string $domain): ?Tenant
    {
        return Tenant::findByDomain($domain);
    }

    /**
     * Create a new tenant
     */
    public function create(array $data): Tenant
    {
        return Tenant::create($data);
    }

    /**
     * Get all active tenants (for network admin)
     */
    public function getAllActive()
    {
        return Tenant::where('status', 'active')->get();
    }

    /**
     * Check if a feature is enabled for current tenant
     */
    public function hasFeature(string $feature): bool
    {
        $tenant = $this->current();
        return $tenant ? $tenant->hasFeature($feature) : false;
    }

    /**
     * Get tenant setting
     */
    public function getSetting(string $key, $default = null)
    {
        $tenant = $this->current();
        return $tenant ? $tenant->getSetting($key, $default) : $default;
    }

    /**
     * Set tenant setting
     */
    public function setSetting(string $key, $value): bool
    {
        $tenant = $this->current();
        if ($tenant) {
            $tenant->setSetting($key, $value);
            return $tenant->save();
        }
        return false;
    }
}