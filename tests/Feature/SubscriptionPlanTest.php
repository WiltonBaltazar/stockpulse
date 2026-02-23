<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_receives_active_basic_subscription(): void
    {
        $user = User::factory()->create();

        $subscription = $user->activeSubscription()->with('plan.features')->first();

        $this->assertNotNull($subscription);
        $this->assertSame('basic', $subscription->plan?->code);
        $this->assertNotNull($subscription->ends_at);
        $this->assertTrue($subscription->ends_at->greaterThan($subscription->started_at));
        $this->assertTrue($user->hasFeature(Feature::SALES));
        $this->assertTrue($user->hasFeature(Feature::FINANCES));
    }

    public function test_user_loses_access_when_feature_is_removed_from_plan(): void
    {
        $user = User::factory()->create();
        $subscription = $user->activeSubscription()->with('plan.features')->first();

        $this->assertNotNull($subscription);
        $plan = $subscription->plan;
        $this->assertNotNull($plan);

        $financeFeature = Feature::query()->where('code', Feature::FINANCES)->first();
        $this->assertNotNull($financeFeature);

        $plan->features()->detach($financeFeature->id);

        $this->assertFalse($user->fresh()->hasFeature(Feature::FINANCES));
    }

    public function test_expired_subscription_is_not_considered_active(): void
    {
        $user = User::factory()->create();
        $subscription = $user->activeSubscription()->first();

        $this->assertNotNull($subscription);

        $subscription->forceFill([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => now()->subMinute(),
        ])->save();

        $this->assertNull($user->fresh()->activeSubscription()->first());
    }
}
