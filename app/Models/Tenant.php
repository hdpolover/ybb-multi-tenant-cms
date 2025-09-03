<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'domain',
        'logo_url',
        'description',
        'primary_color',
        'secondary_color',
        'accent_color',
        'meta_title',
        'meta_description',
        'og_image_url',
        'favicon_url',
        'google_analytics_id',
        'google_adsense_id',
        'google_tag_manager_id',
        'email_from_name',
        'email_from_address',
        'gdpr_enabled',
        'ccpa_enabled',
        'privacy_policy_url',
        'terms_of_service_url',
        'enabled_features',
        'settings',
        'status',
    ];

    protected $casts = [
        'enabled_features' => 'array',
        'settings' => 'array',
        'gdpr_enabled' => 'boolean',
        'ccpa_enabled' => 'boolean',
    ];

    /**
     * Get the users for this tenant
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the posts for this tenant
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the ads for this tenant
     */
    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    /**
     * Get the media for this tenant
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Check if a feature is enabled for this tenant
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->enabled_features ?? []);
    }

    /**
     * Get a setting value
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a setting value
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
    }

    /**
     * Find tenant by domain
     */
    public static function findByDomain(string $domain): ?self
    {
        return static::where('domain', $domain)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Get the full URL for this tenant
     */
    public function getUrlAttribute(): string
    {
        return 'https://' . $this->domain;
    }

    /**
     * Get the admin URL for this tenant
     */
    public function getAdminUrlAttribute(): string
    {
        return $this->url . '/admin';
    }
}