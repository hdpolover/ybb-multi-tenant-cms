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
        Schema::create('ads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('banner'); // banner, popup, sidebar, inline, video
            $table->string('placement'); // header, footer, sidebar, content, popup
            $table->json('content'); // HTML, image URLs, video URLs, etc.
            $table->json('targeting')->nullable(); // URL patterns, post types, categories, etc.
            $table->json('settings')->nullable(); // dimensions, display rules, etc.
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher priority ads show first
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->integer('max_impressions')->nullable();
            $table->integer('max_clicks')->nullable();
            $table->integer('current_impressions')->default(0);
            $table->integer('current_clicks')->default(0);
            $table->decimal('click_rate', 5, 2)->default(0.00);
            $table->string('status')->default('active'); // active, paused, expired, completed
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['tenant_id', 'is_active', 'placement']);
            $table->index(['tenant_id', 'status', 'priority']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};