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
        // Laravel's default notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // User notification preferences
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Email notification preferences
            $table->boolean('email_requisition_submitted')->default(true);
            $table->boolean('email_requisition_approved')->default(true);
            $table->boolean('email_requisition_rejected')->default(true);
            $table->boolean('email_po_created')->default(true);
            $table->boolean('email_po_approved')->default(true);
            $table->boolean('email_po_sent')->default(true);
            $table->boolean('email_goods_received')->default(true);
            $table->boolean('email_budget_threshold')->default(true);
            $table->boolean('email_stock_low')->default(true);
            $table->boolean('email_asset_maintenance')->default(true);
            
            // In-app notification preferences
            $table->boolean('app_requisition_submitted')->default(true);
            $table->boolean('app_requisition_approved')->default(true);
            $table->boolean('app_requisition_rejected')->default(true);
            $table->boolean('app_po_created')->default(true);
            $table->boolean('app_po_approved')->default(true);
            $table->boolean('app_po_sent')->default(true);
            $table->boolean('app_goods_received')->default(true);
            $table->boolean('app_budget_threshold')->default(true);
            $table->boolean('app_stock_low')->default(true);
            $table->boolean('app_asset_maintenance')->default(true);
            
            // Digest preferences
            $table->enum('digest_frequency', ['none', 'daily', 'weekly'])->default('none');
            $table->time('digest_time')->default('08:00:00');
            
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
        Schema::dropIfExists('notifications');
    }
};
