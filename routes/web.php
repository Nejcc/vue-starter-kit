<?php

declare(strict_types=1);

use App\Http\Controllers\CookieConsentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
]))->name('home');

Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

// Quick login for development (user 1) - only available in local environment
if (app()->environment('local')) {
    Route::post('quick-login', function () {
        $user = App\Models\User::find(1);

        if (!$user) {
            return back()->withErrors([
                'email' => 'User 1 does not exist.',
            ]);
        }

        Illuminate\Support\Facades\Auth::login($user);
        \request()->session()->regenerate();

        return redirect()->intended('dashboard');
    })->name('quick-login');
}

// Cookie Consent Routes
Route::prefix('cookie-consent')->group(function (): void {
    Route::get('/', [CookieConsentController::class, 'getPreferences'])->name('cookie-consent.get');
    Route::post('/', [CookieConsentController::class, 'updatePreferences'])->name('cookie-consent.update');
    Route::post('/accept-all', [CookieConsentController::class, 'acceptAll'])->name('cookie-consent.accept-all');
    Route::post('/reject-all', [CookieConsentController::class, 'rejectAll'])->name('cookie-consent.reject-all');
});

// Privacy and Cookie Policy Pages
Route::get('/privacy-policy', fn () => Inertia::render('PrivacyPolicy'))->name('privacy-policy');

Route::get('/cookie-policy', fn () => Inertia::render('CookiePolicy'))->name('cookie-policy');

// About Page
Route::get('/about', [App\Http\Controllers\AboutController::class, 'index'])->name('about');

// Impersonation routes
Route::middleware(['auth'])->prefix('impersonate')->name('impersonate.')->group(function (): void {
    Route::get('/', [App\Http\Controllers\ImpersonateController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\ImpersonateController::class, 'store'])->name('store');
    Route::delete('/', [App\Http\Controllers\ImpersonateController::class, 'destroy'])->name('destroy');
});

require __DIR__.'/settings.php';
