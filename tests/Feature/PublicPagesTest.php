<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Program;
use App\Models\Job;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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

        // Set the tenant for the current request
        tenancy()->initialize($this->tenant);
    }

    public function test_homepage_loads_successfully()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Test Organization');
        $response->assertSee('Search opportunities');
    }

    public function test_homepage_displays_featured_content()
    {
        // Create test content
        $user = User::factory()->create();
        
        $program = Program::create([
            'title' => 'Test Scholarship',
            'description' => 'Test scholarship description',
            'type' => 'scholarship',
            'status' => 'published',
            'featured' => true,
            'organization' => 'Test University',
            'deadline' => now()->addDays(30),
            'created_by' => $user->id
        ]);

        $job = Job::create([
            'title' => 'Test Job',
            'description' => 'Test job description',
            'type' => 'full-time',
            'status' => 'published',
            'featured' => true,
            'company' => 'Test Company',
            'location' => 'Remote',
            'created_by' => $user->id
        ]);

        $post = Post::create([
            'title' => 'Test Article',
            'content' => 'Test article content',
            'status' => 'published',
            'published_at' => now(),
            'author' => 'Test Author',
            'created_by' => $user->id
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Test Scholarship');
        $response->assertSee('Test Job');
        $response->assertSee('Test Article');
    }

    public function test_programs_index_page()
    {
        $response = $this->get('/opportunities');

        $response->assertStatus(200);
        $response->assertSee('Opportunities');
    }

    public function test_jobs_index_page()
    {
        $response = $this->get('/jobs');

        $response->assertStatus(200);
        $response->assertSee('Jobs');
    }

    public function test_search_functionality()
    {
        $user = User::factory()->create();
        
        Program::create([
            'title' => 'Engineering Scholarship',
            'description' => 'For engineering students',
            'type' => 'scholarship',
            'status' => 'published',
            'organization' => 'Tech University',
            'created_by' => $user->id
        ]);

        $response = $this->get('/search?q=engineering');

        $response->assertStatus(200);
        $response->assertSee('Engineering Scholarship');
        $response->assertSee('Search Results for "engineering"');
    }

    public function test_search_autocomplete()
    {
        $user = User::factory()->create();
        
        Program::create([
            'title' => 'Data Science Program',
            'description' => 'Learn data science',
            'type' => 'fellowship',
            'status' => 'published',
            'organization' => 'Data Institute',
            'created_by' => $user->id
        ]);

        $response = $this->get('/search/autocomplete?q=data');

        $response->assertStatus(200);
        $response->assertJson(['Data Institute']);
    }

    public function test_program_detail_page()
    {
        $user = User::factory()->create();
        
        $program = Program::create([
            'title' => 'Test Program',
            'slug' => 'test-program',
            'description' => 'Test program description',
            'type' => 'internship',
            'status' => 'published',
            'organization' => 'Test Org',
            'created_by' => $user->id
        ]);

        $response = $this->get('/opportunities/test-program');

        $response->assertStatus(200);
        $response->assertSee('Test Program');
        $response->assertSee('Test program description');
    }

    public function test_job_detail_page()
    {
        $user = User::factory()->create();
        
        $job = Job::create([
            'title' => 'Test Job',
            'slug' => 'test-job',
            'description' => 'Test job description',
            'type' => 'full-time',
            'status' => 'published',
            'company' => 'Test Company',
            'created_by' => $user->id
        ]);

        $response = $this->get('/jobs/test-job');

        $response->assertStatus(200);
        $response->assertSee('Test Job');
        $response->assertSee('Test job description');
    }

    public function test_unpublished_content_not_visible()
    {
        $user = User::factory()->create();
        
        $program = Program::create([
            'title' => 'Draft Program',
            'slug' => 'draft-program',
            'description' => 'This is a draft',
            'type' => 'scholarship',
            'status' => 'draft',
            'organization' => 'Test Org',
            'created_by' => $user->id
        ]);

        $response = $this->get('/opportunities/draft-program');
        $response->assertStatus(404);

        $response = $this->get('/opportunities');
        $response->assertDontSee('Draft Program');
    }

    public function test_seo_meta_tags_present()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<meta name="description"', false);
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta name="twitter:card"', false);
    }
}