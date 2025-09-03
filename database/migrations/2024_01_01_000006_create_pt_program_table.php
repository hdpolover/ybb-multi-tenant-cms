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
        Schema::create('pt_program', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('post_id');
            
            // Program specific fields
            $table->enum('program_type', ['scholarship', 'opportunity', 'internship'])->index();
            $table->string('organizer_name');
            $table->string('location_text')->nullable();
            $table->string('country_code', 2)->nullable(); // ISO-3166-1 alpha-2
            $table->datetime('deadline_at')->nullable();
            $table->boolean('is_rolling')->default(false);
            $table->enum('funding_type', ['fully_funded', 'partially_funded', 'unfunded'])->nullable();
            $table->decimal('stipend_amount', 18, 2)->nullable();
            $table->decimal('fee_amount', 18, 2)->nullable();
            $table->string('program_length_text')->nullable(); // e.g., "6 months"
            $table->text('eligibility_text')->nullable();
            $table->string('apply_url');
            $table->json('extra')->nullable(); // Room for additional fields
            
            $table->timestamps();

            // Indexes for filtering and performance
            $table->index(['tenant_id', 'program_type']);
            $table->index(['tenant_id', 'country_code']);
            $table->index(['tenant_id', 'deadline_at']);
            $table->index(['tenant_id', 'funding_type']);
            $table->index(['tenant_id', 'is_rolling']);
            $table->index(['tenant_id', 'program_type', 'country_code', 'deadline_at']);
            
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
        Schema::dropIfExists('pt_program');
    }
};