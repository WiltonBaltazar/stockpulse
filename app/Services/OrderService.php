<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Quote;
use App\Models\Recipe;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  User|null  $owner
     * @return array{attributes: array<string, mixed>, items: array<int, array<string, mixed>>}
     */
    public function prepareData(User $actor, array $data, ?User $owner = null): array
    {
        $targetUser = $owner ?? $actor;
        $client = $this->resolveClient($targetUser, $data['client_id'] ?? null, $actor);
        $items = $this->normalizeItems($targetUser, (array) ($data['items'] ?? []));
        $totalAmount = $this->roundMoney((float) collect($items)->sum('total_price'));

        $reference = trim((string) ($data['reference'] ?? ''));
        if ($reference === '') {
            $seed = (string) ($client?->name ?: ($items[0]['item_name'] ?? 'PEDIDO'));
            $reference = $this->generateReference($targetUser, $seed);
        }

        return [
            'attributes' => [
                'user_id' => $targetUser->id,
                'client_id' => $client?->id,
                'quote_id' => $data['quote_id'] ?? null,
                'reference' => $reference,
                'status' => (string) ($data['status'] ?? Order::STATUS_PENDING),
                'payment_status' => (string) ($data['payment_status'] ?? Order::PAYMENT_OPEN),
                'order_date' => $data['order_date'] ?? now(),
                'delivery_date' => $data['delivery_date'] ?? null,
                'total_amount' => $totalAmount,
                'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
            ],
            'items' => $items,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function syncItems(Order $order, array $items): void
    {
        DB::transaction(function () use ($order, $items): void {
            OrderItem::query()->where('order_id', $order->id)->delete();

            foreach ($items as $item) {
                OrderItem::query()->create($item + ['order_id' => $order->id]);
            }
        });
    }

    public function createFromQuote(Quote $quote): Order
    {
        return DB::transaction(function () use ($quote): Order {
            $lockedQuote = Quote::query()
                ->with(['items', 'client'])
                ->whereKey($quote->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedQuote) {
                throw ValidationException::withMessages([
                    'quote' => 'Orçamento não encontrado.',
                ]);
            }

            if ($lockedQuote->order) {
                return $lockedQuote->order;
            }

            if ($lockedQuote->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'quote' => 'O orçamento não possui itens para gerar pedido.',
                ]);
            }

            $reference = $this->generateReference($lockedQuote->user, (string) ($lockedQuote->client?->name ?: $lockedQuote->reference ?: 'PEDIDO'));

            $order = Order::query()->create([
                'user_id' => $lockedQuote->user_id,
                'client_id' => $lockedQuote->client_id,
                'quote_id' => $lockedQuote->id,
                'reference' => $reference,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_OPEN,
                'order_date' => now(),
                'delivery_date' => $lockedQuote->delivery_date
                    ? trim((string) $lockedQuote->delivery_date?->format('Y-m-d').' '.($lockedQuote->delivery_time ?: '08:00:00'))
                    : null,
                'total_amount' => $this->roundMoney((float) $lockedQuote->total_amount),
                'notes' => 'Gerado a partir do orçamento '.$lockedQuote->reference,
            ]);

            foreach ($lockedQuote->items as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'recipe_id' => $item->recipe_id,
                    'item_name' => $item->item_name,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => $this->roundMoney((float) $item->unit_price),
                    'total_price' => $this->roundMoney((float) $item->total_price),
                ]);
            }

            $lockedQuote->status = Quote::STATUS_CONVERTED;
            $lockedQuote->save();

            return $order;
        });
    }

    public function syncSalesAndFinancials(Order $order): void
    {
        $order->loadMissing(['items.recipe', 'client']);
        $existingSales = Sale::query()
            ->where('order_id', $order->id)
            ->get()
            ->keyBy('order_item_id');

        if (! $this->shouldRepresentAsSale($order)) {
            $this->removeSalesForOrder($order);

            return;
        }

        $orderItemIds = $order->items
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        foreach ($order->items as $item) {
            $sale = $existingSales->get((int) $item->id) ?? new Sale();
            $sale->forceFill([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'recipe_id' => $item->recipe_id,
                'client_id' => $order->client_id,
                'item_name' => $item->resolved_item_name,
                'customer_name' => $order->client?->name,
                'reference' => $sale->reference ?: Sale::generateReference((int) $order->user_id, $item->resolved_item_name),
                'status' => Sale::STATUS_COMPLETED,
                'channel' => Sale::CHANNEL_ONLINE,
                'payment_method' => Sale::PAYMENT_OTHER,
                'sold_at' => $order->order_date ?? now(),
                'quantity' => max((int) $item->quantity, 1),
                'unit_price' => $this->roundMoney((float) $item->unit_price),
                'total_amount' => $this->roundMoney((float) $item->total_price),
                'notes' => $sale->notes ?: 'Gerada automaticamente do pedido '.$order->reference,
            ])->save();

            $saleService = app(SaleService::class);

            try {
                $saleService->syncOperationalAndFinancials($sale);
            } catch (ValidationException) {
                $sale->forceFill([
                    'estimated_unit_cost' => null,
                    'estimated_total_cost' => null,
                    'estimated_profit' => null,
                ])->saveQuietly();

                $saleService->syncFinancialTransaction($sale);
            }
        }

        if ($orderItemIds === []) {
            $this->removeSalesForOrder($order);

            return;
        }

        $salesToRemove = Sale::query()
            ->where('order_id', $order->id)
            ->whereNotIn('order_item_id', $orderItemIds)
            ->get();

        foreach ($salesToRemove as $sale) {
            app(SaleService::class)->removeOperationalAndFinancials($sale);
            $sale->delete();
        }
    }

    public function removeSalesForOrder(Order $order): void
    {
        $sales = Sale::query()
            ->where('order_id', $order->id)
            ->get();

        foreach ($sales as $sale) {
            app(SaleService::class)->removeOperationalAndFinancials($sale);
            $sale->delete();
        }
    }

    /**
     * @param  array<int, mixed>  $rawItems
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(User $owner, array $rawItems): array
    {
        if ($rawItems === []) {
            throw ValidationException::withMessages([
                'items' => 'Adicione pelo menos um item no pedido.',
            ]);
        }

        $normalized = [];

        foreach ($rawItems as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $recipeId = $item['recipe_id'] ?? null;
            $recipe = null;
            if (filled($recipeId)) {
                $recipe = Recipe::query()
                    ->whereKey($recipeId)
                    ->where('user_id', $owner->id)
                    ->first();
            }

            $itemName = trim((string) ($item['item_name'] ?? ''));
            if (! $recipe && filled($recipeId) && $itemName === '') {
                throw ValidationException::withMessages([
                    "items.$index.recipe_id" => 'Receita não encontrada para este item.',
                ]);
            }

            if ($itemName === '' && $recipe) {
                $itemName = $recipe->name;
            }

            if ($itemName === '') {
                throw ValidationException::withMessages([
                    "items.$index.item_name" => 'Informe o nome do item ou selecione uma receita.',
                ]);
            }

            $quantity = max((int) round((float) ($item['quantity'] ?? 0)), 1);
            $unitPrice = $this->roundMoney(max((float) ($item['unit_price'] ?? 0), 0));

            if ($unitPrice <= 0 && $recipe) {
                $unitPrice = app(SaleService::class)->suggestedUnitPriceForRecipe($owner, (int) $recipe->id);
            }

            if ($unitPrice <= 0) {
                throw ValidationException::withMessages([
                    "items.$index.unit_price" => 'Preço unitário inválido.',
                ]);
            }

            $totalPrice = $this->roundMoney($quantity * $unitPrice);

            $normalized[] = [
                'recipe_id' => $recipe?->id,
                'item_name' => $itemName,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ];
        }

        if ($normalized === []) {
            throw ValidationException::withMessages([
                'items' => 'Adicione pelo menos um item válido no pedido.',
            ]);
        }

        return $normalized;
    }

    private function resolveClient(User $owner, mixed $clientId, ?User $actor = null): ?Client
    {
        if (! filled($clientId)) {
            return null;
        }

        $client = Client::query()
            ->whereKey($clientId)
            ->first();

        if (! $client) {
            throw ValidationException::withMessages([
                'client_id' => 'Cliente não encontrado.',
            ]);
        }

        if ((int) $client->user_id === (int) $owner->id) {
            return $client;
        }

        if ($actor?->isAdmin()) {
            return $client;
        }

        throw ValidationException::withMessages([
            'client_id' => 'Cliente não encontrado.',
        ]);
    }

    private function roundMoney(float $value): float
    {
        return round($value, 2);
    }

    private function generateReference(User $user, string $seed): string
    {
        $prefix = $this->buildReferencePrefix($seed);

        do {
            $reference = $prefix.$this->randomUpperAlphaNumeric(8);
        } while (
            Order::query()
                ->where('user_id', $user->id)
                ->where('reference', $reference)
                ->exists()
        );

        return $reference;
    }

    private function buildReferencePrefix(string $seed): string
    {
        $lettersOnly = strtoupper((string) Str::of(Str::ascii($seed))
            ->replaceMatches('/[^A-Za-z]/', ''));

        if ($lettersOnly === '') {
            return 'PED';
        }

        return str_pad(substr($lettersOnly, 0, 3), 3, 'X');
    }

    private function randomUpperAlphaNumeric(int $length): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxIndex = strlen($alphabet) - 1;
        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $token .= $alphabet[random_int(0, $maxIndex)];
        }

        return $token;
    }

    private function shouldRepresentAsSale(Order $order): bool
    {
        return $order->payment_status === Order::PAYMENT_PAID
            && $order->status !== Order::STATUS_CANCELLED;
    }
}
