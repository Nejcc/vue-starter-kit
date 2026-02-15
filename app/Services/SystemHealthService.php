<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\SystemHealthRepositoryInterface;
use App\Contracts\Services\SystemHealthServiceInterface;

final class SystemHealthService extends AbstractNonModelService implements SystemHealthServiceInterface
{
    public function __construct(
        private readonly SystemHealthRepositoryInterface $repository,
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function runAllChecks(): array
    {
        return [
            $this->repository->checkDatabaseConnection(),
            $this->repository->checkCacheConnection(),
            $this->repository->checkStorageStatus(),
            $this->repository->checkQueueStatus(),
            $this->repository->checkSchedulerStatus(),
        ];
    }

    /** @return array<string, mixed> */
    public function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'server_time' => now()->toDateTimeString(),
        ];
    }
}
