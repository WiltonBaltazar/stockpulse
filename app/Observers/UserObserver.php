<?php

namespace App\Observers;

use App\Models\User;
use App\Services\SubscriptionService;

class UserObserver
{
    public function created(User $user): void
    {
        app(SubscriptionService::class)->assignDefaultPlan($user);
    }
}
