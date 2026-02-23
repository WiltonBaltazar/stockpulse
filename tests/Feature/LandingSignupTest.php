<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LandingSignupTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_lists_active_plans_from_backend(): void
    {
        Plan::query()->create([
            'code' => 'inactive-plan',
            'name' => 'Inactive',
            'description' => 'Nao deve aparecer.',
            'price' => 99,
            'currency' => 'MT',
            'duration_months' => 1,
            'is_active' => false,
        ]);

        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSee('Planos StockPulse');
        $response->assertSee('Basic');
        $response->assertSee('Receitas com custo e preco automatico');
        $response->assertDontSee('Inactive');
    }

    public function test_signup_uses_selected_plan_for_subscription(): void
    {
        Role::query()->firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        $selectedPlan = Plan::query()->create([
            'code' => 'pro-monthly',
            'name' => 'Pro Monthly',
            'description' => 'Plano de teste.',
            'price' => 1490,
            'currency' => 'MT',
            'duration_months' => 1,
            'is_active' => true,
        ]);

        $response = $this->post(route('landing.signup'), [
            'plan_id' => $selectedPlan->id,
            'name' => 'Cliente Teste',
            'email' => 'cliente.teste@example.com',
            'contact_number' => '+258 84 123 4567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/admin');

        $user = User::query()->where('email', 'cliente.teste@example.com')->first();

        $this->assertNotNull($user);
        $activeSubscription = $user->activeSubscription()->first();

        $this->assertNotNull($activeSubscription);
        $this->assertSame($selectedPlan->id, $activeSubscription->plan_id);
        $this->assertSame(Subscription::STATUS_ACTIVE, $activeSubscription->status);
    }
}
