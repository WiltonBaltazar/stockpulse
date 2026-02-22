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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('priced_at')->nullable();
            $table->decimal('yield_units', 10, 3)->default(1);
            $table->decimal('packaging_cost_per_unit', 10, 2)->default(0);
            $table->decimal('overhead_percent', 5, 2)->default(25);
            $table->decimal('markup_multiplier', 6, 3)->default(3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
