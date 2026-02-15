<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\FailedJobServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class FailedJobsController extends Controller
{
    public function __construct(
        private readonly FailedJobServiceInterface $failedJobService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('admin/FailedJobs/Index', $this->failedJobService->getIndexData($request));
    }

    public function show(int $id): Response
    {
        return Inertia::render('admin/FailedJobs/Show', [
            'job' => $this->failedJobService->getJobDetail($id),
        ]);
    }

    public function retry(string $uuid): RedirectResponse
    {
        try {
            $this->failedJobService->retryJob($uuid);
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to retry job: ' . $e->getMessage());
        }

        return back()->with('success', 'Job has been pushed back onto the queue.');
    }

    public function retryAll(): RedirectResponse
    {
        try {
            $this->failedJobService->retryAllJobs();
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to retry jobs: ' . $e->getMessage());
        }

        return back()->with('success', 'All failed jobs have been pushed back onto the queue.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->failedJobService->deleteJob($id);

        return back()->with('success', 'Failed job deleted.');
    }

    public function destroyAll(): RedirectResponse
    {
        $this->failedJobService->deleteAllJobs();

        return back()->with('success', 'All failed jobs have been deleted.');
    }
}
