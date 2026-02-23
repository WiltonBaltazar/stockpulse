<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionService
{
    public function assignDefaultPlan(User $user): ?Subscription
    {
        return $this->assignPlan($user, $this->resolveDefaultPlan());
    }

    public function assignPlan(User $user, ?Plan $plan): ?Subscription
    {
        if (! $plan || ! $plan->is_active) {
            return null;
        }

        $activeSubscription = Subscription::query()
            ->where('user_id', $user->id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->latest('started_at')
            ->first();

        if ($activeSubscription && (int) $activeSubscription->plan_id === (int) $plan->id) {
            if ($activeSubscription->ends_at === null) {
                $durationMonths = max((int) ($plan->duration_months ?? 1), 1);
                $activeSubscription->ends_at = $activeSubscription->started_at
                    ? $activeSubscription->started_at->copy()->addMonthsNoOverflow($durationMonths)
                    : now()->addMonthsNoOverflow($durationMonths);
                $activeSubscription->save();
            }

            return $activeSubscription;
        }

        if ($activeSubscription) {
            $activeSubscription->forceFill([
                'status' => Subscription::STATUS_CANCELLED,
                'ends_at' => now(),
            ])->save();
        }

        $durationMonths = max((int) ($plan->duration_months ?? 1), 1);

        return Subscription::query()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'started_at' => now(),
            'ends_at' => now()->copy()->addMonthsNoOverflow($durationMonths),
            'price' => (float) $plan->price,
            'currency' => (string) $plan->currency,
        ]);
    }

    public function resolveDefaultPlan(): ?Plan
    {
        $basicPlan = Plan::query()
            ->where('code', Plan::CODE_BASIC)
            ->where('is_active', true)
            ->first();

        if ($basicPlan) {
            return $basicPlan;
        }

        return Plan::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }
}
