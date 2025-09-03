<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Program;
use App\Models\Job;
use App\Models\Post;
use App\Models\Ad;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private $tenant;
    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test tenant
        $this->tenant = Tenant::create([
            'name' => 'Test Organization',
            'slug' => 'test-org',
            'domain' => 'test.example.com',
            'description' => 'Test organization description',
            'status' => 'active'
        ]);

        // Set the tenant for the current request
        tenancy()->initialize($this->tenant);

        // Create admin user
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);
    }

    public function test_admin_dashboard_requires_authentication()
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_admin_dashboard_requires_admin_role()
    {
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);

        $response = $this->actingAs($regularUser)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_admin_can_create_program()
    {
        $programData = [
            'title' => 'New Scholarship',
            'description' => 'A new scholarship program',
            'type' => 'scholarship',
            'status' => 'published',
            'organization' => 'Test University',
            'deadline' => now()->addDays(30)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/programs', $programData);

        $response->assertRedirect();
        $this->assertDatabaseHas('programs', [
            'title' => 'New Scholarship',
            'type' => 'scholarship'
        ]);
    }

    public function test_admin_can_create_job()
    {
        $jobData = [
            'title' => 'Software Engineer',
            'description' => 'Join our development team',
            'type' => 'full-time',
            'status' => 'published',
            'company' => 'Tech Corp',
            'location' => 'San Francisco, CA',
            'salary_min' => 80000,
            'salary_max' => 120000,
        ];

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/jobs', $jobData);

        $response->assertRedirect();
        $this->assertDatabaseHas('jobs', [
            'title' => 'Software Engineer',
            'company' => 'Tech Corp'
        ]);
    }

    public function test_admin_can_create_post()
    {
        $postData = [
            'title' => 'Career Tips',
            'content' => 'Here are some career tips...',
            'status' => 'published',
            'category' => 'career-advice',
            'author' => 'Admin User'
        ];

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/posts', $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'Career Tips',
            'category' => 'career-advice'
        ]);
    }

    public function test_admin_can_create_ad()
    {
        $adData = [
            'title' => 'Test Ad',
            'placement' => 'header',
            'status' => 'active',
            'priority' => 1,
            'content' => ['html' => '<div>Test Ad Content</div>']
        ];

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/ads', $adData);

        $response->assertRedirect();
        $this->assertDatabaseHas('ads', [
            'title' => 'Test Ad',
            'placement' => 'header'
        ]);
    }

    public function test_admin_can_update_program()
    {
        $program = Program::create([
            'title' => 'Original Title',
            'description' => 'Original description',
            'type' => 'scholarship',
            'status' => 'draft',
            'organization' => 'Test Org',
            'created_by' => $this->adminUser->id
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'type' => 'fellowship',
            'status' => 'published',
            'organization' => 'Updated Org'
        ];

        $response = $this->actingAs($this->adminUser)
                         ->put("/admin/programs/{$program->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'title' => 'Updated Title',
            'type' => 'fellowship'
        ]);
    }

    public function test_admin_can_delete_program()
    {
        $program = Program::create([
            'title' => 'To Delete',
            'description' => 'This will be deleted',
            'type' => 'scholarship',
            'status' => 'draft',
            'organization' => 'Test Org',
            'created_by' => $this->adminUser->id
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->delete("/admin/programs/{$program->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('programs', [
            'id' => $program->id
        ]);
    }

    public function test_admin_can_toggle_ad_status()
    {
        $ad = Ad::create([
            'title' => 'Test Ad',
            'placement' => 'sidebar',
            'status' => 'active',
            'priority' => 1,
            'content' => ['html' => '<div>Test</div>'],
            'created_by' => $this->adminUser->id
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->post("/admin/ads/{$ad->id}/toggle");

        $response->assertRedirect();
        $this->assertDatabaseHas('ads', [
            'id' => $ad->id,
            'status' => 'inactive'
        ]);
    }

    public function test_program_validation_rules()
    {
        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/programs', []);

        $response->assertSessionHasErrors(['title', 'description', 'type']);
    }

    public function test_job_validation_rules()
    {
        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/jobs', []);

        $response->assertSessionHasErrors(['title', 'description', 'type']);
    }

    public function test_admin_can_filter_programs_by_status()
    {
        Program::create([
            'title' => 'Published Program',
            'description' => 'Published',
            'type' => 'scholarship',
            'status' => 'published',
            'organization' => 'Test Org',
            'created_by' => $this->adminUser->id
        ]);

        Program::create([
            'title' => 'Draft Program',
            'description' => 'Draft',
            'type' => 'scholarship',
            'status' => 'draft',
            'organization' => 'Test Org',
            'created_by' => $this->adminUser->id
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/programs?status=published');

        $response->assertStatus(200);
        $response->assertSee('Published Program');
        $response->assertDontSee('Draft Program');
    }
}