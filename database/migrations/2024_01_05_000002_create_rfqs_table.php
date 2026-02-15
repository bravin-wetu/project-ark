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
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('rfq_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Link to requisition
            $table->foreignId('requisition_id')->constrained()->onDelete('cascade');
            
            // Polymorphic relationship to Project or DepartmentBudget
            $table->morphs('rfqable');
            
            // Status
            $table->enum('status', [
                'draft',
                'sent',
                'quotes_received',
                'under_evaluation',
                'awarded',
                'cancelled'
            ])->default('draft');
            
            // Dates
            $table->date('issue_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->date('delivery_date')->nullable();
            
            // Terms
            $table->text('terms_and_conditions')->nullable();
            $table->text('submission_instructions')->nullable();
            $table->text('evaluation_criteria')->nullable();
            
            // Minimum suppliers required
            $table->integer('min_quotes')->default(3);
            
            // Awarded supplier
            $table->foreignId('awarded_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('awarded_quote_id')->nullable();
            $table->timestamp('awarded_at')->nullable();
            $table->text('award_justification')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot table: RFQ <-> Supplier (invited suppliers)
        Schema::create('rfq_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->enum('status', ['invited', 'declined', 'quoted'])->default('invited');
            $table->text('decline_reason')->nullable();
            $table->timestamps();

            $table->unique(['rfq_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_supplier');
        Schema::dropIfExists('rfqs');
    }
};
