<?php

use App\Http\Controllers\CookieConsentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Cookie Consent Routes
Route::prefix('cookie-consent')->group(function () {
    Route::get('/', [CookieConsentController::class, 'getPreferences'])->name('cookie-consent.get');
    Route::post('/', [CookieConsentController::class, 'updatePreferences'])->name('cookie-consent.update');
    Route::post('/accept-all', [CookieConsentController::class, 'acceptAll'])->name('cookie-consent.accept-all');
    Route::post('/reject-all', [CookieConsentController::class, 'rejectAll'])->name('cookie-consent.reject-all');
});

require __DIR__.'/settings.php';
