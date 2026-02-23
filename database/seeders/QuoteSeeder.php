<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Quote;
use App\Models\Recipe;
use App\Models\User;
use App\Services\QuoteService;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'admin@bakeryapp.test')
            ->first();

        if (! $user) {
            return;
        }

        Quote::query()
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

        $quoteService = app(QuoteService::class);

        $quotes = [
            [
                'reference' => 'ORC-2026-001-'.$user->id,
                'client' => 'Eventos MZ',
                'status' => Quote::STATUS_SENT,
                'type' => Quote::TYPE_DELIVERY,
                'quote_date' => now()->subDays(5)->toDateString(),
                'delivery_date' => now()->addDays(3)->toDateString(),
                'delivery_time' => '14:00:00',
                'additional_fee' => 150,
                'discount' => 80,
                'items' => [
                    ['recipe' => 'Bolo de chocolate caseiro', 'quantity' => 2, 'unit_price' => 980],
                    ['recipe' => 'Coxinha de frango', 'quantity' => 60, 'unit_price' => 14],
                ],
            ],
            [
                'reference' => 'ORC-2026-002-'.$user->id,
                'client' => 'Empresa Sol Nascente',
                'status' => Quote::STATUS_APPROVED,
                'type' => Quote::TYPE_PICKUP,
                'quote_date' => now()->subDays(2)->toDateString(),
                'delivery_date' => now()->addDays(1)->toDateString(),
                'delivery_time' => '10:30:00',
                'additional_fee' => 0,
                'discount' => 120,
                'items' => [
                    ['recipe' => 'Empada de frango', 'quantity' => 80, 'unit_price' => 22],
                    ['recipe' => 'Pastel de queijo', 'quantity' => 100, 'unit_price' => 13],
                ],
            ],
            [
                'reference' => 'ORC-2026-003-'.$user->id,
                'client' => 'Cantina Horizonte',
                'status' => Quote::STATUS_DRAFT,
                'type' => Quote::TYPE_DELIVERY,
                'quote_date' => now()->toDateString(),
                'delivery_date' => now()->addDays(4)->toDateString(),
                'delivery_time' => '09:00:00',
                'additional_fee' => 200,
                'discount' => 0,
                'items' => [
                    ['recipe' => 'Pão doce clássico', 'quantity' => 120, 'unit_price' => 18],
                    ['item_name' => 'Sucos naturais (garrafa 1L)', 'quantity' => 20, 'unit_price' => 95],
                ],
            ],
        ];

        foreach ($quotes as $quoteData) {
            $client = $clients->get($quoteData['client']);
            if (! $client) {
                continue;
            }

            $items = [];
            foreach ($quoteData['items'] as $itemData) {
                $recipe = isset($itemData['recipe']) ? $recipes->get($itemData['recipe']) : null;
                $items[] = [
                    'recipe_id' => $recipe?->id,
                    'item_name' => $itemData['item_name'] ?? $recipe?->name,
                    'quantity' => (int) $itemData['quantity'],
                    'unit_price' => (float) $itemData['unit_price'],
                ];
            }

            $prepared = $quoteService->prepareData($user, [
                'client_id' => $client->id,
                'reference' => $quoteData['reference'],
                'status' => $quoteData['status'],
                'type' => $quoteData['type'],
                'quote_date' => $quoteData['quote_date'],
                'delivery_date' => $quoteData['delivery_date'],
                'delivery_time' => $quoteData['delivery_time'],
                'additional_fee' => $quoteData['additional_fee'],
                'discount' => $quoteData['discount'],
                'notes' => '[seed] Orçamento de demonstração.',
                'items' => $items,
            ]);

            $quote = Quote::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'reference' => $quoteData['reference'],
                ],
                $prepared['attributes']
            );

            $quoteService->syncItems($quote, $prepared['items']);
        }
    }
}
