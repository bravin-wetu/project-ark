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
        Schema::create('department_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->string('fiscal_year'); // e.g., "FY2025", "FY2026"
            $table->string('name')->nullable(); // Optional custom name
            $table->text('description')->nullable();
            $table->decimal('total_budget', 15, 2)->default(0);
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->enum('currency', ['USD', 'KES', 'EUR', 'GBP'])->default('USD');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Only one active budget per department per fiscal year
            $table->unique(['department_id', 'fiscal_year']);
            $table->index(['status', 'fiscal_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_budgets');
    }
};
