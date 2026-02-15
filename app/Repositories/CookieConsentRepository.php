<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\CookieConsentRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

final class CookieConsentRepository extends AbstractNonModelRepository implements CookieConsentRepositoryInterface
{
    /**
     * @return array<string, bool>
     */
    public function getAuthenticatedUserPreferences(User $user): array
    {
        return $user->cookie_consent_preferences ?? [];
    }

    /**
     * @return array<string, bool>
     */
    public function getGuestPreferences(Request $request): array
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
     * @param  array<string, bool>  $preferences
     */
    public function storeAuthenticatedUserPreferences(User $user, array $preferences, ?string $ipAddress): void
    {
        $user->updateCookieConsent($preferences, $ipAddress);
    }

    /**
     * @param  array<string, bool>  $preferences
     */
    public function storeGuestPreferences(Request $request, array $preferences): void
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
     * @return array<string, array<string, mixed>>
     */
    public function getCookieCategories(): array
    {
        return config('cookie.categories', []);
    }

    /**
     * @return array<string, mixed>
     */
    public function getCookieConfig(): array
    {
        return [
            'enabled' => config('cookie.enabled', true),
            'gdpr_mode' => config('cookie.gdpr_mode', true),
        ];
    }

    private function getCookieName(): string
    {
        return config('cookie.storage.key_prefix', 'cookie_consent') . '_guest';
    }

    private function getCookieLifetime(): int
    {
        $days = config('cookie.storage.lifetime', 365);

        return $days * 24 * 60;
    }

    /**
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
}
