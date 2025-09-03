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
        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('post_id')->nullable(); // Can be associated with a post or standalone
            $table->uuid('uploaded_by')->nullable();
            
            $table->string('name'); // Original filename
            $table->string('file_name'); // Stored filename
            $table->string('mime_type');
            $table->string('disk')->default('public');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size'); // File size in bytes
            $table->json('manipulations')->nullable();
            $table->json('custom_properties')->nullable();
            $table->json('generated_conversions')->nullable();
            $table->json('responsive_images')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->string('collection_name')->nullable(); // For grouping media
            $table->unsignedInteger('order_column')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'post_id']);
            $table->index(['tenant_id', 'collection_name']);
            $table->index(['tenant_id', 'mime_type']);
            $table->index(['tenant_id', 'uploaded_by']);
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};