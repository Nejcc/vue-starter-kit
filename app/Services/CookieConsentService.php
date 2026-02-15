<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\CookieConsentRepositoryInterface;
use App\Contracts\Services\CookieConsentServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class CookieConsentService extends AbstractNonModelService implements CookieConsentServiceInterface
{
    public function __construct(
        private readonly CookieConsentRepositoryInterface $repository,
    ) {}

    /**
     * @return array{preferences: array<string, bool>, hasConsent: bool, categories: array<string, array<string, mixed>>, config: array<string, mixed>}
     */
    public function getPreferences(Request $request): array
    {
        $user = Auth::user();

        if ($user) {
            $preferences = $this->repository->getAuthenticatedUserPreferences($user);
            $hasConsent = $user->hasCookieConsent();
        } else {
            $preferences = $this->repository->getGuestPreferences($request);
            $hasConsent = !empty($preferences);
        }

        return [
            'preferences' => $preferences,
            'hasConsent' => $hasConsent,
            'categories' => $this->repository->getCookieCategories(),
            'config' => $this->repository->getCookieConfig(),
        ];
    }

    /**
     * @param  array<string, bool>  $preferences
     * @return array<string, bool>
     */
    public function updatePreferences(Request $request, array $preferences): array
    {
        $this->savePreferences($request, $preferences, 'Cookie consent updated');

        return $preferences;
    }

    /**
     * @return array<string, bool>
     */
    public function acceptAll(Request $request): array
    {
        $categories = array_keys($this->repository->getCookieCategories());
        $preferences = array_fill_keys($categories, true);

        $this->savePreferences($request, $preferences, 'Cookie consent - Accept All');

        return $preferences;
    }

    /**
     * @return array<string, bool>
     */
    public function rejectAll(Request $request): array
    {
        $categories = array_keys($this->repository->getCookieCategories());
        $preferences = array_fill_keys($categories, false);
        $preferences['essential'] = true;

        $this->savePreferences($request, $preferences, 'Cookie consent - Reject All');

        return $preferences;
    }

    /**
     * @param  array<string, bool>  $preferences
     */
    private function savePreferences(Request $request, array $preferences, string $logMessage): void
    {
        $this->logConsentChange($request, $preferences, $logMessage);

        $user = Auth::user();

        if ($user) {
            $this->repository->storeAuthenticatedUserPreferences($user, $preferences, $request->ip());
        } else {
            $this->repository->storeGuestPreferences($request, $preferences);
        }
    }

    /**
     * @param  array<string, bool>  $preferences
     */
    private function logConsentChange(Request $request, array $preferences, string $message): void
    {
        if (config('cookie.audit_logging.enabled', true)) {
            Log::channel(config('cookie.audit_logging.log_channel', 'daily'))
                ->info($message, [
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'preferences' => $preferences,
                    'timestamp' => now(),
                ]);
        }
    }
}
