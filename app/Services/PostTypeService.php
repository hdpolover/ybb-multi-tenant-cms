<?php

namespace App\Services;

use App\Models\PostTypes\Program;
use App\Models\PostTypes\Job;
use App\Models\PostTypes\BasePostType;

class PostTypeService
{
    /**
     * Get all registered post types
     */
    public function getRegisteredTypes(): array
    {
        return [
            'program' => Program::class,
            'job' => Job::class,
            // Future post types can be added here
            // 'event' => Event::class,
            // 'course' => Course::class,
        ];
    }

    /**
     * Get post type class by kind
     */
    public function getPostTypeClass(string $kind): ?string
    {
        return $this->getRegisteredTypes()[$kind] ?? null;
    }

    /**
     * Get post type instance by kind
     */
    public function getPostTypeInstance(string $kind): ?BasePostType
    {
        $class = $this->getPostTypeClass($kind);
        return $class ? new $class : null;
    }

    /**
     * Get all post family configurations
     */
    public function getAllConfigurations(): array
    {
        $configurations = [];
        
        foreach ($this->getRegisteredTypes() as $kind => $class) {
            $configurations[$kind] = $class::getPostFamilyConfig();
        }

        return $configurations;
    }

    /**
     * Get configuration for specific post type
     */
    public function getConfiguration(string $kind): ?array
    {
        $class = $this->getPostTypeClass($kind);
        return $class ? $class::getPostFamilyConfig() : null;
    }

    /**
     * Get filter options for specific post type
     */
    public function getFilterOptions(string $kind): array
    {
        $class = $this->getPostTypeClass($kind);
        return $class ? $class::getFilterOptions() : [];
    }

    /**
     * Get all filter options for all post types
     */
    public function getAllFilterOptions(): array
    {
        $options = [];
        
        foreach ($this->getRegisteredTypes() as $kind => $class) {
            $options[$kind] = $class::getFilterOptions();
        }

        return $options;
    }

    /**
     * Check if a post type is enabled for current tenant
     */
    public function isEnabledForTenant(string $kind): bool
    {
        $tenant = app('tenant.service')->current();
        
        if (!$tenant) {
            return false;
        }

        return $tenant->hasFeature($kind);
    }

    /**
     * Get enabled post types for current tenant
     */
    public function getEnabledForTenant(): array
    {
        $enabled = [];
        
        foreach ($this->getRegisteredTypes() as $kind => $class) {
            if ($this->isEnabledForTenant($kind)) {
                $enabled[$kind] = $class;
            }
        }

        return $enabled;
    }

    /**
     * Get route prefix for post type
     */
    public function getRoutePrefix(string $kind): ?string
    {
        $config = $this->getConfiguration($kind);
        return $config['route_prefix'] ?? null;
    }

    /**
     * Get model class for post type
     */
    public function getModelClass(string $kind): ?string
    {
        return $this->getPostTypeClass($kind);
    }

    /**
     * Create post type data for a post
     */
    public function createPostTypeData(string $kind, array $data): ?BasePostType
    {
        $class = $this->getPostTypeClass($kind);
        
        if (!$class) {
            return null;
        }

        return $class::create($data);
    }

    /**
     * Update post type data
     */
    public function updatePostTypeData(BasePostType $postType, array $data): bool
    {
        return $postType->update($data);
    }

    /**
     * Delete post type data
     */
    public function deletePostTypeData(BasePostType $postType): bool
    {
        return $postType->delete();
    }

    /**
     * Get structured data for SEO
     */
    public function getStructuredData(BasePostType $postType): array
    {
        return $postType->getStructuredData();
    }

    /**
     * Get search facets for post type
     */
    public function getSearchFacets(BasePostType $postType): array
    {
        return $postType->getSearchFacets();
    }

    /**
     * Get display attributes for admin interface
     */
    public function getDisplayAttributes(BasePostType $postType): array
    {
        return $postType->getDisplayAttributes();
    }

    /**
     * Validate post type data
     */
    public function validatePostTypeData(string $kind, array $data): array
    {
        $errors = [];
        
        // Basic validation - can be extended per post type
        switch ($kind) {
            case 'program':
                if (empty($data['organizer_name'])) {
                    $errors[] = 'Organizer name is required for programs';
                }
                if (empty($data['apply_url'])) {
                    $errors[] = 'Apply URL is required for programs';
                }
                break;
                
            case 'job':
                if (empty($data['company_name'])) {
                    $errors[] = 'Company name is required for jobs';
                }
                if (empty($data['apply_url'])) {
                    $errors[] = 'Apply URL is required for jobs';
                }
                break;
        }

        return $errors;
    }

    /**
     * Register a new post type (for future extensibility)
     */
    public function registerPostType(string $kind, string $class): void
    {
        // This could be enhanced to store registrations in config or database
        // For now, post types are registered in getRegisteredTypes()
    }
}