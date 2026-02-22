<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use App\Models\ProductionBatch;
use App\Models\ProductionBatchItem;
use App\Models\Recipe;
use App\Models\User;
use App\Services\ProductionBatchService;
use Illuminate\Database\Seeder;

class ProductionBatchSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'admin@bakeryapp.test')
            ->first();

        if (! $user) {
            return;
        }

        $seedBatchIds = ProductionBatch::query()
            ->where('notes', 'like', '[seed]%')
            ->pluck('id');

        if ($seedBatchIds->isNotEmpty()) {
            InventoryMovement::query()
                ->where('reference_type', ProductionBatch::class)
                ->whereIn('reference_id', $seedBatchIds)
                ->delete();

            ProductionBatchItem::query()
                ->whereIn('production_batch_id', $seedBatchIds)
                ->delete();

            ProductionBatch::query()
                ->whereIn('id', $seedBatchIds)
                ->delete();
        }

        $plans = [
            ['recipe' => 'Pão doce clássico', 'units' => 48, 'days_ago' => 11],
            ['recipe' => 'Bolo de chocolate caseiro', 'units' => 18, 'days_ago' => 8],
            ['recipe' => 'Biscoito de coco', 'units' => 70, 'days_ago' => 6],
            ['recipe' => 'Queijadinha de coco', 'units' => 24, 'days_ago' => 3],
            ['recipe' => 'Coxinha de frango', 'units' => 78, 'days_ago' => 7],
            ['recipe' => 'Empada de frango', 'units' => 36, 'days_ago' => 5],
            ['recipe' => 'Rissol de carne', 'units' => 62, 'days_ago' => 4],
            ['recipe' => 'Pastel de queijo', 'units' => 58, 'days_ago' => 2],
        ];

        $productionBatchService = app(ProductionBatchService::class);

        $recipesByName = Recipe::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('name');

        foreach ($plans as $plan) {
            $recipe = $recipesByName->get($plan['recipe']);
            if (! $recipe) {
                continue;
            }

            $productionBatchService->createBatch(
                user: $user,
                recipeId: $recipe->id,
                producedUnits: max((float) $plan['units'], 1.0),
                producedAt: now()->subDays((int) $plan['days_ago']),
                notes: '[seed] Produção demo de '.$plan['recipe'].' ('.$user->email.').',
            );
        }
    }
}
