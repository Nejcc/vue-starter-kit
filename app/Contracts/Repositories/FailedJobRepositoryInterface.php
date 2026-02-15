<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;

interface FailedJobRepositoryInterface
{
    public function getPaginated(?string $search, ?string $queue, int $perPage = 20): LengthAwarePaginator;

    public function findById(int $id): ?object;

    /** @return array<int, string> */
    public function getDistinctQueues(): array;

    /** @return array{total: int, queues: int} */
    public function getStats(): array;

    public function deleteById(int $id): void;

    public function retryByUuid(string $uuid): void;

    public function retryAll(): void;

    public function flushAll(): void;

    /** @param array<string, mixed>|null $payload */
    public function extractJobName(?array $payload): string;

    public function extractExceptionSummary(string $exception): string;
}
