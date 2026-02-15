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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique();
            $table->string('supplier_reference')->nullable(); // Supplier's quote reference
            
            // Relationships
            $table->foreignId('rfq_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            
            // Status
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'selected',
                'not_selected',
                'withdrawn'
            ])->default('draft');
            
            // Amounts
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            
            // Validity
            $table->date('quote_date')->nullable();
            $table->date('valid_until')->nullable();
            
            // Delivery
            $table->integer('delivery_days')->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('delivery_terms')->nullable();
            
            // Payment
            $table->text('payment_terms')->nullable();
            
            // Notes
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            
            // Evaluation
            $table->decimal('evaluation_score', 5, 2)->nullable(); // 0.00 to 100.00
            $table->text('evaluation_notes')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('evaluated_at')->nullable();
            
            // Attachments stored path (quote document)
            $table->string('attachment_path')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Quote line items
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->onDelete('cascade');
            $table->foreignId('requisition_item_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('specifications')->nullable();
            
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            
            $table->text('notes')->nullable();
            $table->integer('delivery_days')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
    }
};
