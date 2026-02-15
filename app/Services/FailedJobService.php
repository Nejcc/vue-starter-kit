<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\AuditEvent;
use App\Contracts\Repositories\FailedJobRepositoryInterface;
use App\Contracts\Services\FailedJobServiceInterface;
use App\Models\AuditLog;
use Illuminate\Http\Request;

final class FailedJobService extends AbstractNonModelService implements FailedJobServiceInterface
{
    public function __construct(
        private readonly FailedJobRepositoryInterface $repository,
    ) {}

    /**
     * @return array{failedJobs: mixed, queues: array<int, string>, stats: array{total: int, queues: int}, filters: array<string, string>}
     */
    public function getIndexData(Request $request): array
    {
        $search = $request->get('search');
        $queue = $request->get('queue');

        $failedJobs = $this->repository->getPaginated($search, $queue)->through(function ($job) {
            $payload = json_decode($job->payload, true);

            return [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'connection' => $job->connection,
                'queue' => $job->queue,
                'job_name' => $this->repository->extractJobName($payload),
                'exception_summary' => $this->repository->extractExceptionSummary($job->exception),
                'failed_at' => $job->failed_at,
            ];
        });

        return [
            'failedJobs' => $failedJobs,
            'queues' => $this->repository->getDistinctQueues(),
            'stats' => $this->repository->getStats(),
            'filters' => [
                'search' => $request->get('search', ''),
                'queue' => $request->get('queue', ''),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function getJobDetail(int $id): array
    {
        $job = $this->repository->findById($id);

        if (!$job) {
            abort(404);
        }

        $payload = json_decode($job->payload, true);

        return [
            'id' => $job->id,
            'uuid' => $job->uuid,
            'connection' => $job->connection,
            'queue' => $job->queue,
            'job_name' => $this->repository->extractJobName($payload),
            'payload' => $payload,
            'exception' => $job->exception,
            'failed_at' => $job->failed_at,
        ];
    }

    public function retryJob(string $uuid): void
    {
        $this->repository->retryByUuid($uuid);

        AuditLog::log(AuditEvent::FAILED_JOB_RETRIED, null, null, ['uuid' => $uuid]);
    }

    public function retryAllJobs(): void
    {
        $this->repository->retryAll();

        AuditLog::log(AuditEvent::FAILED_JOB_RETRIED, null, null, ['scope' => 'all']);
    }

    public function deleteJob(int $id): void
    {
        $this->repository->deleteById($id);

        AuditLog::log(AuditEvent::FAILED_JOB_DELETED, null, null, ['id' => $id]);
    }

    public function deleteAllJobs(): void
    {
        $this->repository->flushAll();

        AuditLog::log(AuditEvent::FAILED_JOB_DELETED, null, null, ['scope' => 'all']);
    }
}
