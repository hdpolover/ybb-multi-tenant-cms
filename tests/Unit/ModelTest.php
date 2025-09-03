<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Program;
use App\Models\Job;
use App\Models\Post;
use App\Models\Ad;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    private $tenant;

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

        tenancy()->initialize($this->tenant);
    }

    public function test_tenant_model_creation()
    {
        $tenant = Tenant::create([
            'name' => 'New Tenant',
            'slug' => 'new-tenant',
            'domain' => 'new.example.com',
            'description' => 'New tenant description',
            'status' => 'active'
        ]);

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertEquals('New Tenant', $tenant->name);
        $this->assertEquals('new-tenant', $tenant->slug);
    }

    public function test_user_model_creation()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('user', $user->role);
    }

    public function test_program_model_creation_and_slug_generation()
    {
        $user = User::create([
            'name' => 'Creator',
            'email' => 'creator@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $program = Program::create([
            'title' => 'Test Scholarship Program',
            'description' => 'A comprehensive scholarship program',
            'type' => 'scholarship',
            'status' => 'published',
            'organization' => 'Test University',
            'created_by' => $user->id
        ]);

        $this->assertInstanceOf(Program::class, $program);
        $this->assertEquals('Test Scholarship Program', $program->title);
        $this->assertEquals('test-scholarship-program', $program->slug);
        $this->assertEquals('scholarship', $program->type);
    }

    public function test_job_model_creation_and_relationships()
    {
        $user = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $job = Job::create([
            'title' => 'Software Engineer Position',
            'description' => 'Join our development team',
            'type' => 'full-time',
            'status' => 'published',
            'company' => 'Tech Corp',
            'location' => 'San Francisco, CA',
            'salary_min' => 80000,
            'salary_max' => 120000,
            'remote' => true,
            'created_by' => $user->id
        ]);

        $this->assertInstanceOf(Job::class, $job);
        $this->assertEquals('Software Engineer Position', $job->title);
        $this->assertEquals('software-engineer-position', $job->slug);
        $this->assertTrue($job->remote);
        $this->assertEquals($user->id, $job->created_by);
        $this->assertInstanceOf(User::class, $job->creator);
    }

    public function test_post_model_creation_and_publishing()
    {
        $user = User::create([
            'name' => 'Content Creator',
            'email' => 'content@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $post = Post::create([
            'title' => 'Career Development Tips',
            'content' => 'Here are some valuable career tips...',
            'excerpt' => 'Career tips for success',
            'status' => 'published',
            'category' => 'career-advice',
            'author' => 'Content Creator',
            'published_at' => now(),
            'created_by' => $user->id
        ]);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Career Development Tips', $post->title);
        $this->assertEquals('career-development-tips', $post->slug);
        $this->assertTrue($post->isPublished());
        $this->assertInstanceOf(User::class, $post->creator);
    }

    public function test_ad_model_creation_and_content_handling()
    {
        $user = User::create([
            'name' => 'Marketing Manager',
            'email' => 'marketing@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $ad = Ad::create([
            'title' => 'Banner Advertisement',
            'placement' => 'header',
            'status' => 'active',
            'priority' => 1,
            'content' => [
                'html' => '<div class="ad-banner">Click here!</div>',
                'url' => 'https://example.com'
            ],
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'created_by' => $user->id
        ]);

        $this->assertInstanceOf(Ad::class, $ad);
        $this->assertEquals('Banner Advertisement', $ad->title);
        $this->assertTrue($ad->isActive());
        $this->assertIsArray($ad->content);
        $this->assertEquals('header', $ad->placement);
    }

    public function test_program_scopes()
    {
        $user = User::create([
            'name' => 'Creator',
            'email' => 'creator@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        // Create published program
        Program::create([
            'title' => 'Published Program',
            'description' => 'Published program',
            'type' => 'scholarship',
            'status' => 'published',
            'featured' => true,
            'organization' => 'Test Org',
            'created_by' => $user->id
        ]);

        // Create draft program
        Program::create([
            'title' => 'Draft Program',
            'description' => 'Draft program',
            'type' => 'internship',
            'status' => 'draft',
            'featured' => false,
            'organization' => 'Test Org',
            'created_by' => $user->id
        ]);

        $publishedPrograms = Program::published()->get();
        $featuredPrograms = Program::featured()->get();

        $this->assertEquals(1, $publishedPrograms->count());
        $this->assertEquals(1, $featuredPrograms->count());
        $this->assertEquals('Published Program', $publishedPrograms->first()->title);
    }

    public function test_job_salary_formatting()
    {
        $user = User::create([
            'name' => 'HR',
            'email' => 'hr@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $job = Job::create([
            'title' => 'Developer',
            'description' => 'Job description',
            'type' => 'full-time',
            'status' => 'published',
            'company' => 'Tech Co',
            'salary_min' => 75000,
            'salary_max' => 95000,
            'created_by' => $user->id
        ]);

        $this->assertEquals('$75,000 - $95,000', $job->getFormattedSalaryRange());
    }

    public function test_tenant_relationship_isolation()
    {
        // Create another tenant
        $otherTenant = Tenant::create([
            'name' => 'Other Organization',
            'slug' => 'other-org',
            'domain' => 'other.example.com',
            'description' => 'Other organization',
            'status' => 'active'
        ]);

        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        Program::create([
            'title' => 'Tenant 1 Program',
            'description' => 'Program for tenant 1',
            'type' => 'scholarship',
            'status' => 'published',
            'organization' => 'Tenant 1',
            'created_by' => $user1->id
        ]);

        // Switch to other tenant
        tenancy()->initialize($otherTenant);

        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        Program::create([
            'title' => 'Tenant 2 Program',
            'description' => 'Program for tenant 2',
            'type' => 'fellowship',
            'status' => 'published',
            'organization' => 'Tenant 2',
            'created_by' => $user2->id
        ]);

        // Verify tenant isolation
        $tenant2Programs = Program::all();
        $this->assertEquals(1, $tenant2Programs->count());
        $this->assertEquals('Tenant 2 Program', $tenant2Programs->first()->title);
    }

    public function test_model_validation_attributes()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        // Test required fields
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Program::create([
            'description' => 'Missing title',
            'type' => 'scholarship',
            'status' => 'published',
            'created_by' => $user->id
        ]);
    }
}