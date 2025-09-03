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
        Schema::create('terms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('type', ['category', 'tag', 'location', 'skill', 'industry'])->index();
            $table->uuid('parent_id')->nullable(); // For hierarchical terms
            $table->string('color', 7)->nullable(); // Hex color for UI
            $table->string('icon')->nullable(); // Icon class or URL
            $table->json('meta')->nullable(); // Additional metadata
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('post_count')->default(0); // Cached post count
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'parent_id']);
            $table->index(['tenant_id', 'is_featured']);
            $table->unique(['tenant_id', 'slug', 'type']);
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('terms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};