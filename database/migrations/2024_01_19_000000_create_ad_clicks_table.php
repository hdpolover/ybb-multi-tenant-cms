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
        Schema::create('ad_clicks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('ad_id');
            $table->uuid('impression_id')->nullable(); // Link to specific impression
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->string('page_url');
            $table->string('click_url')->nullable(); // The URL the ad redirected to
            $table->json('device_info')->nullable();
            $table->json('location_info')->nullable();
            $table->timestamp('clicked_at');
            $table->timestamps();
            
            // Indexes
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('ad_id')->references('id')->on('ads')->onDelete('cascade');
            $table->foreign('impression_id')->references('id')->on('ad_impressions')->onDelete('set null');
            $table->index(['tenant_id', 'ad_id', 'clicked_at']);
            $table->index(['clicked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_clicks');
    }
};