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
        Schema::create('requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained()->onDelete('cascade');
            
            // Item details
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('specifications')->nullable();
            
            // Quantity & pricing
            $table->decimal('quantity', 12, 2);
            $table->string('unit')->default('pcs'); // pcs, kg, liters, etc.
            $table->decimal('estimated_unit_price', 15, 2)->default(0);
            $table->decimal('estimated_total', 15, 2)->default(0);
            
            // Categorization
            $table->enum('item_type', ['goods', 'services', 'works'])->default('goods');
            
            // Notes
            $table->text('notes')->nullable();
            
            // Ordering
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Index
            $table->index('requisition_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisition_items');
    }
};
