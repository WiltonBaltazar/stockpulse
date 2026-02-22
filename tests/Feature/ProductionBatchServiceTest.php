<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\User;
use App\Services\ProductionBatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductionBatchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_batch_calculates_cogs_and_deducts_stock(): void
    {
        $user = User::factory()->create();

        $ingredient = Ingredient::query()->create([
            'user_id' => $user->id,
            'name' => 'Farinha',
            'package_quantity_g' => 1000,
            'package_cost' => 100,
            'stock_quantity_g' => 1000,
            'reorder_level_g' => 100,
            'is_active' => true,
        ]);

        $recipe = Recipe::query()->create([
            'user_id' => $user->id,
            'name' => 'Pão',
            'priced_at' => now()->toDateString(),
            'yield_units' => 10,
            'packaging_cost_per_unit' => 1,
            'overhead_percent' => 25,
            'markup_multiplier' => 3,
        ]);

        RecipeItem::query()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used_g' => 200,
        ]);

        $batch = app(ProductionBatchService::class)->createBatch(
            user: $user,
            recipeId: $recipe->id,
            producedUnits: 10,
            producedAt: now(),
            notes: 'Lote de teste',
        );

        $ingredient->refresh();

        $this->assertEqualsWithDelta(800.0, $ingredient->stock_quantity_g, 0.0001);
        $this->assertEqualsWithDelta(20.0, $batch->ingredients_cost, 0.0001);
        $this->assertEqualsWithDelta(10.0, $batch->packaging_cost, 0.0001);
        $this->assertEqualsWithDelta(5.0, $batch->overhead_cost, 0.0001);
        $this->assertEqualsWithDelta(35.0, $batch->total_cogs, 0.0001);
        $this->assertEqualsWithDelta(3.5, $batch->cogs_per_unit, 0.0001);

        $this->assertDatabaseHas('inventory_movements', [
            'ingredient_id' => $ingredient->id,
            'type' => InventoryMovement::TYPE_PRODUCTION,
            'quantity_g' => -200.000,
            'reference_type' => \App\Models\ProductionBatch::class,
            'reference_id' => $batch->id,
        ]);

        $this->assertDatabaseHas('production_batch_items', [
            'production_batch_id' => $batch->id,
            'ingredient_id' => $ingredient->id,
            'ingredient_name' => 'Farinha',
            'quantity_used_g' => 200.000,
            'line_cost' => 20.00,
        ]);
    }

    public function test_it_rejects_batch_when_stock_is_insufficient(): void
    {
        $this->expectException(ValidationException::class);

        $user = User::factory()->create();

        $ingredient = Ingredient::query()->create([
            'user_id' => $user->id,
            'name' => 'Farinha',
            'package_quantity_g' => 1000,
            'package_cost' => 100,
            'stock_quantity_g' => 50,
            'reorder_level_g' => 10,
            'is_active' => true,
        ]);

        $recipe = Recipe::query()->create([
            'user_id' => $user->id,
            'name' => 'Bolo',
            'priced_at' => now()->toDateString(),
            'yield_units' => 5,
            'packaging_cost_per_unit' => 0,
            'overhead_percent' => 10,
            'markup_multiplier' => 2,
        ]);

        RecipeItem::query()->create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used_g' => 100,
        ]);

        app(ProductionBatchService::class)->createBatch(
            user: $user,
            recipeId: $recipe->id,
            producedUnits: 5,
            producedAt: now(),
        );
    }
}
