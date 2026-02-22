<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'admin@bakeryapp.test')
            ->first();

        if (! $user) {
            return;
        }

        $ingredients = [
            ['name' => 'Farinha de trigo', 'package_quantity_g' => 1000, 'package_cost' => 78.00],
            ['name' => 'Farinha de milho', 'package_quantity_g' => 1000, 'package_cost' => 72.00],
            ['name' => 'Açúcar refinado', 'package_quantity_g' => 1000, 'package_cost' => 98.00],
            ['name' => 'Açúcar castanho', 'package_quantity_g' => 1000, 'package_cost' => 112.00],
            ['name' => 'Sal fino', 'package_quantity_g' => 1000, 'package_cost' => 45.00],
            ['name' => 'Fermento em pó', 'package_quantity_g' => 100, 'package_cost' => 62.00],
            ['name' => 'Bicarbonato de sódio', 'package_quantity_g' => 100, 'package_cost' => 78.00],
            ['name' => 'Cacau em pó', 'package_quantity_g' => 200, 'package_cost' => 105.00],
            ['name' => 'Chocolate em pó', 'package_quantity_g' => 500, 'package_cost' => 182.00],
            ['name' => 'Leite em pó', 'package_quantity_g' => 400, 'package_cost' => 345.00],
            ['name' => 'Leite condensado', 'package_quantity_g' => 397, 'package_cost' => 168.00],
            ['name' => 'Creme de leite', 'package_quantity_g' => 200, 'package_cost' => 138.00],
            ['name' => 'Leite de coco', 'package_quantity_g' => 400, 'package_cost' => 176.00],
            ['name' => 'Leite (1L)', 'package_quantity_g' => 1000, 'package_cost' => 99.00],
            ['name' => 'Óleo vegetal', 'package_quantity_g' => 900, 'package_cost' => 188.00],
            ['name' => 'Margarina', 'package_quantity_g' => 500, 'package_cost' => 96.00],
            ['name' => 'Manteiga', 'package_quantity_g' => 250, 'package_cost' => 185.00],
            ['name' => 'Ovos (em unidades)', 'package_quantity_g' => 30, 'package_cost' => 398.00],
            ['name' => 'Baunilha (essência)', 'package_quantity_g' => 30, 'package_cost' => 118.00],
            ['name' => 'Canela em pó', 'package_quantity_g' => 50, 'package_cost' => 74.00],
            ['name' => 'Coco ralado', 'package_quantity_g' => 100, 'package_cost' => 98.00],
            ['name' => 'Amendoim', 'package_quantity_g' => 500, 'package_cost' => 176.00],
            ['name' => 'Gergelim', 'package_quantity_g' => 100, 'package_cost' => 122.00],
            ['name' => 'Castanha de caju (partida)', 'package_quantity_g' => 250, 'package_cost' => 405.00],
            ['name' => 'Limão', 'package_quantity_g' => 1000, 'package_cost' => 96.00],
            ['name' => 'Laranja', 'package_quantity_g' => 1000, 'package_cost' => 101.00],
            ['name' => 'Banana', 'package_quantity_g' => 1000, 'package_cost' => 50.00],
            ['name' => 'Ananás', 'package_quantity_g' => 1000, 'package_cost' => 132.00],
            ['name' => 'Frango desfiado', 'package_quantity_g' => 1000, 'package_cost' => 320.00],
            ['name' => 'Carne moída', 'package_quantity_g' => 1000, 'package_cost' => 360.00],
            ['name' => 'Queijo muçarela', 'package_quantity_g' => 1000, 'package_cost' => 410.00],
            ['name' => 'Batata', 'package_quantity_g' => 1000, 'package_cost' => 65.00],
            ['name' => 'Cebola', 'package_quantity_g' => 1000, 'package_cost' => 70.00],
            ['name' => 'Alho', 'package_quantity_g' => 500, 'package_cost' => 95.00],
            ['name' => 'Tomate', 'package_quantity_g' => 1000, 'package_cost' => 92.00],
            ['name' => 'Pimentão', 'package_quantity_g' => 1000, 'package_cost' => 110.00],
            ['name' => 'Polpa de tomate', 'package_quantity_g' => 340, 'package_cost' => 56.00],
            ['name' => 'Milho verde', 'package_quantity_g' => 280, 'package_cost' => 62.00],
            ['name' => 'Pão ralado', 'package_quantity_g' => 500, 'package_cost' => 88.00],
            ['name' => 'Massa folhada', 'package_quantity_g' => 1000, 'package_cost' => 220.00],
            ['name' => 'Pimenta preta', 'package_quantity_g' => 100, 'package_cost' => 68.00],
            ['name' => 'Orégano', 'package_quantity_g' => 100, 'package_cost' => 45.00],
            ['name' => 'Embalagem (caixa)', 'package_quantity_g' => 1, 'package_cost' => 15.00],
            ['name' => 'Embalagem (saquinho)', 'package_quantity_g' => 1, 'package_cost' => 3.00],
            ['name' => '---', 'package_quantity_g' => 1, 'package_cost' => 0.01],
        ];

        $stockMultiplier = 2.5;

        foreach ($ingredients as $ingredient) {
            Ingredient::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $ingredient['name'],
                ],
                $ingredient + [
                    'user_id' => $user->id,
                    'stock_quantity_g' => (float) $ingredient['package_quantity_g'] * $stockMultiplier,
                    'reorder_level_g' => (float) $ingredient['package_quantity_g'] * 0.5,
                    'is_active' => true,
                ]
            );
        }
    }
}
