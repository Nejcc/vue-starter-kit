<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\AuditEvent;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class FailedJobsController extends Controller
{
    public function index(Request $request): Response
    {
        $query = DB::table('failed_jobs')->orderByDesc('failed_at');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('queue', 'like', "%{$search}%")
                    ->orWhere('payload', 'like', "%{$search}%")
                    ->orWhere('exception', 'like', "%{$search}%");
            });
        }

        if ($queue = $request->get('queue')) {
            $query->where('queue', $queue);
        }

        $failedJobs = $query->paginate(20)->through(function ($job) {
            $payload = json_decode($job->payload, true);

            return [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'connection' => $job->connection,
                'queue' => $job->queue,
                'job_name' => $this->extractJobName($payload),
                'exception_summary' => $this->extractExceptionSummary($job->exception),
                'failed_at' => $job->failed_at,
            ];
        });

        $queues = DB::table('failed_jobs')
            ->select('queue')
            ->distinct()
            ->pluck('queue')
            ->toArray();

        $stats = [
            'total' => DB::table('failed_jobs')->count(),
            'queues' => count($queues),
        ];

        return Inertia::render('admin/FailedJobs/Index', [
            'failedJobs' => $failedJobs,
            'queues' => $queues,
            'stats' => $stats,
            'filters' => [
                'search' => $request->get('search', ''),
                'queue' => $request->get('queue', ''),
            ],
        ]);
    }

    public function show(int $id): Response
    {
        $job = DB::table('failed_jobs')->where('id', $id)->first();

        if (!$job) {
            abort(404);
        }

        $payload = json_decode($job->payload, true);

        return Inertia::render('admin/FailedJobs/Show', [
            'job' => [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'connection' => $job->connection,
                'queue' => $job->queue,
                'job_name' => $this->extractJobName($payload),
                'payload' => $payload,
                'exception' => $job->exception,
                'failed_at' => $job->failed_at,
            ],
        ]);
    }

    public function retry(string $uuid): RedirectResponse
    {
        try {
            Artisan::call('queue:retry', ['id' => [$uuid]]);
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to retry job: ' . $e->getMessage());
        }

        AuditLog::log(AuditEvent::FAILED_JOB_RETRIED, null, null, ['uuid' => $uuid]);

        return back()->with('success', 'Job has been pushed back onto the queue.');
    }

    public function retryAll(): RedirectResponse
    {
        try {
            Artisan::call('queue:retry', ['id' => ['all']]);
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to retry jobs: ' . $e->getMessage());
        }

        AuditLog::log(AuditEvent::FAILED_JOB_RETRIED, null, null, ['scope' => 'all']);

        return back()->with('success', 'All failed jobs have been pushed back onto the queue.');
    }

    public function destroy(int $id): RedirectResponse
    {
        DB::table('failed_jobs')->where('id', $id)->delete();

        AuditLog::log(AuditEvent::FAILED_JOB_DELETED, null, null, ['id' => $id]);

        return back()->with('success', 'Failed job deleted.');
    }

    public function destroyAll(): RedirectResponse
    {
        Artisan::call('queue:flush');

        AuditLog::log(AuditEvent::FAILED_JOB_DELETED, null, null, ['scope' => 'all']);

        return back()->with('success', 'All failed jobs have been deleted.');
    }

    /** @param array<string, mixed>|null $payload */
    private function extractJobName(?array $payload): string
    {
        if (!$payload) {
            return 'Unknown';
        }

        $displayName = $payload['displayName'] ?? null;

        if ($displayName) {
            return class_basename($displayName);
        }

        $data = json_decode($payload['data']['command'] ?? '{}', true);

        return class_basename($data['commandName'] ?? 'Unknown');
    }

    private function extractExceptionSummary(string $exception): string
    {
        $firstLine = strtok($exception, "\n");

        if ($firstLine === false) {
            return 'Unknown error';
        }

        return mb_strlen($firstLine) > 150
            ? mb_substr($firstLine, 0, 150) . '...'
            : $firstLine;
    }
}
