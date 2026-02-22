<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryMovementService
{
    public function record(
        Ingredient $ingredient,
        User $user,
        string $type,
        int $quantityG,
        string|CarbonInterface|null $movedAt = null,
        ?string $notes = null,
        ?float $totalCost = null,
    ): InventoryMovement {
        if ($quantityG === 0) {
            throw ValidationException::withMessages([
                'quantity_g' => 'A quantidade deve ser diferente de zero.',
            ]);
        }

        return DB::transaction(function () use ($ingredient, $user, $type, $quantityG, $movedAt, $notes, $totalCost): InventoryMovement {
            /** @var Ingredient $lockedIngredient */
            $lockedIngredient = Ingredient::query()
                ->lockForUpdate()
                ->findOrFail($ingredient->id);

            $nextStock = (int) round((float) $lockedIngredient->stock_quantity_g) + $quantityG;

            if ($nextStock < 0) {
                $baseUnit = $lockedIngredient->baseUnit();

                throw ValidationException::withMessages([
                    'quantity_g' => sprintf(
                        'Estoque insuficiente. Disponível: %s %s.',
                        number_format(max((float) round((float) $lockedIngredient->stock_quantity_g), 0), 0, ',', '.'),
                        $baseUnit,
                    ),
                ]);
            }

            $lockedIngredient->stock_quantity_g = max($nextStock, 0);
            $lockedIngredient->save();

            $unitCost = max((float) $lockedIngredient->cost_per_gram, 0.0);
            $resolvedTotalCost = $totalCost;
            if ($resolvedTotalCost === null) {
                $resolvedTotalCost = abs($quantityG) * $unitCost;
            }

            return InventoryMovement::query()->create([
                'ingredient_id' => $lockedIngredient->id,
                'user_id' => $user->id,
                'type' => $type,
                'quantity_g' => $quantityG,
                'unit_cost' => $unitCost,
                'total_cost' => $resolvedTotalCost,
                'moved_at' => $movedAt ? Carbon::parse($movedAt) : now(),
                'notes' => $notes,
            ]);
        });
    }
}
