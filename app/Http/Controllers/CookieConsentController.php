<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\CookieConsentServiceInterface;
use App\Http\Requests\UpdateCookieConsentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CookieConsentController extends Controller
{
    public function __construct(
        private readonly CookieConsentServiceInterface $cookieConsentService,
    ) {}

    /**
     * Get the current cookie consent preferences.
     */
    public function getPreferences(Request $request): JsonResponse
    {
        return response()->json($this->cookieConsentService->getPreferences($request));
    }

    /**
     * Update cookie consent preferences.
     */
    public function updatePreferences(UpdateCookieConsentRequest $request): JsonResponse
    {
        $preferences = $this->cookieConsentService->updatePreferences($request, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cookie preferences updated successfully.',
            'preferences' => $preferences,
        ]);
    }

    /**
     * Accept all cookies.
     */
    public function acceptAll(Request $request): JsonResponse
    {
        $preferences = $this->cookieConsentService->acceptAll($request);

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
        $preferences = $this->cookieConsentService->rejectAll($request);

        return response()->json([
            'success' => true,
            'message' => 'Non-essential cookies rejected.',
            'preferences' => $preferences,
        ]);
    }
}
