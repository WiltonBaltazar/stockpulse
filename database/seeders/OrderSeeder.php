<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\Quote;
use App\Models\Recipe;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'admin@bakeryapp.test')
            ->first();

        if (! $user) {
            return;
        }

        Order::query()
            ->where('user_id', $user->id)
            ->where('notes', 'like', '[seed]%')
            ->delete();

        $clients = Client::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('name');

        $recipes = Recipe::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('name');

        $orderService = app(OrderService::class);

        $orders = [
            [
                'reference' => 'PED-2026-001-'.$user->id,
                'client' => 'Cliente Walk-in',
                'status' => Order::STATUS_DELIVERED,
                'payment_status' => Order::PAYMENT_PAID,
                'order_date' => now()->subDays(3),
                'delivery_date' => now()->subDays(3)->addHours(2),
                'items' => [
                    ['recipe' => 'Queijadinha de coco', 'quantity' => 25, 'unit_price' => 20],
                    ['recipe' => 'Rissol de carne', 'quantity' => 30, 'unit_price' => 16],
                ],
            ],
            [
                'reference' => 'PED-2026-002-'.$user->id,
                'client' => 'Café Central Maputo',
                'status' => Order::STATUS_PREPARING,
                'payment_status' => Order::PAYMENT_PARTIAL,
                'order_date' => now()->subDay(),
                'delivery_date' => now()->addDay(),
                'items' => [
                    ['recipe' => 'Biscoito de coco', 'quantity' => 120, 'unit_price' => 8.5],
                    ['item_name' => 'Salgados mini sortidos', 'quantity' => 90, 'unit_price' => 12],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            $client = $clients->get($orderData['client']);
            if (! $client) {
                continue;
            }

            $items = [];
            foreach ($orderData['items'] as $itemData) {
                $recipe = isset($itemData['recipe']) ? $recipes->get($itemData['recipe']) : null;
                $items[] = [
                    'recipe_id' => $recipe?->id,
                    'item_name' => $itemData['item_name'] ?? $recipe?->name,
                    'quantity' => (int) $itemData['quantity'],
                    'unit_price' => (float) $itemData['unit_price'],
                ];
            }

            $prepared = $orderService->prepareData($user, [
                'client_id' => $client->id,
                'reference' => $orderData['reference'],
                'status' => $orderData['status'],
                'payment_status' => $orderData['payment_status'],
                'order_date' => $orderData['order_date'],
                'delivery_date' => $orderData['delivery_date'],
                'notes' => '[seed] Pedido de demonstração.',
                'items' => $items,
            ]);

            $order = Order::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'reference' => $orderData['reference'],
                ],
                $prepared['attributes']
            );

            $orderService->syncItems($order, $prepared['items']);
        }

        $approvedQuote = Quote::query()
            ->where('user_id', $user->id)
            ->where('status', Quote::STATUS_APPROVED)
            ->latest('id')
            ->first();

        if ($approvedQuote) {
            $order = $orderService->createFromQuote($approvedQuote);

            if (str_starts_with((string) $order->notes, '[seed]') === false) {
                $order->notes = '[seed] Pedido convertido automaticamente do orçamento.';
                $order->save();
            }
        }
    }
}
