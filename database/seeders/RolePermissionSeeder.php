<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Posts
            'view_posts',
            'create_posts',
            'edit_posts',
            'delete_posts',
            'publish_posts',
            
            // Programs
            'view_programs',
            'create_programs',
            'edit_programs',
            'delete_programs',
            'publish_programs',
            
            // Jobs
            'view_jobs',
            'create_jobs',
            'edit_jobs',
            'delete_jobs',
            'publish_jobs',
            
            // Media
            'view_media',
            'upload_media',
            'delete_media',
            
            // Users
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Ads
            'view_ads',
            'create_ads',
            'edit_ads',
            'delete_ads',
            
            // SEO
            'manage_seo',
            'view_analytics',
            
            // Settings
            'manage_settings',
            'manage_tenant',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        $roles = [
            'TenantOwner' => [
                'manage_tenant', 'manage_settings', 'view_analytics', 'manage_seo',
                'view_posts', 'create_posts', 'edit_posts', 'delete_posts', 'publish_posts',
                'view_programs', 'create_programs', 'edit_programs', 'delete_programs', 'publish_programs',
                'view_jobs', 'create_jobs', 'edit_jobs', 'delete_jobs', 'publish_jobs',
                'view_media', 'upload_media', 'delete_media',
                'view_users', 'create_users', 'edit_users', 'delete_users',
                'view_ads', 'create_ads', 'edit_ads', 'delete_ads',
            ],
            'Admin' => [
                'view_analytics', 'manage_seo',
                'view_posts', 'create_posts', 'edit_posts', 'delete_posts', 'publish_posts',
                'view_programs', 'create_programs', 'edit_programs', 'delete_programs', 'publish_programs',
                'view_jobs', 'create_jobs', 'edit_jobs', 'delete_jobs', 'publish_jobs',
                'view_media', 'upload_media', 'delete_media',
                'view_users', 'create_users', 'edit_users',
                'view_ads', 'create_ads', 'edit_ads', 'delete_ads',
            ],
            'Editor' => [
                'view_posts', 'create_posts', 'edit_posts', 'publish_posts',
                'view_programs', 'create_programs', 'edit_programs', 'publish_programs',
                'view_jobs', 'create_jobs', 'edit_jobs', 'publish_jobs',
                'view_media', 'upload_media',
                'view_users',
            ],
            'Author' => [
                'view_posts', 'create_posts', 'edit_posts',
                'view_programs', 'create_programs', 'edit_programs',
                'view_jobs', 'create_jobs', 'edit_jobs',
                'view_media', 'upload_media',
            ],
            'SEO' => [
                'view_posts', 'edit_posts', 'manage_seo', 'view_analytics',
                'view_programs', 'edit_programs',
                'view_jobs', 'edit_jobs',
            ],
            'Moderator' => [
                'view_posts', 'edit_posts', 'publish_posts',
                'view_programs', 'edit_programs', 'publish_programs',
                'view_jobs', 'edit_jobs', 'publish_jobs',
                'view_users',
            ],
            'Analyst' => [
                'view_posts', 'view_programs', 'view_jobs',
                'view_analytics', 'view_users', 'view_media', 'view_ads',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }
    }
}