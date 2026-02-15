<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use Illuminate\Http\Request;

interface CacheManagementServiceInterface
{
    /**
     * @return array{driver: array<string, mixed>, stats: array<string, mixed>, items: array<int, array<string, mixed>>, maintenance: array<string, mixed>}
     */
    public function getIndexData(): array;

    public function clearCache(string $type): void;

    public function clearAllCaches(): void;

    /**
     * @return array{message: string}
     */
    public function toggleMaintenance(Request $request): array;
}
