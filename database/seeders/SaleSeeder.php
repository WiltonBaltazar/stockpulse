<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'admin@bakeryapp.test')
            ->first();

        if (! $user) {
            return;
        }

        $saleService = app(SaleService::class);

        $seedSales = Sale::query()
            ->where('notes', 'like', '[seed]%')
            ->get();

        if ($seedSales->isNotEmpty()) {
            foreach ($seedSales as $seedSale) {
                $saleService->removeOperationalAndFinancials($seedSale);
            }

            Sale::query()
                ->whereIn('id', $seedSales->pluck('id')->all())
                ->delete();
        }

        $sales = [
            [
                'recipe' => 'Pão doce clássico',
                'channel' => Sale::CHANNEL_OFFLINE,
                'payment_method' => Sale::PAYMENT_CASH,
                'status' => Sale::STATUS_COMPLETED,
                'quantity' => 24,
                'unit_price' => 18.00,
                'customer_name' => 'Balcão',
                'reference' => 'OFF-001-%s',
                'days_ago' => 8,
                'notes' => '[seed] Venda balcão de pão doce.',
            ],
            [
                'recipe' => 'Coxinha de frango',
                'channel' => Sale::CHANNEL_OFFLINE,
                'payment_method' => Sale::PAYMENT_CASH,
                'status' => Sale::STATUS_COMPLETED,
                'quantity' => 45,
                'unit_price' => 14.00,
                'customer_name' => 'Balcão manhã',
                'reference' => 'OFF-002-%s',
                'days_ago' => 7,
                'notes' => '[seed] Venda de coxinha no atendimento local.',
            ],
            [
                'recipe' => 'Bolo de chocolate caseiro',
                'channel' => Sale::CHANNEL_ONLINE,
                'payment_method' => Sale::PAYMENT_MOBILE,
                'status' => Sale::STATUS_COMPLETED,
                'quantity' => 8,
                'unit_price' => 95.00,
                'customer_name' => 'Pedido Instagram',
                'reference' => 'ONL-003-%s',
                'days_ago' => 6,
                'notes' => '[seed] Encomenda online de bolo.',
            ],
            [
                'recipe' => 'Empada de frango',
                'channel' => Sale::CHANNEL_ONLINE,
                'payment_method' => Sale::PAYMENT_TRANSFER,
                'status' => Sale::STATUS_COMPLETED,
                'quantity' => 20,
                'unit_price' => 22.00,
                'customer_name' => 'Encomenda escritório',
                'reference' => 'ONL-004-%s',
                'days_ago' => 5,
                'notes' => '[seed] Encomenda online de empadas.',
            ],
            [
                'recipe' => 'Biscoito de coco',
                'channel' => Sale::CHANNEL_OFFLINE,
                'payment_method' => Sale::PAYMENT_TRANSFER,
                'status' => Sale::STATUS_COMPLETED,
                'quantity' => 40,
                'unit_price' => 8.50,
                'customer_name' => 'Cantina local',
                'reference' => 'OFF-005-%s',
                'days_ago' => 4,
                'notes' => '[seed] Venda para parceiro local.',
            ],
            [
                'recipe' => 'Rissol de carne',
                'channel' => Sale::CHANNEL_OFFLINE,
                'payment_method' => Sale::PAYMENT_CASH,
                'status' => Sale::STATUS_COMPLETED,
                'quantity' => 28,
                'unit_price' => 16.00,
                'customer_name' => 'Venda rápida',
                'reference' => 'OFF-006-%s',
                'days_ago' => 3,
                'notes' => '[seed] Venda rápida de rissol no balcão.',
            ],
            [
                'recipe' => 'Pastel de queijo',
                'channel' => Sale::CHANNEL_OFFLINE,
                'payment_method' => Sale::PAYMENT_CASH,
                'status' => Sale::STATUS_COMPLETED,
                'quantity' => 34,
                'unit_price' => 13.00,
                'customer_name' => 'Balcão fim de tarde',
                'reference' => 'OFF-007-%s',
                'days_ago' => 2,
                'notes' => '[seed] Venda de pastel de queijo.',
            ],
            [
                'item_name' => 'Combo misto (3 doces + 3 salgados)',
                'channel' => Sale::CHANNEL_ONLINE,
                'payment_method' => Sale::PAYMENT_MOBILE,
                'status' => Sale::STATUS_COMPLETED,
                'quantity' => 10,
                'unit_price' => 68.00,
                'customer_name' => 'Pedido WhatsApp',
                'reference' => 'ONL-008-%s',
                'days_ago' => 2,
                'notes' => '[seed] Combo misto sem receita específica.',
            ],
            [
                'recipe' => 'Queijadinha de coco',
                'channel' => Sale::CHANNEL_ONLINE,
                'payment_method' => Sale::PAYMENT_TRANSFER,
                'status' => Sale::STATUS_PENDING,
                'quantity' => 12,
                'unit_price' => 20.00,
                'customer_name' => 'Evento corporativo',
                'reference' => 'ONL-009-%s',
                'days_ago' => 1,
                'notes' => '[seed] Pedido aguardando confirmação de pagamento.',
            ],
            [
                'recipe' => 'Coxinha de frango',
                'channel' => Sale::CHANNEL_ONLINE,
                'payment_method' => Sale::PAYMENT_TRANSFER,
                'status' => Sale::STATUS_CANCELLED,
                'quantity' => 18,
                'unit_price' => 14.00,
                'customer_name' => 'Pedido cancelado',
                'reference' => 'ONL-010-%s',
                'days_ago' => 1,
                'notes' => '[seed] Cancelado por atraso de confirmação.',
            ],
        ];

        $recipesByName = Recipe::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('name');

        foreach ($sales as $data) {
            $recipe = isset($data['recipe']) ? $recipesByName->get($data['recipe']) : null;

            $reference = sprintf($data['reference'], $user->id);
            $quantity = round((float) $data['quantity'], 3);
            $unitPrice = round((float) $data['unit_price'], 2);

            $saleData = [
                'recipe_id' => $recipe?->id,
                'item_name' => $data['item_name'] ?? null,
                'channel' => $data['channel'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'customer_name' => $data['customer_name'],
                'reference' => $reference,
                'sold_at' => now()->subDays((int) $data['days_ago']),
                'notes' => $data['notes'].' ['.$user->email.']',
            ];

            $prepared = $saleService->prepareData($user, $saleData);

            $sale = Sale::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'reference' => $reference,
                ],
                $prepared
            );

            $saleService->syncOperationalAndFinancials($sale);
        }
    }
}
