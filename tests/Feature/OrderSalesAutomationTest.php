<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\Sale;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSalesAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_order_generates_sale_and_financial_transaction_without_duplicates(): void
    {
        $user = User::factory()->create();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Pedido Pago',
            'is_active' => true,
        ]);

        $service = app(OrderService::class);
        $prepared = $service->prepareData($user, [
            'client_id' => $client->id,
            'payment_status' => Order::PAYMENT_PAID,
            'status' => Order::STATUS_PENDING,
            'order_date' => now(),
            'items' => [
                ['item_name' => 'Pastel de carne', 'quantity' => 3, 'unit_price' => 120],
            ],
        ]);

        $order = Order::query()->create($prepared['attributes']);
        $service->syncItems($order, $prepared['items']);
        $service->syncSalesAndFinancials($order);
        $service->syncSalesAndFinancials($order->fresh());

        $this->assertDatabaseCount('sales', 1);

        $sale = Sale::query()->where('order_id', $order->id)->first();
        $this->assertNotNull($sale);
        $this->assertSame(Sale::STATUS_COMPLETED, $sale->status);
        $this->assertSame(Sale::CHANNEL_ONLINE, $sale->channel);
        $this->assertSame(3.0, (float) $sale->quantity);
        $this->assertSame(360.0, (float) $sale->total_amount);
        $this->assertSame($client->id, $sale->client_id);
        $this->assertNotNull($sale->order_item_id);

        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $user->id,
            'reference' => $sale->reference.'-REV',
            'source' => FinancialTransaction::SOURCE_SALES,
            'type' => FinancialTransaction::TYPE_INCOME,
            'status' => FinancialTransaction::STATUS_COMPLETED,
            'amount' => 360.00,
        ]);

        $this->assertSame(
            1,
            FinancialTransaction::query()
                ->where('user_id', $user->id)
                ->where('reference', $sale->reference.'-REV')
                ->count()
        );
    }

    public function test_unpaid_order_removes_generated_sales_and_financial_effects(): void
    {
        $user = User::factory()->create();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Reaberto',
            'is_active' => true,
        ]);

        $service = app(OrderService::class);
        $prepared = $service->prepareData($user, [
            'client_id' => $client->id,
            'payment_status' => Order::PAYMENT_PAID,
            'status' => Order::STATUS_PENDING,
            'order_date' => now(),
            'items' => [
                ['item_name' => 'Bolo de coco', 'quantity' => 2, 'unit_price' => 250],
            ],
        ]);

        $order = Order::query()->create($prepared['attributes']);
        $service->syncItems($order, $prepared['items']);
        $service->syncSalesAndFinancials($order);

        $sale = Sale::query()->where('order_id', $order->id)->first();
        $this->assertNotNull($sale);

        $order->forceFill([
            'payment_status' => Order::PAYMENT_OPEN,
        ])->save();

        $service->syncSalesAndFinancials($order->fresh());

        $this->assertDatabaseMissing('sales', [
            'id' => $sale->id,
        ]);
        $this->assertDatabaseMissing('financial_transactions', [
            'user_id' => $user->id,
            'reference' => $sale->reference.'-REV',
        ]);
        $this->assertDatabaseMissing('financial_transactions', [
            'user_id' => $user->id,
            'reference' => $sale->reference.'-COGS',
        ]);
    }
}

