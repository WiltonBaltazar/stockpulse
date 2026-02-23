<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LandingSignupController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect('/admin');
        }

        $plans = Plan::query()
            ->where('is_active', true)
            ->with('features:id,code,name')
            ->orderBy('price')
            ->orderBy('id')
            ->get();

        $defaultPlan = $plans->firstWhere('code', Plan::CODE_BASIC) ?? $plans->first();

        return view('landing', [
            'plans' => $plans,
            'defaultPlanId' => $defaultPlan?->id,
        ]);
    }

    public function store(Request $request, SubscriptionService $subscriptionService): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => [
                'required',
                'integer',
                Rule::exists('plans', 'id')->where(static fn ($query) => $query->where('is_active', true)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'contact_number' => [
                'required',
                'string',
                'max:40',
                static function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! User::isValidMozambicanContact((string) $value)) {
                        $fail('O número de contacto deve ser válido para Moçambique (ex: +258841234567).');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $validated = $validator->validate();
        $plan = Plan::query()
            ->whereKey((int) $validated['plan_id'])
            ->where('is_active', true)
            ->first();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'contact_number' => User::normalizeMozambicanContact($validated['contact_number']),
            'password' => $validated['password'],
        ]);

        $user->assignRole('user');
        $subscriptionService->assignPlan($user, $plan);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/admin');
    }
}
