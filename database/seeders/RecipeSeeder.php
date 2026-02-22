<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'admin@bakeryapp.test')
            ->first();

        if (! $user) {
            return;
        }

        $recipes = [
            [
                'name' => 'Pão doce clássico',
                'priced_at' => now()->subDays(20)->toDateString(),
                'yield_units' => 40,
                'packaging_cost_per_unit' => 4.00,
                'overhead_percent' => 18,
                'markup_multiplier' => 2.4,
                'items' => [
                    'Farinha de trigo' => 1000,
                    'Açúcar refinado' => 180,
                    'Sal fino' => 20,
                    'Fermento em pó' => 25,
                    'Leite (1L)' => 450,
                    'Margarina' => 120,
                    'Ovos (em unidades)' => 10,
                ],
            ],
            [
                'name' => 'Bolo de chocolate caseiro',
                'priced_at' => now()->subDays(15)->toDateString(),
                'yield_units' => 18,
                'packaging_cost_per_unit' => 12.00,
                'overhead_percent' => 22,
                'markup_multiplier' => 2.8,
                'items' => [
                    'Farinha de trigo' => 650,
                    'Açúcar refinado' => 450,
                    'Cacau em pó' => 180,
                    'Leite (1L)' => 350,
                    'Óleo vegetal' => 220,
                    'Fermento em pó' => 20,
                    'Ovos (em unidades)' => 12,
                    'Baunilha (essência)' => 6,
                ],
            ],
            [
                'name' => 'Biscoito de coco',
                'priced_at' => now()->subDays(12)->toDateString(),
                'yield_units' => 60,
                'packaging_cost_per_unit' => 2.50,
                'overhead_percent' => 20,
                'markup_multiplier' => 2.7,
                'items' => [
                    'Farinha de trigo' => 500,
                    'Açúcar castanho' => 250,
                    'Coco ralado' => 150,
                    'Margarina' => 180,
                    'Fermento em pó' => 8,
                    'Ovos (em unidades)' => 6,
                    'Baunilha (essência)' => 4,
                ],
            ],
            [
                'name' => 'Queijadinha de coco',
                'priced_at' => now()->subDays(9)->toDateString(),
                'yield_units' => 25,
                'packaging_cost_per_unit' => 3.00,
                'overhead_percent' => 25,
                'markup_multiplier' => 2.9,
                'items' => [
                    'Leite condensado' => 397,
                    'Coco ralado' => 120,
                    'Açúcar refinado' => 80,
                    'Margarina' => 60,
                    'Ovos (em unidades)' => 8,
                    'Baunilha (essência)' => 3,
                ],
            ],
            [
                'name' => 'Coxinha de frango',
                'priced_at' => now()->subDays(8)->toDateString(),
                'yield_units' => 80,
                'packaging_cost_per_unit' => 1.20,
                'overhead_percent' => 20,
                'markup_multiplier' => 2.5,
                'items' => [
                    'Farinha de trigo' => 950,
                    'Frango desfiado' => 1200,
                    'Batata' => 500,
                    'Cebola' => 180,
                    'Alho' => 35,
                    'Leite (1L)' => 300,
                    'Óleo vegetal' => 250,
                    'Pão ralado' => 200,
                    'Sal fino' => 24,
                    'Pimenta preta' => 8,
                ],
            ],
            [
                'name' => 'Empada de frango',
                'priced_at' => now()->subDays(6)->toDateString(),
                'yield_units' => 40,
                'packaging_cost_per_unit' => 2.50,
                'overhead_percent' => 22,
                'markup_multiplier' => 2.7,
                'items' => [
                    'Farinha de trigo' => 800,
                    'Manteiga' => 320,
                    'Frango desfiado' => 900,
                    'Cebola' => 120,
                    'Alho' => 20,
                    'Polpa de tomate' => 200,
                    'Creme de leite' => 120,
                    'Milho verde' => 120,
                    'Sal fino' => 14,
                    'Pimenta preta' => 4,
                ],
            ],
            [
                'name' => 'Rissol de carne',
                'priced_at' => now()->subDays(5)->toDateString(),
                'yield_units' => 70,
                'packaging_cost_per_unit' => 1.50,
                'overhead_percent' => 21,
                'markup_multiplier' => 2.6,
                'items' => [
                    'Farinha de trigo' => 700,
                    'Carne moída' => 1100,
                    'Cebola' => 150,
                    'Alho' => 24,
                    'Polpa de tomate' => 160,
                    'Óleo vegetal' => 220,
                    'Pão ralado' => 180,
                    'Sal fino' => 20,
                    'Pimenta preta' => 6,
                ],
            ],
            [
                'name' => 'Pastel de queijo',
                'priced_at' => now()->subDays(4)->toDateString(),
                'yield_units' => 60,
                'packaging_cost_per_unit' => 1.40,
                'overhead_percent' => 20,
                'markup_multiplier' => 2.55,
                'items' => [
                    'Massa folhada' => 1300,
                    'Queijo muçarela' => 850,
                    'Orégano' => 20,
                    'Óleo vegetal' => 320,
                    'Sal fino' => 8,
                ],
            ],
        ];

        $ingredientsByName = Ingredient::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('name');

        foreach ($recipes as $data) {
            $recipe = Recipe::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $data['name'],
                ],
                [
                    'user_id' => $user->id,
                    'name' => $data['name'],
                    'is_active' => true,
                    'priced_at' => $data['priced_at'],
                    'yield_units' => $data['yield_units'],
                    'packaging_cost_per_unit' => $data['packaging_cost_per_unit'],
                    'overhead_percent' => $data['overhead_percent'],
                    'markup_multiplier' => $data['markup_multiplier'],
                ]
            );

            $ingredientIds = [];

            foreach ($data['items'] as $ingredientName => $quantityUsed) {
                $ingredient = $ingredientsByName->get($ingredientName);

                if (! $ingredient) {
                    continue;
                }

                $ingredientIds[] = $ingredient->id;

                $recipe->items()->updateOrCreate(
                    ['ingredient_id' => $ingredient->id],
                    ['quantity_used_g' => $quantityUsed]
                );
            }

            if ($ingredientIds !== []) {
                $recipe->items()
                    ->whereNotIn('ingredient_id', $ingredientIds)
                    ->delete();
            }
        }
    }
}
