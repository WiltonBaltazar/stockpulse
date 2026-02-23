<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FinancialTransaction;
use App\Models\ProductionBatch;
use App\Models\Recipe;
use App\Models\Sale;
use App\Models\SaleBatchAllocation;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_registered_client_data_when_creating_sale(): void
    {
        $user = User::factory()->create();

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Cantina Horizonte',
            'contact_number' => '+258842202020',
            'is_active' => true,
        ]);

        $service = app(SaleService::class);
        $prepared = $service->prepareData($user, [
            'item_name' => 'Empada de frango',
            'status' => Sale::STATUS_COMPLETED,
            'channel' => Sale::CHANNEL_OFFLINE,
            'payment_method' => Sale::PAYMENT_CASH,
            'quantity' => 6,
            'unit_price' => 20,
            'client_id' => $client->id,
            'customer_name' => 'Texto manual que deve ser ignorado',
            'sold_at' => now(),
        ]);

        $this->assertSame($client->id, $prepared['client_id']);
        $this->assertSame('Cantina Horizonte', $prepared['customer_name']);
    }

    public function test_it_rejects_client_from_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $foreignClient = Client::query()->create([
            'user_id' => $otherUser->id,
            'name' => 'Cliente Externo',
            'is_active' => true,
        ]);

        $service = app(SaleService::class);

        $this->expectException(ValidationException::class);

        $service->prepareData($user, [
            'item_name' => 'Coxinha',
            'status' => Sale::STATUS_COMPLETED,
            'channel' => Sale::CHANNEL_OFFLINE,
            'payment_method' => Sale::PAYMENT_CASH,
            'quantity' => 2,
            'unit_price' => 12,
            'client_id' => $foreignClient->id,
            'sold_at' => now(),
        ]);
    }

    public function test_it_allocates_recipe_sales_fifo_and_syncs_financials(): void
    {
        $user = User::factory()->create();

        $recipe = Recipe::query()->create([
            'user_id' => $user->id,
            'name' => 'Coxinha de Frango',
            'priced_at' => now()->toDateString(),
            'yield_units' => 10,
            'packaging_cost_per_unit' => 0,
            'overhead_percent' => 0,
            'markup_multiplier' => 2,
        ]);

        $firstBatch = ProductionBatch::query()->create([
            'recipe_id' => $recipe->id,
            'user_id' => $user->id,
            'produced_at' => now()->subDays(3)->toDateString(),
            'produced_units' => 5,
            'sold_units' => 0,
            'ingredients_cost' => 50,
            'packaging_cost' => 0,
            'overhead_cost' => 0,
            'total_cogs' => 50,
            'cogs_per_unit' => 10,
            'suggested_unit_price' => 20,
        ]);

        $secondBatch = ProductionBatch::query()->create([
            'recipe_id' => $recipe->id,
            'user_id' => $user->id,
            'produced_at' => now()->subDays(1)->toDateString(),
            'produced_units' => 5,
            'sold_units' => 0,
            'ingredients_cost' => 60,
            'packaging_cost' => 0,
            'overhead_cost' => 0,
            'total_cogs' => 60,
            'cogs_per_unit' => 12,
            'suggested_unit_price' => 20,
        ]);

        $service = app(SaleService::class);

        $prepared = $service->prepareData($user, [
            'recipe_id' => $recipe->id,
            'status' => Sale::STATUS_COMPLETED,
            'channel' => Sale::CHANNEL_OFFLINE,
            'payment_method' => Sale::PAYMENT_CASH,
            'quantity' => 7,
            'customer_name' => 'Balcão',
            'sold_at' => now(),
        ]);

        $sale = Sale::query()->create($prepared);
        $service->syncOperationalAndFinancials($sale);

        $sale->refresh();
        $firstBatch->refresh();
        $secondBatch->refresh();

        $this->assertSame(5, $firstBatch->sold_units);
        $this->assertSame(2, $secondBatch->sold_units);
        $this->assertEqualsWithDelta(74.0, (float) $sale->estimated_total_cost, 0.0001);
        $this->assertEqualsWithDelta(66.0, (float) $sale->estimated_profit, 0.0001);

        $this->assertDatabaseHas('sale_batch_allocations', [
            'sale_id' => $sale->id,
            'production_batch_id' => $firstBatch->id,
            'quantity_units' => 5,
            'total_cogs' => 50.00,
        ]);
        $this->assertDatabaseHas('sale_batch_allocations', [
            'sale_id' => $sale->id,
            'production_batch_id' => $secondBatch->id,
            'quantity_units' => 2,
            'total_cogs' => 24.00,
        ]);
        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $user->id,
            'reference' => $sale->reference.'-REV',
            'source' => FinancialTransaction::SOURCE_SALES,
            'type' => FinancialTransaction::TYPE_INCOME,
            'amount' => 140.00,
        ]);
        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $user->id,
            'reference' => $sale->reference.'-COGS',
            'source' => FinancialTransaction::SOURCE_COGS,
            'type' => FinancialTransaction::TYPE_EXPENSE,
            'amount' => 74.00,
        ]);

        $updated = $service->prepareData($user, [
            'recipe_id' => $recipe->id,
            'status' => Sale::STATUS_COMPLETED,
            'channel' => Sale::CHANNEL_OFFLINE,
            'payment_method' => Sale::PAYMENT_CASH,
            'quantity' => 3,
            'customer_name' => 'Balcão',
            'sold_at' => now(),
            'reference' => $sale->reference,
        ]);

        $sale->forceFill($updated)->save();
        $service->syncOperationalAndFinancials($sale);

        $sale->refresh();
        $firstBatch->refresh();
        $secondBatch->refresh();

        $this->assertSame(3, $firstBatch->sold_units);
        $this->assertSame(0, $secondBatch->sold_units);
        $this->assertDatabaseCount('sale_batch_allocations', 1);
        $this->assertDatabaseHas('sale_batch_allocations', [
            'sale_id' => $sale->id,
            'production_batch_id' => $firstBatch->id,
            'quantity_units' => 3,
            'total_cogs' => 30.00,
        ]);
        $this->assertEqualsWithDelta(30.0, (float) $sale->estimated_total_cost, 0.0001);
        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $user->id,
            'reference' => $sale->reference.'-COGS',
            'amount' => 30.00,
        ]);
    }

    public function test_it_rejects_sale_when_completed_recipe_stock_is_insufficient(): void
    {
        $user = User::factory()->create();

        $recipe = Recipe::query()->create([
            'user_id' => $user->id,
            'name' => 'Pastel de Queijo',
            'priced_at' => now()->toDateString(),
            'yield_units' => 10,
            'packaging_cost_per_unit' => 0,
            'overhead_percent' => 0,
            'markup_multiplier' => 2,
        ]);

        $batch = ProductionBatch::query()->create([
            'recipe_id' => $recipe->id,
            'user_id' => $user->id,
            'produced_at' => now()->subDay()->toDateString(),
            'produced_units' => 4,
            'sold_units' => 0,
            'ingredients_cost' => 24,
            'packaging_cost' => 0,
            'overhead_cost' => 0,
            'total_cogs' => 24,
            'cogs_per_unit' => 6,
            'suggested_unit_price' => 12,
        ]);

        $service = app(SaleService::class);
        $prepared = $service->prepareData($user, [
            'recipe_id' => $recipe->id,
            'status' => Sale::STATUS_COMPLETED,
            'channel' => Sale::CHANNEL_OFFLINE,
            'payment_method' => Sale::PAYMENT_CASH,
            'quantity' => 9,
            'customer_name' => 'Balcão',
            'sold_at' => now(),
        ]);

        $sale = Sale::query()->create($prepared);

        try {
            $service->syncOperationalAndFinancials($sale);
            $this->fail('Expected validation exception for insufficient stock.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantity', $exception->errors());
        }

        $batch->refresh();
        $this->assertSame(0, $batch->sold_units);
        $this->assertDatabaseCount('sale_batch_allocations', 0);
        $this->assertDatabaseMissing('financial_transactions', [
            'user_id' => $user->id,
            'reference' => $sale->reference.'-REV',
        ]);
        $this->assertDatabaseMissing('financial_transactions', [
            'user_id' => $user->id,
            'reference' => $sale->reference.'-COGS',
        ]);
        $this->assertSame(0, SaleBatchAllocation::query()->count());
    }
}
