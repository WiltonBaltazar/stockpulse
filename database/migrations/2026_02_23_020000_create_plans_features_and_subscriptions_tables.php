<?php

use App\Models\Plan;
use App\Models\Subscription;
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
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 8)->default('MT');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('features', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('feature_plan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['plan_id', 'feature_id']);
        });

        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default(Subscription::STATUS_ACTIVE);
            $table->timestamp('started_at');
            $table->timestamp('ends_at')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 8)->default('MT');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        $now = now();

        $features = [
            ['code' => 'ingredients', 'name' => 'Ingredientes', 'description' => 'Cadastro e gestão de ingredientes e custos.', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'recipes', 'name' => 'Receitas', 'description' => 'Precificação, fichas técnicas e rendimento.', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'inventory', 'name' => 'Movimentos de stock', 'description' => 'Entradas, saídas e histórico de inventário.', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'production_batches', 'name' => 'Lotes de produção', 'description' => 'Registo de produção e consumo de ingredientes.', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'clients', 'name' => 'Clientes', 'description' => 'Cadastro e controlo de clientes.', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'quotes', 'name' => 'Orçamentos', 'description' => 'Emissão e gestão de cotações.', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'orders', 'name' => 'Pedidos', 'description' => 'Gestão de pedidos e entregas.', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'sales', 'name' => 'Vendas', 'description' => 'Registo de vendas online/offline.', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'finances', 'name' => 'Finanças', 'description' => 'Transações financeiras e controlo financeiro.', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('features')->insert($features);

        $planId = DB::table('plans')->insertGetId([
            'code' => Plan::CODE_BASIC,
            'name' => 'Basic',
            'description' => 'Plano base gratuito.',
            'price' => 0,
            'currency' => 'MT',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $featureIds = DB::table('features')->pluck('id');

        foreach ($featureIds as $featureId) {
            DB::table('feature_plan')->insert([
                'plan_id' => $planId,
                'feature_id' => $featureId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $userIds = DB::table('users')->pluck('id');
        foreach ($userIds as $userId) {
            DB::table('subscriptions')->insert([
                'user_id' => $userId,
                'plan_id' => $planId,
                'status' => Subscription::STATUS_ACTIVE,
                'started_at' => $now,
                'ends_at' => null,
                'price' => 0,
                'currency' => 'MT',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('feature_plan');
        Schema::dropIfExists('features');
        Schema::dropIfExists('plans');
    }
};
