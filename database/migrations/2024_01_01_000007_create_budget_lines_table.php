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
        // Budget categories for organizing budget lines
        Schema::create('budget_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Budget lines - polymorphic to support both Projects and Department Budgets
        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->string('code'); // e.g., BL-001, BL-002
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('budget_category_id')->nullable()->constrained()->nullOnDelete();
            
            // Polymorphic relationship (Project or DepartmentBudget)
            $table->morphs('budgetable');
            
            // Financial tracking
            $table->decimal('allocated', 15, 2)->default(0);
            $table->decimal('committed', 15, 2)->default(0);
            $table->decimal('spent', 15, 2)->default(0);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['budgetable_type', 'budgetable_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
        Schema::dropIfExists('budget_categories');
    }
};
