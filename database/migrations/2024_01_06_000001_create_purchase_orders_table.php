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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            
            // Source references
            $table->foreignId('rfq_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requisition_id')->nullable()->constrained()->nullOnDelete();
            
            // Polymorphic relationship to Project or DepartmentBudget
            $table->morphs('purchaseable');
            
            // Supplier
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            
            // Budget line for commitment tracking
            $table->foreignId('budget_line_id')->constrained()->onDelete('restrict');
            
            // Delivery
            $table->foreignId('delivery_hub_id')->nullable()->constrained('hubs')->nullOnDelete();
            $table->text('delivery_address')->nullable();
            $table->date('expected_delivery_date')->nullable();
            
            // Amounts
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            
            // Status
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'rejected',
                'sent',
                'acknowledged',
                'partially_received',
                'received',
                'cancelled',
                'closed'
            ])->default('draft');
            
            // Terms
            $table->text('payment_terms')->nullable();
            $table->text('delivery_terms')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->text('notes')->nullable();
            
            // Approval workflow
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Sent tracking
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // PO line items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('quote_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requisition_item_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('specifications')->nullable();
            
            $table->decimal('quantity', 10, 2);
            $table->decimal('received_quantity', 10, 2)->default(0);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            
            $table->enum('status', ['pending', 'partially_received', 'received', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
