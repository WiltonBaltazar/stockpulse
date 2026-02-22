<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchItem;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionBatchService
{
    /**
     * @return array{
     *   recipe: Recipe,
     *   produced_units: float,
     *   ingredients_cost: float,
     *   packaging_cost: float,
     *   overhead_cost: float,
     *   total_cogs: float,
     *   cogs_per_unit: float,
     *   suggested_unit_price: float,
     *   margin_per_unit: float,
     *   lines: array<int, array{
     *      ingredient_id: int,
     *      ingredient_name: string,
     *      required_quantity_g: float,
     *      stock_quantity_g: float,
     *      remaining_stock_g: float,
     *      unit_cost: float,
     *      line_cost: float
     *   }>,
     *   shortages: array<int, array{
     *      ingredient_name: string,
     *      shortage_g: float
     *   }>
     * }
     */
    public function previewForRecipe(Recipe $recipe, float $producedUnits): array
    {
        $producedUnits = max((int) round($producedUnits), 0);
        $yield = max((int) round((float) $recipe->yield_units), 1);
        $factor = $producedUnits / $yield;

        $items = $recipe->relationLoaded('items')
            ? $recipe->items
            : $recipe->items()->with('ingredient')->get();

        $lines = [];
        $shortages = [];
        $ingredientsCost = 0.0;

        foreach ($items as $item) {
            /** @var RecipeItem $item */
            $ingredient = $item->ingredient;
            if (! $ingredient) {
                continue;
            }

            $requiredQuantity = max((int) round((float) $item->quantity_used_g * $factor), 0);
            $stockQuantity = max((int) round((float) $ingredient->stock_quantity_g), 0);
            $remainingStock = $stockQuantity - $requiredQuantity;
            $unitCost = max((float) $ingredient->cost_per_gram, 0.0);
            $lineCost = $requiredQuantity * $unitCost;

            if ($requiredQuantity > 0 && $remainingStock < 0) {
                $shortages[] = [
                    'ingredient_name' => $ingredient->name,
                    'shortage_g' => abs($remainingStock),
                ];
            }

            $ingredientsCost += $lineCost;
            $lines[] = [
                'ingredient_id' => (int) $ingredient->id,
                'ingredient_name' => $ingredient->name,
                'required_quantity_g' => $requiredQuantity,
                'stock_quantity_g' => $stockQuantity,
                'remaining_stock_g' => $remainingStock,
                'unit_cost' => $unitCost,
                'line_cost' => $lineCost,
            ];
        }

        $packagingCost = (float) $producedUnits * max((float) $recipe->packaging_cost_per_unit, 0.0);
        $overheadCost = $ingredientsCost * (max((float) $recipe->overhead_percent, 0.0) / 100);
        $totalCogs = $ingredientsCost + $packagingCost + $overheadCost;
        $cogsPerUnit = $producedUnits > 0 ? ($totalCogs / (float) $producedUnits) : 0.0;
        $suggestedUnitPrice = max((float) $recipe->final_unit_price, 0.0);

        return [
            'recipe' => $recipe,
            'produced_units' => $producedUnits,
            'ingredients_cost' => $ingredientsCost,
            'packaging_cost' => $packagingCost,
            'overhead_cost' => $overheadCost,
            'total_cogs' => $totalCogs,
            'cogs_per_unit' => $cogsPerUnit,
            'suggested_unit_price' => $suggestedUnitPrice,
            'margin_per_unit' => $suggestedUnitPrice - $cogsPerUnit,
            'lines' => $lines,
            'shortages' => $shortages,
        ];
    }

    public function createBatch(
        User $user,
        int $recipeId,
        float $producedUnits,
        string|CarbonInterface|null $producedAt = null,
        ?string $notes = null,
    ): ProductionBatch {
        $producedUnits = (float) $producedUnits;
        $producedUnits = (float) max((int) round($producedUnits), 0);

        if ($producedUnits <= 0) {
            throw ValidationException::withMessages([
                'produced_units' => 'Informe uma quantidade de produção maior que zero.',
            ]);
        }

        return DB::transaction(function () use ($user, $recipeId, $producedUnits, $producedAt, $notes): ProductionBatch {
            $recipeQuery = Recipe::query()
                ->with(['items.ingredient'])
                ->whereKey($recipeId);

            if (! $user->isAdmin()) {
                $recipeQuery->where('user_id', $user->id);
            }

            $recipe = $recipeQuery->first();
            if (! $recipe) {
                throw ValidationException::withMessages([
                    'recipe_id' => 'Receita não encontrada.',
                ]);
            }

            $ingredientIds = $recipe->items
                ->pluck('ingredient_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $lockedIngredients = Ingredient::query()
                ->whereIn('id', $ingredientIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($recipe->items as $item) {
                $item->setRelation('ingredient', $lockedIngredients->get($item->ingredient_id));
            }

            $snapshot = $this->previewForRecipe($recipe, $producedUnits);

            if ($snapshot['shortages'] !== []) {
                $messages = collect($snapshot['shortages'])
                    ->map(fn (array $shortage): string => sprintf(
                        '%s: faltam %s g.',
                        $shortage['ingredient_name'],
                        number_format((float) round((float) $shortage['shortage_g']), 0, ',', '.')
                    ))
                    ->implode(' ');

                throw ValidationException::withMessages([
                    'produced_units' => 'Estoque insuficiente para esta produção. '.$messages,
                ]);
            }

            $batch = ProductionBatch::query()->create([
                'recipe_id' => $recipe->id,
                'user_id' => $user->id,
                'produced_at' => $producedAt ? Carbon::parse($producedAt)->toDateString() : now()->toDateString(),
                'produced_units' => $snapshot['produced_units'],
                'sold_units' => 0,
                'ingredients_cost' => $snapshot['ingredients_cost'],
                'packaging_cost' => $snapshot['packaging_cost'],
                'overhead_cost' => $snapshot['overhead_cost'],
                'total_cogs' => $snapshot['total_cogs'],
                'cogs_per_unit' => $snapshot['cogs_per_unit'],
                'suggested_unit_price' => $snapshot['suggested_unit_price'],
                'notes' => $notes,
            ]);

            $movementTime = $producedAt ? Carbon::parse($producedAt) : now();

            foreach ($snapshot['lines'] as $line) {
                $requiredQuantity = (float) $line['required_quantity_g'];
                if ($requiredQuantity <= 0) {
                    continue;
                }

                $ingredient = $lockedIngredients->get($line['ingredient_id']);
                if (! $ingredient) {
                    continue;
                }

                $ingredient->stock_quantity_g = max((float) $ingredient->stock_quantity_g - $requiredQuantity, 0.0);
                $ingredient->save();

                ProductionBatchItem::query()->create([
                    'production_batch_id' => $batch->id,
                    'ingredient_id' => $ingredient->id,
                    'ingredient_name' => $line['ingredient_name'],
                    'quantity_used_g' => $requiredQuantity,
                    'unit_cost' => (float) $line['unit_cost'],
                    'line_cost' => (float) $line['line_cost'],
                ]);

                InventoryMovement::query()->create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => $user->id,
                    'type' => InventoryMovement::TYPE_PRODUCTION,
                    'quantity_g' => -1 * $requiredQuantity,
                    'unit_cost' => (float) $line['unit_cost'],
                    'total_cost' => (float) $line['line_cost'],
                    'moved_at' => $movementTime,
                    'notes' => 'Consumo automático da produção #'.$batch->id,
                    'reference_type' => ProductionBatch::class,
                    'reference_id' => $batch->id,
                ]);
            }

            return $batch;
        });
    }
}
