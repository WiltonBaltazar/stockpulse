<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\User;
use App\Services\InventoryMovementService;
use Illuminate\Database\Seeder;

class InventoryMovementSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'admin@bakeryapp.test')
            ->first();

        if (! $user) {
            return;
        }

        InventoryMovement::query()
            ->where('notes', 'like', '[seed]%')
            ->whereNull('reference_type')
            ->delete();

        $movements = [
            [
                'ingredient' => 'Farinha de trigo',
                'type' => InventoryMovement::TYPE_PURCHASE,
                'quantity' => 3500,
                'total_cost' => 280,
                'days_ago' => 18,
                'notes' => '[seed] Reposição semanal de farinha.',
            ],
            [
                'ingredient' => 'Açúcar refinado',
                'type' => InventoryMovement::TYPE_PURCHASE,
                'quantity' => 2500,
                'total_cost' => 245,
                'days_ago' => 16,
                'notes' => '[seed] Compra para produção de bolos.',
            ],
            [
                'ingredient' => 'Leite (1L)',
                'type' => InventoryMovement::TYPE_PURCHASE,
                'quantity' => 2400,
                'total_cost' => 232,
                'days_ago' => 15,
                'notes' => '[seed] Reposição de leite.',
            ],
            [
                'ingredient' => 'Ovos (em unidades)',
                'type' => InventoryMovement::TYPE_PURCHASE,
                'quantity' => 45,
                'total_cost' => 595,
                'days_ago' => 14,
                'notes' => '[seed] Compra de cartelas de ovos.',
            ],
            [
                'ingredient' => 'Cacau em pó',
                'type' => InventoryMovement::TYPE_PURCHASE,
                'quantity' => 500,
                'total_cost' => 250,
                'days_ago' => 12,
                'notes' => '[seed] Compra de cacau para receitas premium.',
            ],
            [
                'ingredient' => 'Coco ralado',
                'type' => InventoryMovement::TYPE_PURCHASE,
                'quantity' => 300,
                'total_cost' => 295,
                'days_ago' => 11,
                'notes' => '[seed] Compra de coco para produção semanal.',
            ],
            [
                'ingredient' => 'Frango desfiado',
                'type' => InventoryMovement::TYPE_PURCHASE,
                'quantity' => 3000,
                'total_cost' => 960,
                'days_ago' => 10,
                'notes' => '[seed] Compra de frango para salgados.',
            ],
            [
                'ingredient' => 'Carne moída',
                'type' => InventoryMovement::TYPE_PURCHASE,
                'quantity' => 2200,
                'total_cost' => 792,
                'days_ago' => 9,
                'notes' => '[seed] Compra de carne para rissóis.',
            ],
            [
                'ingredient' => 'Queijo muçarela',
                'type' => InventoryMovement::TYPE_PURCHASE,
                'quantity' => 1800,
                'total_cost' => 738,
                'days_ago' => 8,
                'notes' => '[seed] Compra de queijo para pastéis.',
            ],
            [
                'ingredient' => 'Massa folhada',
                'type' => InventoryMovement::TYPE_PURCHASE,
                'quantity' => 2200,
                'total_cost' => 484,
                'days_ago' => 8,
                'notes' => '[seed] Reposição de massa para pastelaria salgada.',
            ],
            [
                'ingredient' => 'Farinha de trigo',
                'type' => InventoryMovement::TYPE_MANUAL_OUT,
                'quantity' => -200,
                'total_cost' => null,
                'days_ago' => 9,
                'notes' => '[seed] Perda por derrame no armazenamento.',
            ],
            [
                'ingredient' => 'Margarina',
                'type' => InventoryMovement::TYPE_MANUAL_OUT,
                'quantity' => -150,
                'total_cost' => null,
                'days_ago' => 7,
                'notes' => '[seed] Quebra de cadeia de frio.',
            ],
            [
                'ingredient' => 'Frango desfiado',
                'type' => InventoryMovement::TYPE_MANUAL_OUT,
                'quantity' => -220,
                'total_cost' => null,
                'days_ago' => 6,
                'notes' => '[seed] Perda de recheio por falha de refrigeração.',
            ],
            [
                'ingredient' => 'Coco ralado',
                'type' => InventoryMovement::TYPE_ADJUSTMENT,
                'quantity' => 60,
                'total_cost' => null,
                'days_ago' => 5,
                'notes' => '[seed] Ajuste positivo após contagem física.',
            ],
            [
                'ingredient' => 'Fermento em pó',
                'type' => InventoryMovement::TYPE_ADJUSTMENT,
                'quantity' => -12,
                'total_cost' => null,
                'days_ago' => 4,
                'notes' => '[seed] Ajuste por validade expirada.',
            ],
            [
                'ingredient' => 'Massa folhada',
                'type' => InventoryMovement::TYPE_ADJUSTMENT,
                'quantity' => 120,
                'total_cost' => null,
                'days_ago' => 3,
                'notes' => '[seed] Ajuste positivo após conferência de estoque.',
            ],
        ];

        $movementService = app(InventoryMovementService::class);

        $ingredientsByName = Ingredient::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('name');

        foreach ($movements as $movement) {
            $ingredient = $ingredientsByName->get($movement['ingredient']);
            if (! $ingredient) {
                continue;
            }

            $movementService->record(
                ingredient: $ingredient,
                user: $user,
                type: $movement['type'],
                quantityG: (float) $movement['quantity'],
                movedAt: now()->subDays((int) $movement['days_ago']),
                notes: $movement['notes'].' ['.$user->email.']',
                totalCost: $movement['total_cost'] === null ? null : (float) $movement['total_cost'],
            );
        }
    }
}
