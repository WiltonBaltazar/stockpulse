<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QuoteService
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

        $additionalFee = $this->roundMoney(max((float) ($data['additional_fee'] ?? 0), 0));
        $discount = $this->roundMoney(max((float) ($data['discount'] ?? 0), 0));
        $subTotal = $this->roundMoney((float) collect($items)->sum('total_price'));
        $totalAmount = $this->roundMoney(max($subTotal + $additionalFee - $discount, 0));

        $reference = trim((string) ($data['reference'] ?? ''));
        if ($reference === '') {
            $seed = (string) ($client?->name ?: ($items[0]['item_name'] ?? 'ORCAMENTO'));
            $reference = $this->generateReference($targetUser, $seed);
        }

        return [
            'attributes' => [
                'user_id' => $targetUser->id,
                'client_id' => $client?->id,
                'reference' => $reference,
                'status' => (string) ($data['status'] ?? Quote::STATUS_DRAFT),
                'type' => (string) ($data['type'] ?? Quote::TYPE_PICKUP),
                'quote_date' => $data['quote_date'] ?? now()->toDateString(),
                'delivery_date' => $data['delivery_date'] ?? null,
                'delivery_time' => $data['delivery_time'] ?? null,
                'additional_fee' => $additionalFee,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
            ],
            'items' => $items,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function syncItems(Quote $quote, array $items): void
    {
        DB::transaction(function () use ($quote, $items): void {
            QuoteItem::query()->where('quote_id', $quote->id)->delete();

            foreach ($items as $item) {
                QuoteItem::query()->create($item + ['quote_id' => $quote->id]);
            }
        });
    }

    /**
     * @param  array<int, mixed>  $rawItems
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(User $owner, array $rawItems): array
    {
        if ($rawItems === []) {
            throw ValidationException::withMessages([
                'items' => 'Adicione pelo menos um item no orçamento.',
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
                'items' => 'Adicione pelo menos um item válido no orçamento.',
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
            Quote::query()
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
            return 'ORC';
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
}
