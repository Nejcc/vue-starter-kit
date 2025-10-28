<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCookieConsentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CookieConsentController extends Controller
{
    /**
     * Get the current cookie consent preferences.
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $user = Auth::user();
        $ipAddress = $request->ip();

        if ($user) {
            // For authenticated users, get preferences from database
            $preferences = $user->cookie_consent_preferences ?? [];
            $hasConsent = $user->hasCookieConsent();
        } else {
            // For guest users, get preferences from session
            $preferences = $request->session()->get(
                config('cookie.storage.session_key'),
                []
            );
            $hasConsent = ! empty($preferences);
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
            // For guest users, save to session
            $request->session()->put(
                config('cookie.storage.session_key'),
                $validated
            );
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
            $request->session()->put(
                config('cookie.storage.session_key'),
                $preferences
            );
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
            $request->session()->put(
                config('cookie.storage.session_key'),
                $preferences
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Non-essential cookies rejected.',
            'preferences' => $preferences,
        ]);
    }
}
