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
        // Approval matrix configuration
        Schema::create('approval_matrices', function (Blueprint $table) {
            $table->id();
            $table->enum('context_type', ['project', 'department']); // Which budget context
            $table->string('document_type'); // requisition, rfq, po, etc.
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->nullable(); // null = no upper limit
            $table->foreignId('approver_role_id')->constrained('roles')->cascadeOnDelete();
            $table->integer('approval_level')->default(1); // For multi-level approvals
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['context_type', 'document_type', 'is_active']);
        });

        // Track individual approvals on documents
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable'); // The document being approved
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('approval_matrix_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'returned']);
            $table->integer('level')->default(1);
            $table->text('comments')->nullable();
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('approval_matrices');
    }
};
