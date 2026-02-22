<?php

use App\Http\Controllers\LandingSignupController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingSignupController::class, 'show'])->name('landing');
Route::post('/signup', [LandingSignupController::class, 'store'])
    ->middleware('guest')
    ->name('landing.signup');
