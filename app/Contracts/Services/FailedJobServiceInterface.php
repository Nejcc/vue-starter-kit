<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use Illuminate\Http\Request;

interface FailedJobServiceInterface
{
    /**
     * @return array{failedJobs: mixed, queues: array<int, string>, stats: array{total: int, queues: int}, filters: array<string, string>}
     */
    public function getIndexData(Request $request): array;

    /** @return array<string, mixed> */
    public function getJobDetail(int $id): array;

    public function retryJob(string $uuid): void;

    public function retryAllJobs(): void;

    public function deleteJob(int $id): void;

    public function deleteAllJobs(): void;
}
