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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            
            // Event details
            $table->string('event_type'); // 'page_view', 'search', 'apply_click', 'newsletter_signup', etc.
            $table->string('event_category')->nullable(); // 'engagement', 'conversion', 'navigation'
            $table->string('event_action')->nullable(); // 'click', 'view', 'submit', 'download'
            $table->string('event_label')->nullable(); // Additional context
            $table->decimal('event_value', 10, 2)->nullable(); // Numeric value for the event
            
            // Page/content context
            $table->string('page_url')->nullable();
            $table->string('page_title')->nullable();
            $table->uuid('content_id')->nullable(); // Related post/program/job ID
            $table->string('content_type')->nullable(); // 'program', 'job', 'post'
            
            // User context
            $table->uuid('user_id')->nullable(); // If logged in
            $table->string('session_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->json('utm_params')->nullable(); // UTM tracking parameters
            
            // Device/browser info
            $table->string('device_type')->nullable(); // mobile, tablet, desktop
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            
            // Additional metadata
            $table->json('custom_data')->nullable(); // Flexible field for additional tracking
            
            $table->timestamp('created_at')->index();

            // Indexes for analytics queries
            $table->index(['tenant_id', 'event_type', 'created_at']);
            $table->index(['tenant_id', 'content_id', 'content_type']);
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'session_id']);
            $table->index(['tenant_id', 'event_category', 'created_at']);
            $table->index(['tenant_id', 'created_at']); // For time-based reports
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};