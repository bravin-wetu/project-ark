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
        // Stock items - consumable inventory
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique(); // Stock Keeping Unit: STK-2026-0001
            $table->string('name');
            $table->text('description')->nullable();
            
            // Category & Classification
            $table->string('category'); // Office Supplies, Cleaning, WASH, Medical, etc.
            $table->string('subcategory')->nullable();
            $table->string('unit')->default('pcs'); // pcs, kg, liters, boxes, etc.
            
            // Reorder settings
            $table->decimal('reorder_level', 10, 2)->default(0); // Alert when below this
            $table->decimal('reorder_quantity', 10, 2)->default(0); // Suggested quantity to order
            
            // Default pricing
            $table->decimal('unit_cost', 15, 2)->nullable(); // Average cost
            $table->string('currency', 3)->default('USD');
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['category', 'is_active']);
        });

        // Stock batches - actual inventory received
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique(); // BAT-2026-0001
            $table->foreignId('stock_item_id')->constrained()->onDelete('cascade');
            
            // Source tracking
            $table->foreignId('goods_receipt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('goods_receipt_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            
            // Budget context (polymorphic)
            $table->morphs('batchable');
            $table->foreignId('budget_line_id')->nullable()->constrained()->nullOnDelete();
            
            // Location
            $table->foreignId('hub_id')->constrained()->onDelete('restrict');
            $table->string('storage_location')->nullable(); // Shelf, room, etc.
            
            // Quantities
            $table->decimal('quantity_received', 10, 2);
            $table->decimal('quantity_available', 10, 2);
            $table->decimal('quantity_reserved', 10, 2)->default(0);
            $table->decimal('quantity_issued', 10, 2)->default(0);
            
            // Cost
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total_cost', 15, 2);
            $table->string('currency', 3)->default('USD');
            
            // Dates
            $table->date('received_date');
            $table->date('expiry_date')->nullable();
            $table->string('lot_number')->nullable(); // Manufacturer lot number
            
            // Status
            $table->enum('status', [
                'active',       // Available for issue
                'reserved',     // Fully reserved
                'depleted',     // All issued
                'expired',      // Past expiry date
                'damaged'       // Cannot be used
            ])->default('active');
            
            // Notes
            $table->text('notes')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['stock_item_id', 'status']);
            $table->index(['hub_id', 'status']);
        });

        // Stock issues - tracking consumption
        Schema::create('stock_issues', function (Blueprint $table) {
            $table->id();
            $table->string('issue_number')->unique(); // ISS-2026-0001
            
            // Context (polymorphic - Project or DepartmentBudget)
            $table->morphs('issueable');
            
            // Location
            $table->foreignId('hub_id')->constrained()->onDelete('restrict');
            
            // Recipient
            $table->foreignId('issued_to')->constrained('users')->onDelete('restrict');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            
            // Status
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'issued',
                'cancelled'
            ])->default('draft');
            
            // Purpose
            $table->string('purpose')->nullable();
            $table->text('notes')->nullable();
            
            // Date
            $table->date('issue_date');
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'hub_id']);
        });

        // Stock issue items
        Schema::create('stock_issue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_issue_id')->constrained()->onDelete('cascade');
            $table->foreignId('stock_item_id')->constrained()->onDelete('restrict');
            $table->foreignId('stock_batch_id')->nullable()->constrained()->nullOnDelete();
            
            $table->decimal('quantity_requested', 10, 2);
            $table->decimal('quantity_issued', 10, 2)->default(0);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->decimal('total_cost', 15, 2)->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Stock adjustments - for corrections, write-offs, etc.
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique(); // ADJ-2026-0001
            $table->foreignId('stock_batch_id')->constrained()->onDelete('cascade');
            
            // Type
            $table->enum('type', [
                'correction',   // Inventory count correction
                'damage',       // Damaged items
                'expired',      // Expired items
                'loss',         // Unexplained loss
                'return',       // Returned to stock
                'transfer_in',  // Received from another hub
                'transfer_out'  // Sent to another hub
            ]);
            
            // Quantity change (positive = increase, negative = decrease)
            $table->decimal('quantity_change', 10, 2);
            $table->decimal('quantity_before', 10, 2);
            $table->decimal('quantity_after', 10, 2);
            
            // Reason
            $table->text('reason');
            $table->text('notes')->nullable();
            
            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_issue_items');
        Schema::dropIfExists('stock_issues');
        Schema::dropIfExists('stock_batches');
        Schema::dropIfExists('stock_items');
    }
};
