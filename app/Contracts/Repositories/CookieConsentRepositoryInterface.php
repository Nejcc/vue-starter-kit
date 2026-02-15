<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Http\Request;

interface CookieConsentRepositoryInterface
{
    /**
     * @return array<string, bool>
     */
    public function getAuthenticatedUserPreferences(User $user): array;

    /**
     * @return array<string, bool>
     */
    public function getGuestPreferences(Request $request): array;

    /**
     * @param  array<string, bool>  $preferences
     */
    public function storeAuthenticatedUserPreferences(User $user, array $preferences, ?string $ipAddress): void;

    /**
     * @param  array<string, bool>  $preferences
     */
    public function storeGuestPreferences(Request $request, array $preferences): void;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getCookieCategories(): array;

    /**
     * @return array<string, mixed>
     */
    public function getCookieConfig(): array;
}
