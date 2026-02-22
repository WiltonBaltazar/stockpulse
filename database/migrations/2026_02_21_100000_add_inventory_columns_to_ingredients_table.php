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
        Schema::table('ingredients', function (Blueprint $table) {
            $table->decimal('stock_quantity_g', 12, 3)->default(0)->after('package_cost');
            $table->decimal('reorder_level_g', 12, 3)->default(0)->after('stock_quantity_g');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity_g', 'reorder_level_g']);
        });
    }
};
