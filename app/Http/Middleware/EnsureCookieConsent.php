<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCookieConsent
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware if cookie consent is disabled
        if (!config('cookie.enabled', true)) {
            return $next($request);
        }

        $user = Auth::user();
        $hasConsent = false;
        $preferences = [];

        if ($user) {
            // For authenticated users, check database
            $hasConsent = $user->hasCookieConsent();
            $preferences = $user->cookie_consent_preferences ?? [];
        } else {
            // For guest users, check session
            $preferences = $request->session()->get(
                config('cookie.storage.session_key'),
                []
            );
            $hasConsent = !empty($preferences);
        }

        // Share cookie consent state with Inertia pages
        Inertia::share([
            'cookieConsent' => [
                'hasConsent' => $hasConsent,
                'preferences' => $preferences,
                'categories' => config('cookie.categories', []),
                'config' => [
                    'enabled' => config('cookie.enabled', true),
                    'gdpr_mode' => config('cookie.gdpr_mode', true),
                    'banner' => config('cookie.banner', []),
                    'modal' => config('cookie.modal', []),
                ],
            ],
        ]);

        // Block non-essential cookies if no consent given
        if (!$hasConsent) {
            $this->blockNonEssentialCookies($request);
        }

        return $next($request);
    }

    /**
     * Block non-essential cookies by modifying the request.
     */
    private function blockNonEssentialCookies(Request $request): void
    {
        // Get essential cookies from config
        $essentialCookies = config('cookie.categories.essential.cookies', []);

        // This is a placeholder - in a real implementation, you might:
        // 1. Modify cookie headers in the response
        // 2. Prevent JavaScript from setting non-essential cookies
        // 3. Use a cookie management service

        // For now, we'll just track this in the request
        $request->attributes->set('block_non_essential_cookies', true);
        $request->attributes->set('allowed_cookies', $essentialCookies);
    }
}
