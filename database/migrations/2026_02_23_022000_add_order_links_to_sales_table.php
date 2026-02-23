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
        Schema::table('sales', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales', 'order_id')) {
                $table->foreignId('order_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('orders')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('sales', 'order_item_id')) {
                $table->foreignId('order_item_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained('order_items')
                    ->nullOnDelete();
            }
        });

        Schema::table('sales', function (Blueprint $table): void {
            if (Schema::hasColumn('sales', 'order_id')) {
                $table->index('order_id');
            }

            if (Schema::hasColumn('sales', 'order_item_id')) {
                $table->unique('order_item_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            if (Schema::hasColumn('sales', 'order_item_id')) {
                $table->dropUnique(['order_item_id']);
            }

            if (Schema::hasColumn('sales', 'order_id')) {
                $table->dropIndex(['order_id']);
            }
        });

        Schema::table('sales', function (Blueprint $table): void {
            if (Schema::hasColumn('sales', 'order_item_id')) {
                $table->dropConstrainedForeignId('order_item_id');
            }

            if (Schema::hasColumn('sales', 'order_id')) {
                $table->dropConstrainedForeignId('order_id');
            }
        });
    }
};

