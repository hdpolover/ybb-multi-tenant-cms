<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seo_landings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            
            // SEO Landing page details
            $table->string('title');
            $table->string('slug');
            $table->text('meta_description')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('canonical_url')->nullable();
            $table->text('content')->nullable(); // Rich content for the landing page
            $table->json('schema_markup')->nullable(); // Structured data JSON-LD
            
            // Targeting and filters
            $table->string('target_keyword')->nullable(); // Primary keyword
            $table->json('target_filters')->nullable(); // Auto-applied filters for programs/jobs
            $table->enum('content_type', ['programs', 'jobs', 'mixed'])->default('mixed');
            $table->integer('items_per_page')->default(20);
            
            // Performance tracking
            $table->unsignedInteger('views')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            
            // SEO status
            $table->enum('index_status', ['index', 'noindex'])->default('index');
            $table->enum('follow_status', ['follow', 'nofollow'])->default('follow');
            
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'content_type']);
            $table->unique(['tenant_id', 'slug']);
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_landings');
    }
};