<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Feature::catalog() as $code => $meta) {
            Feature::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $meta['name'],
                    'description' => $meta['description'],
                ]
            );
        }

        $basicPlan = Plan::query()->updateOrCreate(
            ['code' => Plan::CODE_BASIC],
            [
                'name' => 'Basic',
                'description' => 'Plano base gratuito.',
                'price' => 0,
                'currency' => 'MT',
                'duration_months' => 1,
                'is_active' => true,
            ]
        );

        $featureIds = Feature::query()
            ->whereIn('code', array_keys(Feature::catalog()))
            ->pluck('id')
            ->all();

        $basicPlan->features()->sync($featureIds);

        $users = User::query()->get();
        foreach ($users as $user) {
            $activeSubscription = Subscription::query()
                ->where('user_id', $user->id)
                ->where('status', Subscription::STATUS_ACTIVE)
                ->latest('started_at')
                ->first();

            if ($activeSubscription && $activeSubscription->ends_at !== null) {
                continue;
            }

            $startedAt = $activeSubscription?->started_at ?: now();
            $endsAt = $startedAt->copy()->addMonthsNoOverflow(max((int) ($basicPlan->duration_months ?? 1), 1));

            if ($activeSubscription) {
                $activeSubscription->forceFill([
                    'plan_id' => $basicPlan->id,
                    'price' => (float) $basicPlan->price,
                    'currency' => (string) $basicPlan->currency,
                    'ends_at' => $endsAt,
                ])->save();
            } else {
                Subscription::query()->create([
                    'user_id' => $user->id,
                    'plan_id' => $basicPlan->id,
                    'status' => Subscription::STATUS_ACTIVE,
                    'started_at' => $startedAt,
                    'ends_at' => $endsAt,
                    'price' => (float) $basicPlan->price,
                    'currency' => $basicPlan->currency,
                ]);
            }
        }
    }
}
