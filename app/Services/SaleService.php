<?php

namespace App\Services;

use App\Models\Client;
use App\Models\FinancialTransaction;
use App\Models\ProductionBatch;
use App\Models\Recipe;
use App\Models\Sale;
use App\Models\SaleBatchAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function prepareData(User $actor, array $data, ?User $owner = null): array
    {
        $targetUser = $owner ?? $actor;

        $quantity = max((int) round((float) ($data['quantity'] ?? 0)), 1);
        $recipeId = $data['recipe_id'] ?? null;
        $itemName = trim((string) ($data['item_name'] ?? ''));
        $customerName = trim((string) ($data['customer_name'] ?? ''));
        $client = null;
        $clientId = $data['client_id'] ?? null;

        if (filled($clientId)) {
            $client = Client::query()
                ->whereKey($clientId)
                ->where('user_id', $targetUser->id)
                ->first();

            if (! $client) {
                throw ValidationException::withMessages([
                    'client_id' => 'Cliente não encontrado.',
                ]);
            }
        }

        if (! $recipeId && $itemName === '') {
            throw ValidationException::withMessages([
                'item_name' => 'Informe um item vendido ou selecione uma receita.',
            ]);
        }

        $estimatedUnitCost = null;
        $estimatedTotalCost = null;
        $estimatedProfit = null;
        $unitPrice = $this->roundMoney(max((float) ($data['unit_price'] ?? 0), 0.0));

        if ($recipeId) {
            $recipe = Recipe::query()
                ->with(['items.ingredient'])
                ->whereKey($recipeId)
                ->where('user_id', $targetUser->id)
                ->first();

            if (! $recipe) {
                throw ValidationException::withMessages([
                    'recipe_id' => 'Receita não encontrada.',
                ]);
            }

            $unitPrice = $this->estimateSaleUnitPrice($recipe, $targetUser);
            $estimatedUnitCost = $this->roundMoney($this->estimateUnitCost($recipe, $targetUser));
            $estimatedTotalCost = $this->roundMoney($estimatedUnitCost * (float) $quantity);

            $itemName = $recipe->name;
        } elseif ($unitPrice <= 0) {
            throw ValidationException::withMessages([
                'unit_price' => 'Informe um preço unitário maior que zero.',
            ]);
        }

        if ($client) {
            $customerName = $client->name;
        }

        $totalAmount = $this->roundMoney(((float) $quantity) * $unitPrice);

        if ($estimatedTotalCost !== null) {
            $estimatedProfit = $this->roundMoney($totalAmount - $estimatedTotalCost);
        }

        $reference = trim((string) ($data['reference'] ?? ''));
        if ($reference === '') {
            $reference = $this->generateReference($targetUser, $itemName);
        }

        return array_merge($data, [
            'user_id' => $targetUser->id,
            'client_id' => $client?->id,
            'item_name' => $itemName === '' ? null : $itemName,
            'customer_name' => $customerName === '' ? null : $customerName,
            'reference' => $reference,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'estimated_unit_cost' => $estimatedUnitCost,
            'estimated_total_cost' => $estimatedTotalCost,
            'estimated_profit' => $estimatedProfit,
            'sold_at' => $data['sold_at'] ?? now(),
        ]);
    }

    public function suggestedUnitPriceForRecipe(User $user, int $recipeId): float
    {
        $recipe = Recipe::query()
            ->whereKey($recipeId)
            ->where('user_id', $user->id)
            ->first();

        if (! $recipe) {
            return 0.0;
        }

        return $this->roundMoney($this->estimateSaleUnitPrice($recipe, $user));
    }

    public function syncOperationalAndFinancials(Sale $sale): void
    {
        DB::transaction(function () use ($sale): void {
            /** @var Sale|null $lockedSale */
            $lockedSale = Sale::query()
                ->whereKey($sale->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedSale) {
                return;
            }

            $this->releaseBatchAllocations($lockedSale);
            $allocationTotals = $this->allocateBatchesForSale($lockedSale);
            $this->updateSaleCostSnapshot($lockedSale, $allocationTotals);
            $this->syncFinancialTransaction($lockedSale);
        });

        $sale->refresh();
    }

    public function removeOperationalAndFinancials(Sale $sale): void
    {
        DB::transaction(function () use ($sale): void {
            /** @var Sale|null $lockedSale */
            $lockedSale = Sale::query()
                ->whereKey($sale->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedSale) {
                return;
            }

            $this->releaseBatchAllocations($lockedSale);
            $this->removeFinancialTransaction($lockedSale);
        });
    }

    public function syncFinancialTransaction(Sale $sale): void
    {
        $references = $this->financialReferences($sale);

        FinancialTransaction::query()
            ->where('user_id', $sale->user_id)
            ->whereIn('reference', $references['cleanup'])
            ->whereNotIn('reference', [$references['revenue'], $references['cogs']])
            ->delete();

        FinancialTransaction::query()->updateOrCreate(
            [
                'user_id' => $sale->user_id,
                'reference' => $references['revenue'],
            ],
            [
                'user_id' => $sale->user_id,
                'transaction_date' => optional($sale->sold_at)->toDateString() ?? now()->toDateString(),
                'type' => FinancialTransaction::TYPE_INCOME,
                'status' => $sale->status,
                'source' => FinancialTransaction::SOURCE_SALES,
                'counterparty' => $sale->customer_name ?: $sale->resolved_item_name,
                'amount' => (float) $sale->total_amount,
                'notes' => $this->buildFinanceNotes($sale),
            ]
        );

        if (
            $sale->status === Sale::STATUS_COMPLETED
            && $sale->recipe_id
            && $sale->estimated_total_cost !== null
            && (float) $sale->estimated_total_cost > 0
        ) {
            FinancialTransaction::query()->updateOrCreate(
                [
                    'user_id' => $sale->user_id,
                    'reference' => $references['cogs'],
                ],
                [
                    'user_id' => $sale->user_id,
                    'transaction_date' => optional($sale->sold_at)->toDateString() ?? now()->toDateString(),
                    'type' => FinancialTransaction::TYPE_EXPENSE,
                    'status' => FinancialTransaction::STATUS_COMPLETED,
                    'source' => FinancialTransaction::SOURCE_COGS,
                    'counterparty' => $sale->resolved_item_name,
                    'amount' => $this->roundMoney((float) $sale->estimated_total_cost),
                    'notes' => 'Custo do que foi vendido registado automaticamente para a venda '.$sale->reference,
                ]
            );

            return;
        }

        FinancialTransaction::query()
            ->where('user_id', $sale->user_id)
            ->where('reference', $references['cogs'])
            ->delete();
    }

    public function removeFinancialTransaction(Sale $sale): void
    {
        $references = $this->financialReferences($sale)['cleanup'];

        if ($references === []) {
            return;
        }

        FinancialTransaction::query()
            ->where('user_id', $sale->user_id)
            ->whereIn('reference', $references)
            ->delete();
    }

    private function releaseBatchAllocations(Sale $sale): void
    {
        $allocations = SaleBatchAllocation::query()
            ->where('sale_id', $sale->id)
            ->lockForUpdate()
            ->get();

        if ($allocations->isEmpty()) {
            return;
        }

        $batchIds = $allocations
            ->pluck('production_batch_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $batchesById = ProductionBatch::query()
            ->whereIn('id', $batchIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($allocations as $allocation) {
            $batch = $batchesById->get($allocation->production_batch_id);
            if (! $batch) {
                continue;
            }

            $currentSold = max((int) ($batch->sold_units ?? 0), 0);
            $releasedUnits = max((int) $allocation->quantity_units, 0);
            $batch->sold_units = max($currentSold - $releasedUnits, 0);
            $batch->save();
        }

        SaleBatchAllocation::query()
            ->where('sale_id', $sale->id)
            ->delete();
    }

    /**
     * @return array{unit_cost: float, total_cost: float}|null
     */
    private function allocateBatchesForSale(Sale $sale): ?array
    {
        if (! $this->shouldAllocateStock($sale)) {
            return null;
        }

        $requiredUnits = max((int) round((float) $sale->quantity), 1);
        $remainingUnits = $requiredUnits;
        $totalCost = 0.0;

        $batches = ProductionBatch::query()
            ->where('user_id', $sale->user_id)
            ->where('recipe_id', $sale->recipe_id)
            ->orderBy('produced_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remainingUnits <= 0) {
                break;
            }

            $availableUnits = max((int) round((float) $batch->produced_units) - max((int) $batch->sold_units, 0), 0);
            if ($availableUnits <= 0) {
                continue;
            }

            $allocatedUnits = min($availableUnits, $remainingUnits);
            $unitCogs = round(max((float) $batch->cogs_per_unit, 0.0), 4);
            $lineCost = $this->roundMoney($allocatedUnits * $unitCogs);

            SaleBatchAllocation::query()->create([
                'sale_id' => $sale->id,
                'production_batch_id' => $batch->id,
                'quantity_units' => $allocatedUnits,
                'unit_cogs' => $unitCogs,
                'total_cogs' => $lineCost,
            ]);

            $batch->sold_units = max((int) $batch->sold_units, 0) + $allocatedUnits;
            $batch->save();

            $totalCost += $lineCost;
            $remainingUnits -= $allocatedUnits;
        }

        if ($remainingUnits > 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Estoque de produção insuficiente para esta venda. Produza mais unidades ou reduza a quantidade.',
            ]);
        }

        $totalCost = $this->roundMoney($totalCost);
        $unitCost = $requiredUnits > 0 ? round($totalCost / $requiredUnits, 4) : 0.0;

        return [
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
        ];
    }

    /**
     * @param  array{unit_cost: float, total_cost: float}|null  $allocationTotals
     */
    private function updateSaleCostSnapshot(Sale $sale, ?array $allocationTotals): void
    {
        if ($allocationTotals === null) {
            $sale->forceFill([
                'estimated_unit_cost' => null,
                'estimated_total_cost' => null,
                'estimated_profit' => null,
            ])->saveQuietly();

            return;
        }

        $totalCost = $this->roundMoney((float) $allocationTotals['total_cost']);
        $totalAmount = $this->roundMoney((float) $sale->total_amount);
        $profit = $this->roundMoney($totalAmount - $totalCost);

        $sale->forceFill([
            'estimated_unit_cost' => (float) $allocationTotals['unit_cost'],
            'estimated_total_cost' => $totalCost,
            'estimated_profit' => $profit,
        ])->saveQuietly();
    }

    private function shouldAllocateStock(Sale $sale): bool
    {
        return $sale->status === Sale::STATUS_COMPLETED
            && $sale->recipe_id !== null;
    }

    private function estimateUnitCost(Recipe $recipe, User $user): float
    {
        $lastBatch = ProductionBatch::query()
            ->where('recipe_id', $recipe->id)
            ->where('user_id', $user->id)
            ->orderByDesc('produced_at')
            ->orderByDesc('id')
            ->first();

        if ($lastBatch) {
            return max((float) $lastBatch->cogs_per_unit, 0.0);
        }

        $ingredientsCost = (float) $recipe->ingredients_cost;
        $overheadFactor = 1 + ((float) $recipe->overhead_percent / 100);
        $yield = max((float) $recipe->yield_units, 0.000001);

        $baseCostPerUnit = ($ingredientsCost * $overheadFactor) / $yield;

        return max($baseCostPerUnit + (float) $recipe->packaging_cost_per_unit, 0.0);
    }

    private function estimateSaleUnitPrice(Recipe $recipe, User $user): float
    {
        $lastBatch = ProductionBatch::query()
            ->where('recipe_id', $recipe->id)
            ->where('user_id', $user->id)
            ->orderByDesc('produced_at')
            ->orderByDesc('id')
            ->first();

        if ($lastBatch && (float) $lastBatch->suggested_unit_price > 0) {
            return $this->roundMoney((float) $lastBatch->suggested_unit_price);
        }

        if ((float) $recipe->final_unit_price > 0) {
            return $this->roundMoney((float) $recipe->final_unit_price);
        }

        $unitCost = $this->estimateUnitCost($recipe, $user);

        return $this->roundMoney(max($unitCost * 1.35, 0.01));
    }

    private function roundMoney(float $value): float
    {
        return round($value, 2);
    }

    private function generateReference(User $user, string $productName): string
    {
        return Sale::generateReference($user->id, $productName);
    }

    /**
     * @return array{revenue: string, cogs: string, cleanup: array<int, string>}
     */
    private function financialReferences(Sale $sale): array
    {
        $legacyBase = 'SALE-'.$sale->id;
        $base = trim((string) $sale->reference) !== '' ? trim((string) $sale->reference) : $legacyBase;

        $revenue = $base.'-REV';
        $cogs = $base.'-COGS';

        $cleanup = array_values(array_unique(array_filter([
            $base,
            $legacyBase,
            $revenue,
            $cogs,
            $legacyBase.'-REV',
            $legacyBase.'-COGS',
        ])));

        return [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'cleanup' => $cleanup,
        ];
    }

    private function buildFinanceNotes(Sale $sale): string
    {
        $channel = Sale::channelOptions()[$sale->channel] ?? $sale->channel;
        $payment = Sale::paymentOptions()[$sale->payment_method] ?? $sale->payment_method;
        $parts = ['Venda '.$channel, 'Pagamento '.$payment];

        if ($sale->notes) {
            $parts[] = $sale->notes;
        }

        return implode(' | ', $parts);
    }
}
