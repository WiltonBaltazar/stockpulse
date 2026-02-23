<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_sale_receipt_pdf_for_completed_sale(): void
    {
        $user = User::factory()->create();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Recibo',
            'is_active' => true,
        ]);

        $sale = Sale::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'item_name' => 'Bolo',
            'customer_name' => $client->name,
            'reference' => 'BOLABC123456',
            'status' => Sale::STATUS_COMPLETED,
            'channel' => Sale::CHANNEL_OFFLINE,
            'payment_method' => Sale::PAYMENT_CASH,
            'sold_at' => now(),
            'quantity' => 2,
            'unit_price' => 350,
            'total_amount' => 700,
        ]);

        $response = $this->actingAs($user)->get(route('documents.sales.receipt', ['sale' => $sale]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_it_blocks_sale_receipt_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $sale = Sale::query()->create([
            'user_id' => $owner->id,
            'item_name' => 'Coxinha',
            'customer_name' => 'Cliente',
            'reference' => 'COXABC123456',
            'status' => Sale::STATUS_COMPLETED,
            'channel' => Sale::CHANNEL_OFFLINE,
            'payment_method' => Sale::PAYMENT_CASH,
            'sold_at' => now(),
            'quantity' => 1,
            'unit_price' => 20,
            'total_amount' => 20,
        ]);

        $this->actingAs($other)
            ->get(route('documents.sales.receipt', ['sale' => $sale]))
            ->assertForbidden();
    }

    public function test_it_generates_quote_pdf(): void
    {
        $user = User::factory()->create();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Cotação',
            'is_active' => true,
        ]);

        $quote = Quote::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'reference' => 'COTABC123456',
            'status' => Quote::STATUS_SENT,
            'type' => Quote::TYPE_DELIVERY,
            'quote_date' => now()->toDateString(),
            'additional_fee' => 50,
            'discount' => 10,
            'total_amount' => 240,
        ]);

        QuoteItem::query()->create([
            'quote_id' => $quote->id,
            'item_name' => 'Mini salgado',
            'quantity' => 10,
            'unit_price' => 20,
            'total_price' => 200,
        ]);

        $response = $this->actingAs($user)->get(route('documents.quotes.pdf', ['quote' => $quote]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_it_generates_order_slip_pdf(): void
    {
        $user = User::factory()->create();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Pedido',
            'is_active' => true,
        ]);

        $order = Order::query()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'reference' => 'PEDABC123456',
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_OPEN,
            'order_date' => now(),
            'delivery_date' => now()->addDay(),
            'total_amount' => 300,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'item_name' => 'Empada',
            'quantity' => 15,
            'unit_price' => 20,
            'total_price' => 300,
        ]);

        $response = $this->actingAs($user)->get(route('documents.orders.slip', ['order' => $order]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }
}
