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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('reference')->nullable();
            $table->string('status', 20)->default('completed');
            $table->string('channel', 20)->default('offline');
            $table->string('payment_method', 30)->default('cash');
            $table->timestamp('sold_at');
            $table->decimal('quantity', 10, 3)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('estimated_unit_cost', 12, 4)->nullable();
            $table->decimal('estimated_total_cost', 12, 2)->nullable();
            $table->decimal('estimated_profit', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'sold_at']);
            $table->index(['status', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
