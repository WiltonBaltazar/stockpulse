<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Quote;
use App\Models\User;
use App\Services\OrderService;
use App\Services\QuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuoteOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_service_calculates_total_with_fee_and_discount(): void
    {
        $user = User::factory()->create();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste',
            'is_active' => true,
        ]);

        $prepared = app(QuoteService::class)->prepareData($user, [
            'client_id' => $client->id,
            'status' => Quote::STATUS_DRAFT,
            'type' => Quote::TYPE_DELIVERY,
            'additional_fee' => 10,
            'discount' => 5,
            'items' => [
                ['item_name' => 'Bolo', 'quantity' => 2, 'unit_price' => 10],
                ['item_name' => 'Salgado', 'quantity' => 1, 'unit_price' => 20],
            ],
        ]);

        $this->assertSame($client->id, $prepared['attributes']['client_id']);
        $this->assertSame(45.0, (float) $prepared['attributes']['total_amount']);
        $this->assertCount(2, $prepared['items']);
    }

    public function test_order_service_creates_order_from_quote_and_marks_quote_as_converted(): void
    {
        $user = User::factory()->create();
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Conversão',
            'is_active' => true,
        ]);

        $quoteService = app(QuoteService::class);
        $preparedQuote = $quoteService->prepareData($user, [
            'client_id' => $client->id,
            'status' => Quote::STATUS_APPROVED,
            'type' => Quote::TYPE_PICKUP,
            'items' => [
                ['item_name' => 'Empada', 'quantity' => 10, 'unit_price' => 12],
            ],
        ]);

        $quote = Quote::query()->create($preparedQuote['attributes']);
        $quoteService->syncItems($quote, $preparedQuote['items']);

        $order = app(OrderService::class)->createFromQuote($quote);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'quote_id' => $quote->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'status' => Quote::STATUS_CONVERTED,
        ]);
    }

    public function test_quote_service_rejects_client_from_another_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $foreignClient = Client::query()->create([
            'user_id' => $other->id,
            'name' => 'Cliente Externo',
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);

        app(QuoteService::class)->prepareData($user, [
            'client_id' => $foreignClient->id,
            'items' => [
                ['item_name' => 'Teste', 'quantity' => 1, 'unit_price' => 10],
            ],
        ]);
    }

    public function test_quote_and_order_accept_custom_item_even_with_invalid_recipe_id(): void
    {
        $user = User::factory()->create();
        $service = app(QuoteService::class);

        $preparedQuote = $service->prepareData($user, [
            'items' => [
                [
                    'recipe_id' => 999999,
                    'item_name' => 'Pacote de mini-salgados',
                    'quantity' => 2,
                    'unit_price' => 350,
                ],
            ],
        ]);

        $this->assertCount(1, $preparedQuote['items']);
        $this->assertNull($preparedQuote['items'][0]['recipe_id']);
        $this->assertSame('Pacote de mini-salgados', $preparedQuote['items'][0]['item_name']);

        $preparedOrder = app(OrderService::class)->prepareData($user, [
            'payment_status' => Order::PAYMENT_OPEN,
            'status' => Order::STATUS_PENDING,
            'items' => [
                [
                    'recipe_id' => 999999,
                    'item_name' => 'Combo salgado personalizado',
                    'quantity' => 1,
                    'unit_price' => 500,
                ],
            ],
        ]);

        $this->assertCount(1, $preparedOrder['items']);
        $this->assertNull($preparedOrder['items'][0]['recipe_id']);
        $this->assertSame('Combo salgado personalizado', $preparedOrder['items'][0]['item_name']);
    }

    public function test_quote_created_without_client_can_be_updated_with_client_later(): void
    {
        $user = User::factory()->create();
        $service = app(QuoteService::class);

        $prepared = $service->prepareData($user, [
            'status' => Quote::STATUS_DRAFT,
            'type' => Quote::TYPE_PICKUP,
            'items' => [
                ['item_name' => 'Caixa de salgados', 'quantity' => 1, 'unit_price' => 300],
            ],
        ]);

        $quote = Quote::query()->create($prepared['attributes']);
        $service->syncItems($quote, $prepared['items']);

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Tardio',
            'is_active' => true,
        ]);

        $preparedUpdate = $service->prepareData($user, [
            'client_id' => $client->id,
            'status' => Quote::STATUS_SENT,
            'type' => Quote::TYPE_PICKUP,
            'reference' => $quote->reference,
            'items' => [
                ['item_name' => 'Caixa de salgados', 'quantity' => 1, 'unit_price' => 300],
            ],
        ], $quote->user);

        $quote->forceFill($preparedUpdate['attributes'])->save();

        $this->assertSame($client->id, $quote->fresh()->client_id);
    }

    public function test_admin_can_attach_client_from_different_owner_when_updating_quote(): void
    {
        $adminRole = Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $owner = User::factory()->create();

        $foreignClient = Client::query()->create([
            'user_id' => $admin->id,
            'name' => 'Cliente do Admin',
            'is_active' => true,
        ]);

        $prepared = app(QuoteService::class)->prepareData($admin, [
            'client_id' => $foreignClient->id,
            'items' => [
                ['item_name' => 'Produto avulso', 'quantity' => 1, 'unit_price' => 100],
            ],
        ], $owner);

        $this->assertSame($foreignClient->id, $prepared['attributes']['client_id']);
    }
}
