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
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('requisition_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('justification')->nullable();
            
            // Polymorphic relationship to Project or DepartmentBudget
            $table->morphs('requisitionable');
            
            // Budget line this requisition is charged to
            $table->foreignId('budget_line_id')->constrained()->onDelete('restrict');
            
            // Requester
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('requested_at')->useCurrent();
            
            // Delivery details
            $table->foreignId('delivery_hub_id')->nullable()->constrained('hubs')->onDelete('set null');
            $table->date('required_date')->nullable();
            
            // Financial
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            
            // Status workflow
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'rejected',
                'cancelled',
                'in_progress',    // RFQ/PO created
                'completed'       // Goods received
            ])->default('draft');
            
            // Approval tracking
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Priority
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'requested_at']);
            $table->index('requisition_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
