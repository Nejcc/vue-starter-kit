<?php

declare(strict_types=1);

use App\Http\Controllers\CookieConsentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use LaravelPlus\GlobalSettings\Contracts\SettingsRepositoryInterface;

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

Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Quick login & register for development - only when APP_ENV=local
if (app()->environment('local')) {
    Route::post('quick-login/{role}', function (string $role) {
        $allowedRoles = ['super-admin', 'admin', 'user'];

        if (!in_array($role, $allowedRoles, true)) {
            return back()->withErrors([
                'role' => "Invalid role: {$role}.",
            ]);
        }

        $user = App\Models\User::role($role)->first();

        if (!$user) {
            $user = App\Models\User::factory()->create([
                'name' => ucfirst(str_replace('-', ' ', $role)),
                'email' => $role . '@example.com',
                'email_verified_at' => now(),
            ]);
            $user->assignRole($role);
        }

        Illuminate\Support\Facades\Auth::login($user);
        \request()->session()->regenerate();

        return redirect()->intended('dashboard');
    })->name('quick-login');

    Route::post('quick-register/{role}', function (string $role) {
        $allowedRoles = ['super-admin', 'admin', 'user'];

        if (!in_array($role, $allowedRoles, true)) {
            return back()->withErrors([
                'role' => "Invalid role: {$role}.",
            ]);
        }

        $user = App\Models\User::factory()->create([
            'name' => ucfirst($role) . ' ' . fake()->firstName(),
            'email' => $role . '-' . fake()->unique()->numerify('###') . '@example.com',
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        Illuminate\Support\Facades\Auth::login($user);
        \request()->session()->regenerate();

        return redirect()->intended('dashboard');
    })->name('quick-register');
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
    Route::post('/', [App\Http\Controllers\ImpersonateController::class, 'store'])->middleware(['role:super-admin,admin', 'throttle:impersonate'])->name('store');
    // Stop impersonation doesn't require admin role (anyone being impersonated should be able to stop)
    Route::delete('/', [App\Http\Controllers\ImpersonateController::class, 'destroy'])->name('destroy');
});

// Notification routes - accessible to all authenticated users
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function (): void {
    Route::get('/', [App\Http\Controllers\NotificationsController::class, 'index'])->name('index');
    Route::get('/recent', [App\Http\Controllers\NotificationsController::class, 'recent'])->name('recent');
    Route::patch('/{id}/read', [App\Http\Controllers\NotificationsController::class, 'markAsRead'])->name('mark-as-read');
    Route::post('/mark-all-read', [App\Http\Controllers\NotificationsController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{id}', [App\Http\Controllers\NotificationsController::class, 'destroy'])->name('destroy');
});

require __DIR__.'/settings.php';

// Admin routes - only accessible to super-admin or admin roles
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('index');
    Route::get('users', [App\Http\Controllers\Admin\UsersController::class, 'index'])->name('users.index');
    Route::get('users/export', [App\Http\Controllers\Admin\UsersController::class, 'export'])->name('users.export');
    Route::get('users/create', [App\Http\Controllers\Admin\UsersController::class, 'create'])->name('users.create');
    Route::post('users', [App\Http\Controllers\Admin\UsersController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [App\Http\Controllers\Admin\UsersController::class, 'edit'])->name('users.edit');
    Route::patch('users/{user}', [App\Http\Controllers\Admin\UsersController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [App\Http\Controllers\Admin\UsersController::class, 'destroy'])->name('users.destroy');
    Route::get('users/{user}/permissions', [App\Http\Controllers\Admin\UsersController::class, 'permissions'])->name('users.permissions');
    Route::patch('users/{user}/permissions', [App\Http\Controllers\Admin\UsersController::class, 'updatePermissions'])->name('users.permissions.update');
    Route::post('users/{user}/suspend', [App\Http\Controllers\Admin\UsersController::class, 'suspend'])->name('users.suspend');
    Route::post('users/{user}/unsuspend', [App\Http\Controllers\Admin\UsersController::class, 'unsuspend'])->name('users.unsuspend');
    Route::get('roles', [App\Http\Controllers\Admin\RolesController::class, 'index'])->name('roles.index');
    Route::get('roles/create', [App\Http\Controllers\Admin\RolesController::class, 'create'])->name('roles.create');
    Route::post('roles', [App\Http\Controllers\Admin\RolesController::class, 'store'])->name('roles.store');
    Route::get('roles/{role}/edit', [App\Http\Controllers\Admin\RolesController::class, 'edit'])->name('roles.edit');
    Route::patch('roles/{role}', [App\Http\Controllers\Admin\RolesController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [App\Http\Controllers\Admin\RolesController::class, 'destroy'])->name('roles.destroy');
    Route::get('roles/{role}/permissions', [App\Http\Controllers\Admin\RolesController::class, 'permissions'])->name('roles.permissions');
    Route::patch('roles/{role}/permissions', [App\Http\Controllers\Admin\RolesController::class, 'updatePermissions'])->name('roles.permissions.update');
    Route::get('permissions', [App\Http\Controllers\Admin\PermissionsController::class, 'index'])->name('permissions.index');
    Route::get('permissions/create', [App\Http\Controllers\Admin\PermissionsController::class, 'create'])->name('permissions.create');
    Route::post('permissions', [App\Http\Controllers\Admin\PermissionsController::class, 'store'])->name('permissions.store');
    Route::get('permissions/{permission}/edit', [App\Http\Controllers\Admin\PermissionsController::class, 'edit'])->name('permissions.edit');
    Route::patch('permissions/{permission}', [App\Http\Controllers\Admin\PermissionsController::class, 'update'])->name('permissions.update');
    Route::delete('permissions/{permission}', [App\Http\Controllers\Admin\PermissionsController::class, 'destroy'])->name('permissions.destroy');
    Route::get('database', [App\Http\Controllers\Admin\DatabaseController::class, 'index'])->name('database.index');
    Route::get('database/{connection}', [App\Http\Controllers\Admin\DatabaseController::class, 'index'])->name('database.connection.index');
    Route::get('database/{connection}/{table}', [App\Http\Controllers\Admin\DatabaseController::class, 'show'])->name('database.connection.show');
    Route::get('database/{connection}/{table}/export', [App\Http\Controllers\Admin\DatabaseController::class, 'export'])->name('database.export');
    Route::get('database/{connection}/{table}/{view}', [App\Http\Controllers\Admin\DatabaseController::class, 'show'])->name('database.connection.show.view');
    Route::post('database/query', [App\Http\Controllers\Admin\DatabaseController::class, 'query'])->name('database.query');
    Route::get('databases', [App\Http\Controllers\Admin\DatabaseController::class, 'listConnections'])->name('databases.index');
    Route::get('audit-logs', [App\Http\Controllers\Admin\AuditLogsController::class, 'index'])->name('audit-logs.index');
    Route::get('health', [App\Http\Controllers\Admin\HealthController::class, 'index'])->name('health.index');
    Route::get('failed-jobs', [App\Http\Controllers\Admin\FailedJobsController::class, 'index'])->name('failed-jobs.index');
    Route::get('failed-jobs/{id}', [App\Http\Controllers\Admin\FailedJobsController::class, 'show'])->name('failed-jobs.show');
    Route::post('failed-jobs/{uuid}/retry', [App\Http\Controllers\Admin\FailedJobsController::class, 'retry'])->name('failed-jobs.retry');
    Route::post('failed-jobs/retry-all', [App\Http\Controllers\Admin\FailedJobsController::class, 'retryAll'])->name('failed-jobs.retry-all');
    Route::delete('failed-jobs/{id}', [App\Http\Controllers\Admin\FailedJobsController::class, 'destroy'])->name('failed-jobs.destroy');
    Route::delete('failed-jobs', [App\Http\Controllers\Admin\FailedJobsController::class, 'destroyAll'])->name('failed-jobs.destroy-all');
    Route::get('cache', [App\Http\Controllers\Admin\CacheController::class, 'index'])->name('cache.index');
    Route::post('cache/clear', [App\Http\Controllers\Admin\CacheController::class, 'clearCache'])->name('cache.clear');
    Route::post('cache/clear-views', [App\Http\Controllers\Admin\CacheController::class, 'clearViews'])->name('cache.clear-views');
    Route::post('cache/clear-routes', [App\Http\Controllers\Admin\CacheController::class, 'clearRoutes'])->name('cache.clear-routes');
    Route::post('cache/clear-config', [App\Http\Controllers\Admin\CacheController::class, 'clearConfig'])->name('cache.clear-config');
    Route::post('cache/clear-all', [App\Http\Controllers\Admin\CacheController::class, 'clearAll'])->name('cache.clear-all');
    Route::post('cache/maintenance', [App\Http\Controllers\Admin\CacheController::class, 'toggleMaintenance'])->name('cache.maintenance');
    Route::get('logs', [App\Http\Controllers\Admin\LogsController::class, 'index'])->name('logs.index');
    Route::get('notifications', [App\Http\Controllers\Admin\NotificationsController::class, 'index'])->name('notifications.index');
    Route::post('notifications/send', [App\Http\Controllers\Admin\NotificationsController::class, 'send'])->name('notifications.send');
    Route::patch('notifications/{id}/read', [App\Http\Controllers\Admin\NotificationsController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::delete('notifications/{id}', [App\Http\Controllers\Admin\NotificationsController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('notifications', [App\Http\Controllers\Admin\NotificationsController::class, 'destroyAll'])->name('notifications.destroy-all');
    Route::get('modules', [App\Http\Controllers\Admin\ModulesController::class, 'index'])->name('modules.index');
    Route::get('packages', [App\Http\Controllers\Admin\PackagesController::class, 'index'])->name('packages.index');
    Route::patch('packages/{key}', [App\Http\Controllers\Admin\PackagesController::class, 'update'])->name('packages.update');
});
