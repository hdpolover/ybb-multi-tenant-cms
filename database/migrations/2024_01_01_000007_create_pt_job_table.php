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
        Schema::create('pt_job', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('post_id');
            
            // Job specific fields
            $table->string('company_name');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'internship'])->index();
            $table->enum('workplace_type', ['onsite', 'hybrid', 'remote'])->index();
            $table->string('title_override')->nullable(); // If different from post title
            $table->string('location_city')->nullable();
            $table->string('country_code', 2)->nullable(); // ISO-3166-1 alpha-2
            $table->decimal('min_salary', 18, 2)->nullable();
            $table->decimal('max_salary', 18, 2)->nullable();
            $table->string('salary_currency', 3)->nullable(); // ISO-4217
            $table->enum('salary_period', ['year', 'month', 'day', 'hour'])->nullable();
            $table->enum('experience_level', ['junior', 'mid', 'senior', 'lead'])->nullable()->index();
            $table->longText('responsibilities')->nullable();
            $table->longText('requirements')->nullable();
            $table->json('benefits')->nullable(); // List of benefits
            $table->datetime('deadline_at')->nullable();
            $table->string('apply_url');
            $table->json('extra')->nullable(); // Room for additional fields
            
            $table->timestamps();

            // Indexes for filtering and performance
            $table->index(['tenant_id', 'workplace_type']);
            $table->index(['tenant_id', 'employment_type']);
            $table->index(['tenant_id', 'country_code']);
            $table->index(['tenant_id', 'experience_level']);
            $table->index(['tenant_id', 'deadline_at']);
            $table->index(['tenant_id', 'company_name']);
            $table->index(['tenant_id', 'workplace_type', 'employment_type']);
            $table->index(['tenant_id', 'min_salary', 'max_salary']);
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->unique(['tenant_id', 'post_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pt_job');
    }
};