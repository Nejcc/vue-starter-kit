<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

interface SystemHealthRepositoryInterface
{
    /** @return array<string, mixed> */
    public function checkDatabaseConnection(): array;

    /** @return array<string, mixed> */
    public function checkCacheConnection(): array;

    /** @return array<string, mixed> */
    public function checkStorageStatus(): array;

    /** @return array<string, mixed> */
    public function checkQueueStatus(): array;

    /** @return array<string, mixed> */
    public function checkSchedulerStatus(): array;
}
