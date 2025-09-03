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
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            
            // Campaign details
            $table->string('name');
            $table->string('subject');
            $table->text('preview_text')->nullable();
            $table->longText('content'); // HTML content
            $table->enum('type', ['newsletter', 'digest', 'announcement', 'promotional'])->default('newsletter');
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'paused', 'cancelled'])->default('draft');
            
            // Targeting
            $table->json('recipient_criteria')->nullable(); // Filters for who gets this
            $table->unsignedInteger('estimated_recipients')->default(0);
            $table->unsignedInteger('actual_recipients')->default(0);
            
            // Scheduling
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('sent_at')->nullable();
            
            // Analytics
            $table->unsignedInteger('emails_sent')->default(0);
            $table->unsignedInteger('emails_delivered')->default(0);
            $table->unsignedInteger('emails_opened')->default(0);
            $table->unsignedInteger('emails_clicked')->default(0);
            $table->unsignedInteger('emails_bounced')->default(0);
            $table->unsignedInteger('emails_unsubscribed')->default(0);
            $table->decimal('open_rate', 5, 2)->default(0.00);
            $table->decimal('click_rate', 5, 2)->default(0.00);
            $table->decimal('bounce_rate', 5, 2)->default(0.00);
            
            // Template and settings
            $table->string('template')->nullable(); // Email template used
            $table->json('settings')->nullable(); // Campaign-specific settings
            
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'scheduled_at']);
            $table->index(['tenant_id', 'sent_at']);
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};