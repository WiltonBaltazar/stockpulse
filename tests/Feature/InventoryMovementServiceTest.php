<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\User;
use App\Services\InventoryMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryMovementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_purchase_and_manual_output(): void
    {
        $user = User::factory()->create();
        $ingredient = Ingredient::query()->create([
            'user_id' => $user->id,
            'name' => 'Farinha',
            'package_quantity_g' => 1000,
            'package_cost' => 100,
            'stock_quantity_g' => 100,
            'reorder_level_g' => 10,
            'is_active' => true,
        ]);

        $service = app(InventoryMovementService::class);
        $service->record($ingredient, $user, InventoryMovement::TYPE_PURCHASE, 300, now(), 'Compra');
        $service->record($ingredient, $user, InventoryMovement::TYPE_MANUAL_OUT, -150, now(), 'Perda');

        $ingredient->refresh();

        $this->assertSame(250.0, $ingredient->stock_quantity_g);
        $this->assertDatabaseCount('inventory_movements', 2);
        $this->assertDatabaseHas('inventory_movements', [
            'ingredient_id' => $ingredient->id,
            'type' => InventoryMovement::TYPE_PURCHASE,
            'quantity_g' => 300.000,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'ingredient_id' => $ingredient->id,
            'type' => InventoryMovement::TYPE_MANUAL_OUT,
            'quantity_g' => -150.000,
        ]);
    }

    public function test_it_blocks_output_that_would_make_stock_negative(): void
    {
        $this->expectException(ValidationException::class);

        $user = User::factory()->create();
        $ingredient = Ingredient::query()->create([
            'user_id' => $user->id,
            'name' => 'Açúcar',
            'package_quantity_g' => 1000,
            'package_cost' => 90,
            'stock_quantity_g' => 50,
            'reorder_level_g' => 0,
            'is_active' => true,
        ]);

        app(InventoryMovementService::class)->record(
            ingredient: $ingredient,
            user: $user,
            type: InventoryMovement::TYPE_MANUAL_OUT,
            quantityG: -80,
            movedAt: now(),
            notes: 'Saída acima do estoque',
        );
    }
}
