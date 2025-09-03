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
        Schema::create('redirects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            
            // Redirect details
            $table->string('from_url'); // Original URL path
            $table->string('to_url'); // Destination URL (can be external)
            $table->enum('status_code', ['301', '302', '307', '308'])->default('301');
            $table->text('description')->nullable(); // Why this redirect exists
            
            // Tracking
            $table->unsignedInteger('hits')->default(0); // Number of times used
            $table->datetime('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Automatic vs manual
            $table->boolean('is_automatic')->default(false); // Created by system vs user
            $table->string('created_reason')->nullable(); // 'slug_change', 'post_deleted', etc.
            
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'from_url']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'status_code']);
            $table->unique(['tenant_id', 'from_url']);
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};