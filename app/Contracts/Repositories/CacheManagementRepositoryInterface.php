<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

interface CacheManagementRepositoryInterface
{
    /** @return array<string, mixed> */
    public function getDriverInfo(): array;

    /** @return array<string, mixed> */
    public function getCacheStats(): array;

    /** @return array<int, array<string, mixed>> */
    public function getCacheItems(): array;

    /** @return array<string, mixed> */
    public function getMaintenanceStatus(): array;

    public function clearArtisanCache(string $command): void;

    /**
     * @return array<int, string> List of error messages for failed commands
     */
    public function clearAllCaches(): array;

    /** @param array<string, string> $params */
    public function enableMaintenance(array $params = []): void;

    public function disableMaintenance(): void;
}
