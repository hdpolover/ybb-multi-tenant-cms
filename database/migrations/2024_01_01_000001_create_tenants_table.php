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
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('logo_url')->nullable();
            $table->text('description')->nullable();
            
            // Branding
            $table->string('primary_color', 7)->default('#3b82f6');
            $table->string('secondary_color', 7)->default('#64748b');
            $table->string('accent_color', 7)->default('#10b981');
            
            // SEO defaults
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image_url')->nullable();
            $table->string('favicon_url')->nullable();
            
            // Analytics & Ads
            $table->string('google_analytics_id')->nullable();
            $table->string('google_adsense_id')->nullable();
            $table->string('google_tag_manager_id')->nullable();
            
            // Email configuration
            $table->string('email_from_name')->nullable();
            $table->string('email_from_address')->nullable();
            
            // Compliance
            $table->boolean('gdpr_enabled')->default(false);
            $table->boolean('ccpa_enabled')->default(false);
            $table->text('privacy_policy_url')->nullable();
            $table->text('terms_of_service_url')->nullable();
            
            // Features
            $table->json('enabled_features')->nullable(); // ['programs', 'jobs', 'news', etc.]
            $table->json('settings')->nullable(); // flexible key-value store
            
            // Status
            $table->enum('status', ['active', 'suspended', 'pending'])->default('active');
            
            $table->timestamps();
            
            // Indexes
            $table->index('domain');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};