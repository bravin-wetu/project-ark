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
        // Budget revisions - track all changes to budget lines
        Schema::create('budget_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            
            // Change tracking
            $table->enum('revision_type', ['allocation_change', 'reallocation', 'adjustment', 'correction']);
            $table->decimal('previous_allocated', 15, 2);
            $table->decimal('new_allocated', 15, 2);
            $table->decimal('change_amount', 15, 2); // Can be negative
            
            // Approval tracking
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('reference_number')->nullable(); // e.g., REV-2024-001
            
            $table->timestamps();
            
            $table->index(['budget_line_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        // Budget locks - lock budgets from changes
        Schema::create('budget_locks', function (Blueprint $table) {
            $table->id();
            $table->morphs('lockable'); // Project or DepartmentBudget
            $table->foreignId('locked_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('unlocked_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->enum('lock_type', ['soft', 'hard'])->default('soft');
            // soft = warnings but can override with approval
            // hard = no changes allowed at all
            
            $table->text('reason')->nullable();
            $table->timestamp('locked_at');
            $table->timestamp('unlocked_at')->nullable();
            $table->date('lock_until')->nullable(); // Optional expiry
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['lockable_type', 'lockable_id', 'is_active']);
        });

        // Budget thresholds - configurable alert thresholds
        Schema::create('budget_thresholds', function (Blueprint $table) {
            $table->id();
            $table->morphs('thresholdable'); // Project or DepartmentBudget
            
            $table->decimal('warning_percentage', 5, 2)->default(80.00);
            $table->decimal('critical_percentage', 5, 2)->default(95.00);
            $table->decimal('block_percentage', 5, 2)->default(100.00);
            
            $table->boolean('send_warning_alert')->default(true);
            $table->boolean('send_critical_alert')->default(true);
            $table->boolean('block_on_exceed')->default(false);
            
            // Track when alerts were last sent
            $table->timestamp('warning_sent_at')->nullable();
            $table->timestamp('critical_sent_at')->nullable();
            
            $table->timestamps();
            
            $table->unique(['thresholdable_type', 'thresholdable_id']);
        });

        // Add lock status to department_budgets
        Schema::table('department_budgets', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('status');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
        });

        // Add approval requirement to budget_lines for changes
        Schema::table('budget_lines', function (Blueprint $table) {
            $table->boolean('requires_approval_for_changes')->default(false)->after('is_active');
            $table->decimal('original_allocated', 15, 2)->default(0)->after('requires_approval_for_changes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_lines', function (Blueprint $table) {
            $table->dropColumn(['requires_approval_for_changes', 'original_allocated']);
        });

        Schema::table('department_budgets', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'locked_at']);
        });

        Schema::dropIfExists('budget_thresholds');
        Schema::dropIfExists('budget_locks');
        Schema::dropIfExists('budget_revisions');
    }
};
