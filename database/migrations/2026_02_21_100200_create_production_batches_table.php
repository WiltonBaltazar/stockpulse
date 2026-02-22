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
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('produced_at');
            $table->decimal('produced_units', 10, 3);
            $table->decimal('ingredients_cost', 12, 2);
            $table->decimal('packaging_cost', 12, 2);
            $table->decimal('overhead_cost', 12, 2);
            $table->decimal('total_cogs', 12, 2);
            $table->decimal('cogs_per_unit', 12, 4);
            $table->decimal('suggested_unit_price', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'produced_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_batches');
    }
};
