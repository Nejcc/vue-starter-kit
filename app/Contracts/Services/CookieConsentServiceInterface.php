<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use Illuminate\Http\Request;

interface CookieConsentServiceInterface
{
    /**
     * @return array{preferences: array<string, bool>, hasConsent: bool, categories: array<string, array<string, mixed>>, config: array<string, mixed>}
     */
    public function getPreferences(Request $request): array;

    /**
     * @param  array<string, bool>  $preferences
     * @return array<string, bool>
     */
    public function updatePreferences(Request $request, array $preferences): array;

    /**
     * @return array<string, bool>
     */
    public function acceptAll(Request $request): array;

    /**
     * @return array<string, bool>
     */
    public function rejectAll(Request $request): array;
}
