<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\AdminNavigation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        if ($user) {
            $user->loadMissing(['roles', 'permissions']);
        }

        $isImpersonating = session()->has('impersonator_id');
        $impersonator = null;

        if ($isImpersonating) {
            $impersonatorId = session()->get('impersonator_id');
            $impersonator = \App\Models\User::find($impersonatorId);
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => mb_trim($message), 'author' => mb_trim($author)],
            'auth' => [
                'user' => $user ? [
                    ...$user->toArray(),
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                ] : null,
                'isImpersonating' => $isImpersonating,
                'impersonator' => $impersonator ? [
                    'id' => $impersonator->id,
                    'name' => $impersonator->name,
                    'email' => $impersonator->email,
                ] : null,
            ],
            'auth_layout' => class_exists(\LaravelPlus\GlobalSettings\Models\Setting::class)
                ? \LaravelPlus\GlobalSettings\Models\Setting::get('auth_layout', 'simple')
                : 'simple',
            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'modules' => $this->getInstalledModules(),
            'moduleNavigation' => app(AdminNavigation::class)->groups(),
            'notifications' => [
                'unreadCount' => $user ? $user->unreadNotifications()->count() : 0,
            ],
            'currentOrganization' => $user && method_exists($user, 'currentOrganization')
                ? $user->currentOrganization()
                : null,
            'organizations' => $user && method_exists($user, 'organizations')
                ? $user->organizations()->select('organizations.id', 'organizations.name', 'organizations.slug', 'organizations.is_personal')->get()
                : [],
            'localization' => fn () => $this->getLocalizationData(),
        ];
    }

    /**
     * Get localization data for the frontend.
     *
     * @return array<string, mixed>|null
     */
    private function getLocalizationData(): ?array
    {
        if (!class_exists(\LaravelPlus\Localization\Services\LocaleService::class)) {
            return null;
        }

        $localeService = app(\LaravelPlus\Localization\Services\LocaleService::class);

        return [
            'locale' => app()->getLocale(),
            'fallbackLocale' => config('app.fallback_locale'),
            'translations' => $localeService->getTranslationsForCurrentLocale(),
            'availableLocales' => $localeService->getActiveLanguages(),
        ];
    }

    /**
     * Detect which optional packages/modules are installed.
     *
     * @return array<string, bool>
     */
    private function getInstalledModules(): array
    {
        return [
            'globalSettings' => Route::has('admin.settings.index'),
            'payments' => Route::has('admin.payments.dashboard'),
            'subscribers' => Route::has('admin.subscribers.index'),
            'horizon' => Route::has('horizon.index'),
            'organizations' => Route::has('admin.organizations.index'),
            'localizations' => Route::has('admin.localizations.languages.index'),
        ];
    }
}
