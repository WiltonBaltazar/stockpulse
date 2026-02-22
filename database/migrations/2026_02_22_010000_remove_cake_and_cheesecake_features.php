<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('custom_cake_orders')) {
            Schema::drop('custom_cake_orders');
        }

        if (! Schema::hasTable('permissions')) {
            return;
        }

        $tableNames = config('permission.table_names');
        $permissionsTable = $tableNames['permissions'] ?? 'permissions';
        $roleHasPermissionsTable = $tableNames['role_has_permissions'] ?? 'role_has_permissions';
        $modelHasPermissionsTable = $tableNames['model_has_permissions'] ?? 'model_has_permissions';

        $permissionId = DB::table($permissionsTable)
            ->where('name', 'manage cake orders')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        if (Schema::hasTable($roleHasPermissionsTable)) {
            DB::table($roleHasPermissionsTable)
                ->where('permission_id', $permissionId)
                ->delete();
        }

        if (Schema::hasTable($modelHasPermissionsTable)) {
            DB::table($modelHasPermissionsTable)
                ->where('permission_id', $permissionId)
                ->delete();
        }

        DB::table($permissionsTable)
            ->where('id', $permissionId)
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('custom_cake_orders')) {
            return;
        }

        Schema::create('custom_cake_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->string('order_type', 30)->default('custom_cake');
            $table->string('client_name');
            $table->string('client_contact')->nullable();
            $table->string('client_email')->nullable();
            $table->string('occasion')->nullable();
            $table->unsignedInteger('servings')->nullable();
            $table->string('cake_size')->nullable();
            $table->string('flavor')->nullable();
            $table->string('filling')->nullable();
            $table->string('frosting')->nullable();
            $table->text('design_notes')->nullable();
            $table->json('reference_images')->nullable();
            $table->date('event_date')->nullable();
            $table->dateTime('due_at');
            $table->string('pickup_method', 20)->default('pickup');
            $table->text('delivery_address')->nullable();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('decoration_price', 10, 2)->default(0);
            $table->decimal('extras_price', 10, 2)->default(0);
            $table->decimal('delivery_price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->default(0);
            $table->decimal('quoted_price', 10, 2)->default(0);
            $table->text('quote_notes')->nullable();
            $table->string('status', 30)->default('inquiry');
            $table->dateTime('quoted_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->dateTime('deposit_due_at')->nullable();
            $table->dateTime('deposit_paid_at')->nullable();
            $table->text('deposit_notes')->nullable();
            $table->dateTime('reminder_sent_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_at']);
            $table->index(['status', 'due_at']);
            $table->index(['order_type', 'status', 'due_at']);
        });
    }
};
