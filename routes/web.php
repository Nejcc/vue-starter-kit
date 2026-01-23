<?php

declare(strict_types=1);

use App\Contracts\Repositories\SettingsRepositoryInterface;
use App\Http\Controllers\CookieConsentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    try {
        $settingsRepository = app(SettingsRepositoryInterface::class);
        $canRegister = (bool) $settingsRepository->get('registration_enabled', false);
    } catch (Exception $e) {
        $canRegister = false;
    }

    return Inertia::render('Welcome', [
        'canRegister' => $canRegister,
    ]);
})->name('home');

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

// Impersonation routes - only accessible to super-admin or admin roles
Route::middleware(['auth'])->prefix('impersonate')->name('impersonate.')->group(function (): void {
    Route::get('/', [App\Http\Controllers\ImpersonateController::class, 'index'])->middleware('role:super-admin,admin')->name('index');
    Route::post('/', [App\Http\Controllers\ImpersonateController::class, 'store'])->middleware('role:super-admin,admin')->name('store');
    // Stop impersonation doesn't require admin role (anyone being impersonated should be able to stop)
    Route::delete('/', [App\Http\Controllers\ImpersonateController::class, 'destroy'])->name('destroy');
});

require __DIR__.'/settings.php';

// Admin routes - only accessible to super-admin or admin roles
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('index');
    Route::resource('settings', App\Http\Controllers\Admin\SettingsController::class)->except(['show']);
    Route::patch('settings/bulk', [App\Http\Controllers\Admin\SettingsController::class, 'bulkUpdate'])->name('settings.bulk-update');
    Route::get('users', [App\Http\Controllers\Admin\UsersController::class, 'index'])->name('users.index');
    Route::get('users/create', [App\Http\Controllers\Admin\UsersController::class, 'create'])->name('users.create');
    Route::post('users', [App\Http\Controllers\Admin\UsersController::class, 'store'])->name('users.store');
    Route::get('roles', [App\Http\Controllers\Admin\RolesController::class, 'index'])->name('roles.index');
    Route::get('roles/create', [App\Http\Controllers\Admin\RolesController::class, 'create'])->name('roles.create');
    Route::post('roles', [App\Http\Controllers\Admin\RolesController::class, 'store'])->name('roles.store');
    Route::get('roles/{role}/edit', [App\Http\Controllers\Admin\RolesController::class, 'edit'])->name('roles.edit');
    Route::patch('roles/{role}', [App\Http\Controllers\Admin\RolesController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [App\Http\Controllers\Admin\RolesController::class, 'destroy'])->name('roles.destroy');
    Route::get('permissions', [App\Http\Controllers\Admin\PermissionsController::class, 'index'])->name('permissions.index');
    Route::get('permissions/create', [App\Http\Controllers\Admin\PermissionsController::class, 'create'])->name('permissions.create');
    Route::post('permissions', [App\Http\Controllers\Admin\PermissionsController::class, 'store'])->name('permissions.store');
    Route::get('permissions/{permission}/edit', [App\Http\Controllers\Admin\PermissionsController::class, 'edit'])->name('permissions.edit');
    Route::patch('permissions/{permission}', [App\Http\Controllers\Admin\PermissionsController::class, 'update'])->name('permissions.update');
    Route::get('database', [App\Http\Controllers\Admin\DatabaseController::class, 'index'])->name('database.index');
    Route::get('database/{connection}', [App\Http\Controllers\Admin\DatabaseController::class, 'index'])->name('database.connection.index');
    Route::get('database/{connection}/{table}', [App\Http\Controllers\Admin\DatabaseController::class, 'show'])->name('database.connection.show');
    Route::get('database/{connection}/{table}/{view}', [App\Http\Controllers\Admin\DatabaseController::class, 'show'])->name('database.connection.show.view');
    Route::get('databases', [App\Http\Controllers\Admin\DatabaseController::class, 'listConnections'])->name('databases.index');
});
