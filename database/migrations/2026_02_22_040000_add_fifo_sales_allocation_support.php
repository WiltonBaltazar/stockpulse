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
        Schema::table('production_batches', function (Blueprint $table) {
            $table->unsignedInteger('sold_units')->default(0)->after('produced_units');
            $table->index(['recipe_id', 'produced_at'], 'production_batches_recipe_produced_idx');
        });

        Schema::create('sale_batch_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity_units');
            $table->decimal('unit_cogs', 12, 4);
            $table->decimal('total_cogs', 12, 2);
            $table->timestamps();

            $table->unique(['sale_id', 'production_batch_id']);
            $table->index(['production_batch_id', 'sale_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_batch_allocations');

        Schema::table('production_batches', function (Blueprint $table) {
            $table->dropIndex('production_batches_recipe_produced_idx');
            $table->dropColumn('sold_units');
        });
    }
};
