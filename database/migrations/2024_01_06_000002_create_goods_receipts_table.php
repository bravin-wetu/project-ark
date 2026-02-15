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
        // Goods Receipts table
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            
            // Receiving details
            $table->string('delivery_note_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('received_by')->nullable();
            $table->dateTime('received_at');
            
            // Location
            $table->foreignId('hub_id')->nullable()->constrained()->nullOnDelete();
            $table->string('receiving_location')->nullable();
            
            // Condition
            $table->enum('overall_condition', [
                'excellent',
                'good',
                'acceptable',
                'damaged',
                'rejected'
            ])->default('good');
            
            // Status
            $table->enum('status', [
                'draft',
                'confirmed',
                'partial',
                'complete',
                'cancelled'
            ])->default('draft');
            
            // Notes
            $table->text('notes')->nullable();
            $table->text('discrepancy_notes')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('purchase_order_id');
            $table->index('status');
            $table->index('received_at');
        });

        // Goods Receipt Items table
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_order_item_id')->constrained()->onDelete('cascade');
            
            // Quantities
            $table->decimal('expected_quantity', 12, 2);
            $table->decimal('received_quantity', 12, 2);
            $table->decimal('accepted_quantity', 12, 2)->default(0);
            $table->decimal('rejected_quantity', 12, 2)->default(0);
            
            // Condition
            $table->enum('condition', [
                'excellent',
                'good',
                'acceptable',
                'damaged',
                'rejected'
            ])->default('good');
            
            // Inspection
            $table->boolean('inspected')->default(false);
            $table->string('inspected_by')->nullable();
            $table->dateTime('inspected_at')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Storage
            $table->string('storage_location')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            
            $table->timestamps();

            $table->index('goods_receipt_id');
            $table->index('purchase_order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
    }
};
