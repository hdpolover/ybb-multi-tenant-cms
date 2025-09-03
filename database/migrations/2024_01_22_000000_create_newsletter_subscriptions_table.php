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
        Schema::create('newsletter_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            
            // Subscriber details
            $table->string('email');
            $table->string('name')->nullable();
            $table->json('preferences')->nullable(); // What content types they want
            $table->enum('status', ['active', 'unsubscribed', 'bounced', 'pending'])->default('pending');
            $table->string('frequency')->default('weekly'); // daily, weekly, monthly
            
            // Verification
            $table->string('verification_token')->nullable();
            $table->datetime('verified_at')->nullable();
            $table->string('unsubscribe_token');
            
            // Tracking
            $table->json('tags')->nullable(); // Segmentation tags
            $table->string('source')->nullable(); // How they subscribed
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->datetime('last_sent_at')->nullable();
            $table->unsignedInteger('emails_sent')->default(0);
            $table->unsignedInteger('emails_opened')->default(0);
            $table->unsignedInteger('links_clicked')->default(0);
            
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'frequency']);
            $table->index(['verification_token']);
            $table->index(['unsubscribe_token']);
            $table->unique(['tenant_id', 'email']);
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscriptions');
    }
};