<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LandingSignupController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect('/admin');
        }

        return view('landing');
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
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

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'contact_number' => User::normalizeMozambicanContact($validated['contact_number']),
            'password' => $validated['password'],
        ]);

        $user->assignRole('user');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/admin');
    }
}
