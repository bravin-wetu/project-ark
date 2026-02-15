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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag')->unique(); // AST-2026-0001
            $table->string('name');
            $table->text('description')->nullable();
            
            // Category & Classification
            $table->string('category'); // Equipment, Vehicle, Furniture, IT, etc.
            $table->string('subcategory')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            
            // Source tracking (where did this asset come from)
            $table->foreignId('goods_receipt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('goods_receipt_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            
            // Budget context (polymorphic - Project or DepartmentBudget)
            $table->morphs('assetable');
            $table->foreignId('budget_line_id')->nullable()->constrained()->nullOnDelete();
            
            // Financial
            $table->decimal('acquisition_cost', 15, 2)->default(0);
            $table->date('acquisition_date')->nullable();
            $table->decimal('current_value', 15, 2)->nullable();
            $table->decimal('salvage_value', 15, 2)->nullable();
            $table->integer('useful_life_months')->nullable(); // For depreciation
            $table->string('currency', 3)->default('USD');
            
            // Location & Custody
            $table->foreignId('hub_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('location')->nullable(); // Physical location within hub
            
            // Status
            $table->enum('status', [
                'pending',          // Just created, pending verification
                'active',           // In use
                'in_maintenance',   // Under repair/service
                'in_transit',       // Being transferred
                'disposed',         // No longer in use
                'lost',             // Cannot be located
                'stolen'            // Reported stolen
            ])->default('pending');
            
            // Condition
            $table->enum('condition', [
                'new',
                'excellent',
                'good',
                'fair',
                'poor',
                'damaged',
                'non_functional'
            ])->default('new');
            
            // Warranty
            $table->date('warranty_expiry')->nullable();
            $table->text('warranty_notes')->nullable();
            
            // Insurance
            $table->string('insurance_policy')->nullable();
            $table->date('insurance_expiry')->nullable();
            
            // Disposal tracking
            $table->date('disposal_date')->nullable();
            $table->enum('disposal_method', ['sold', 'donated', 'scrapped', 'returned', 'other'])->nullable();
            $table->decimal('disposal_value', 15, 2)->nullable();
            $table->text('disposal_notes')->nullable();
            $table->foreignId('disposed_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Notes & attachments
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable(); // Photos, documents
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'hub_id']);
            $table->index(['category', 'status']);
        });

        // Asset transfers
        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique(); // TRF-2026-0001
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            
            // From
            $table->foreignId('from_hub_id')->nullable()->constrained('hubs')->nullOnDelete();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_location')->nullable();
            
            // To
            $table->foreignId('to_hub_id')->nullable()->constrained('hubs')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('to_location')->nullable();
            
            // Status
            $table->enum('status', [
                'pending',      // Transfer initiated
                'in_transit',   // Asset on the way
                'received',     // Received at destination
                'cancelled'     // Transfer cancelled
            ])->default('pending');
            
            // Dates
            $table->date('transfer_date');
            $table->date('expected_arrival')->nullable();
            $table->date('received_date')->nullable();
            
            // Notes
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->enum('condition_on_transfer', ['excellent', 'good', 'fair', 'poor', 'damaged'])->nullable();
            $table->enum('condition_on_receipt', ['excellent', 'good', 'fair', 'poor', 'damaged'])->nullable();
            
            // Audit
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Asset maintenance records
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('maintenance_number')->unique(); // MNT-2026-0001
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            
            // Type
            $table->enum('type', [
                'preventive',   // Scheduled maintenance
                'corrective',   // Repair
                'inspection',   // Routine check
                'upgrade'       // Enhancement
            ])->default('corrective');
            
            // Status
            $table->enum('status', [
                'scheduled',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('scheduled');
            
            // Details
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('scheduled_date');
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            
            // Provider
            $table->string('service_provider')->nullable();
            $table->string('technician')->nullable();
            
            // Cost
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            
            // Condition
            $table->enum('condition_before', ['excellent', 'good', 'fair', 'poor', 'damaged', 'non_functional'])->nullable();
            $table->enum('condition_after', ['excellent', 'good', 'fair', 'poor', 'damaged', 'non_functional'])->nullable();
            
            // Notes
            $table->text('findings')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('recommendations')->nullable();
            $table->date('next_maintenance_due')->nullable();
            
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('asset_transfers');
        Schema::dropIfExists('assets');
    }
};
