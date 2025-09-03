<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample tenant
        $tenantId = Str::uuid();
        
        DB::table('tenants')->insert([
            'id' => $tenantId,
            'name' => 'Youth Bridge Initiative',
            'domain' => 'youthbridge.org',
            'description' => 'Connecting youth with global opportunities for education, employment, and leadership development.',
            'primary_color' => '#2563eb',
            'secondary_color' => '#64748b',
            'accent_color' => '#10b981',
            'meta_title' => 'Youth Bridge Initiative - Global Opportunities for Young Leaders',
            'meta_description' => 'Discover scholarships, internships, job opportunities, and leadership programs designed for ambitious young people worldwide.',
            'email_from_name' => 'Youth Bridge Initiative',
            'email_from_address' => 'hello@youthbridge.org',
            'gdpr_enabled' => true,
            'ccpa_enabled' => false,
            'enabled_features' => json_encode([
                'programs', 'jobs', 'news', 'guides', 'newsletter', 'seo'
            ]),
            'settings' => json_encode([
                'items_per_page' => 20,
                'allow_comments' => true,
                'moderate_comments' => true,
                'enable_search' => true,
                'enable_social_sharing' => true
            ]),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create corresponding domain entry
        DB::table('domains')->insert([
            'domain' => 'youthbridge.org',
            'tenant_id' => $tenantId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create additional sample domain (for subdomain or custom domain)
        DB::table('domains')->insert([
            'domain' => 'ybi.example.com',
            'tenant_id' => $tenantId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create sample terms (categories)
        $categoryTerms = [
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => 'Scholarships',
                'slug' => 'scholarships',
                'description' => 'Educational funding opportunities',
                'type' => 'category',
                'color' => '#3b82f6',
                'is_featured' => true,
                'post_count' => 0,
            ],
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => 'Internships',
                'slug' => 'internships',
                'description' => 'Professional experience opportunities',
                'type' => 'category',
                'color' => '#10b981',
                'is_featured' => true,
                'post_count' => 0,
            ],
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => 'Remote Jobs',
                'slug' => 'remote-jobs',
                'description' => 'Work from anywhere opportunities',
                'type' => 'category',
                'color' => '#8b5cf6',
                'is_featured' => true,
                'post_count' => 0,
            ],
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => 'Career Development',
                'slug' => 'career-development',
                'description' => 'Professional growth resources',
                'type' => 'category',
                'color' => '#f59e0b',
                'is_featured' => false,
                'post_count' => 0,
            ],
        ];

        foreach ($categoryTerms as $term) {
            $term['created_at'] = now();
            $term['updated_at'] = now();
            DB::table('terms')->insert($term);
        }

        // Create sample tags
        $tagTerms = [
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => 'STEM',
                'slug' => 'stem',
                'description' => 'Science, Technology, Engineering, Mathematics',
                'type' => 'tag',
                'color' => '#dc2626',
            ],
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Business and entrepreneurship',
                'type' => 'tag',
                'color' => '#059669',
            ],
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => 'Arts & Humanities',
                'slug' => 'arts-humanities',
                'description' => 'Creative and cultural fields',
                'type' => 'tag',
                'color' => '#7c3aed',
            ],
        ];

        foreach ($tagTerms as $term) {
            $term['is_featured'] = false;
            $term['post_count'] = 0;
            $term['created_at'] = now();
            $term['updated_at'] = now();
            DB::table('terms')->insert($term);
        }

        // Create sample locations
        $locationTerms = [
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => 'United States',
                'slug' => 'united-states',
                'type' => 'location',
                'meta' => json_encode(['country_code' => 'US']),
            ],
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => 'United Kingdom',
                'slug' => 'united-kingdom',
                'type' => 'location',
                'meta' => json_encode(['country_code' => 'GB']),
            ],
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => 'Canada',
                'slug' => 'canada',
                'type' => 'location',
                'meta' => json_encode(['country_code' => 'CA']),
            ],
            [
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => 'Global/Remote',
                'slug' => 'global-remote',
                'type' => 'location',
                'meta' => json_encode(['country_code' => null, 'remote' => true]),
            ],
        ];

        foreach ($locationTerms as $term) {
            $term['description'] = null;
            $term['color'] = null;
            $term['is_featured'] = false;
            $term['post_count'] = 0;
            $term['created_at'] = now();
            $term['updated_at'] = now();
            DB::table('terms')->insert($term);
        }

        $this->command->info('Sample tenant "Youth Bridge Initiative" created successfully!');
        $this->command->info('Domain: youthbridge.org');
        $this->command->info('Tenant ID: ' . $tenantId);
    }
}