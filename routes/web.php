<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LandingSignupController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingSignupController::class, 'show'])->name('landing');
Route::post('/signup', [LandingSignupController::class, 'store'])
    ->middleware('guest')
    ->name('landing.signup');

Route::prefix('documents')->name('documents.')->group(function (): void {
    Route::get('/sales/{sale}/receipt', [DocumentController::class, 'saleReceipt'])->name('sales.receipt');
    Route::get('/quotes/{quote}', [DocumentController::class, 'quotePdf'])->name('quotes.pdf');
    Route::get('/orders/{order}/slip', [DocumentController::class, 'orderSlip'])->name('orders.slip');
});
