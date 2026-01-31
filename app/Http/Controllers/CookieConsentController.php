<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCookieConsentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

final class CookieConsentController extends Controller
{
    /**
     * Get the cookie name for storing guest consent.
     */
    private function getCookieName(): string
    {
        return config('cookie.storage.key_prefix', 'cookie_consent').'_guest';
    }

    /**
     * Get the cookie lifetime in minutes.
     */
    private function getCookieLifetime(): int
    {
        $days = config('cookie.storage.lifetime', 365);

        return $days * 24 * 60; // Convert days to minutes
    }

    /**
     * Sanitize decoded cookie preferences to only include valid category keys with boolean values.
     *
     * @param  array<string, mixed>  $preferences
     * @return array<string, bool>
     */
    private function sanitizePreferences(array $preferences): array
    {
        $validKeys = array_keys(config('cookie.categories', []));

        $sanitized = [];
        foreach ($validKeys as $key) {
            if (array_key_exists($key, $preferences)) {
                $sanitized[$key] = (bool) $preferences[$key];
            }
        }

        return $sanitized;
    }

    /**
     * Store guest cookie consent preferences in both session and browser cookie.
     */
    private function storeGuestPreferences(Request $request, array $preferences): void
    {
        // Store in session for immediate access
        $request->session()->put(
            config('cookie.storage.session_key'),
            $preferences
        );

        // Also store in browser cookie for persistence across sessions
        Cookie::queue(
            $this->getCookieName(),
            json_encode($preferences),
            $this->getCookieLifetime()
        );
    }

    /**
     * Get guest cookie consent preferences from session or browser cookie.
     */
    private function getGuestPreferences(Request $request): array
    {
        // First check session
        $preferences = $request->session()->get(
            config('cookie.storage.session_key')
        );

        // If not in session, check browser cookie
        if (empty($preferences)) {
            $cookieValue = $request->cookie($this->getCookieName());
            if ($cookieValue) {
                $decoded = json_decode($cookieValue, true) ?? [];
                $preferences = $this->sanitizePreferences($decoded);
                // Restore to session
                if (!empty($preferences)) {
                    $request->session()->put(
                        config('cookie.storage.session_key'),
                        $preferences
                    );
                }
            }
        }

        return $preferences ?? [];
    }

    /**
     * Get the current cookie consent preferences.
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user) {
            // For authenticated users, get preferences from database
            $preferences = $user->cookie_consent_preferences ?? [];
            $hasConsent = $user->hasCookieConsent();
        } else {
            // For guest users, get preferences from session or browser cookie
            $preferences = $this->getGuestPreferences($request);
            $hasConsent = !empty($preferences);
        }

        return response()->json([
            'preferences' => $preferences,
            'hasConsent' => $hasConsent,
            'categories' => config('cookie.categories', []),
            'config' => [
                'enabled' => config('cookie.enabled', true),
                'gdpr_mode' => config('cookie.gdpr_mode', true),
            ],
        ]);
    }

    /**
     * Update cookie consent preferences.
     */
    public function updatePreferences(UpdateCookieConsentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $ipAddress = $request->ip();

        // Log consent change for audit purposes
        if (config('cookie.audit_logging.enabled', true)) {
            Log::channel(config('cookie.audit_logging.log_channel', 'daily'))
                ->info('Cookie consent updated', [
                    'user_id' => Auth::id(),
                    'ip_address' => $ipAddress,
                    'preferences' => $validated,
                    'timestamp' => now(),
                ]);
        }

        $user = Auth::user();

        if ($user) {
            // For authenticated users, save to database
            $user->updateCookieConsent($validated, $ipAddress);
        } else {
            // For guest users, save to session and browser cookie
            $this->storeGuestPreferences($request, $validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cookie preferences updated successfully.',
            'preferences' => $validated,
        ]);
    }

    /**
     * Accept all cookies.
     */
    public function acceptAll(Request $request): JsonResponse
    {
        $categories = array_keys(config('cookie.categories', []));
        $preferences = array_fill_keys($categories, true);

        $ipAddress = $request->ip();

        // Log consent change for audit purposes
        if (config('cookie.audit_logging.enabled', true)) {
            Log::channel(config('cookie.audit_logging.log_channel', 'daily'))
                ->info('Cookie consent - Accept All', [
                    'user_id' => Auth::id(),
                    'ip_address' => $ipAddress,
                    'preferences' => $preferences,
                    'timestamp' => now(),
                ]);
        }

        $user = Auth::user();

        if ($user) {
            $user->updateCookieConsent($preferences, $ipAddress);
        } else {
            $this->storeGuestPreferences($request, $preferences);
        }

        return response()->json([
            'success' => true,
            'message' => 'All cookies accepted.',
            'preferences' => $preferences,
        ]);
    }

    /**
     * Reject all non-essential cookies.
     */
    public function rejectAll(Request $request): JsonResponse
    {
        $categories = array_keys(config('cookie.categories', []));
        $preferences = array_fill_keys($categories, false);

        // Essential cookies are always required
        $preferences['essential'] = true;

        $ipAddress = $request->ip();

        // Log consent change for audit purposes
        if (config('cookie.audit_logging.enabled', true)) {
            Log::channel(config('cookie.audit_logging.log_channel', 'daily'))
                ->info('Cookie consent - Reject All', [
                    'user_id' => Auth::id(),
                    'ip_address' => $ipAddress,
                    'preferences' => $preferences,
                    'timestamp' => now(),
                ]);
        }

        $user = Auth::user();

        if ($user) {
            $user->updateCookieConsent($preferences, $ipAddress);
        } else {
            $this->storeGuestPreferences($request, $preferences);
        }

        return response()->json([
            'success' => true,
            'message' => 'Non-essential cookies rejected.',
            'preferences' => $preferences,
        ]);
    }
}
