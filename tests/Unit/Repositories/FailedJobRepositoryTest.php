<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\FailedJobRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class FailedJobRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FailedJobRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FailedJobRepository();
    }

    public function test_get_paginated_returns_paginator(): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => 'uuid-1',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode([]),
            'exception' => 'Exception: test',
            'failed_at' => now(),
        ]);

        $result = $this->repository->getPaginated(null, null);

        $this->assertCount(1, $result->items());
    }

    public function test_get_paginated_filters_by_search(): void
    {
        DB::table('failed_jobs')->insert([
            ['uuid' => 'uuid-1', 'connection' => 'database', 'queue' => 'emails', 'payload' => json_encode([]), 'exception' => 'Exception: test', 'failed_at' => now()],
            ['uuid' => 'uuid-2', 'connection' => 'database', 'queue' => 'default', 'payload' => json_encode([]), 'exception' => 'Exception: other', 'failed_at' => now()],
        ]);

        $result = $this->repository->getPaginated('emails', null);

        $this->assertCount(1, $result->items());
    }

    public function test_get_paginated_filters_by_queue(): void
    {
        DB::table('failed_jobs')->insert([
            ['uuid' => 'uuid-1', 'connection' => 'database', 'queue' => 'emails', 'payload' => json_encode([]), 'exception' => 'Exception: test', 'failed_at' => now()],
            ['uuid' => 'uuid-2', 'connection' => 'database', 'queue' => 'default', 'payload' => json_encode([]), 'exception' => 'Exception: other', 'failed_at' => now()],
        ]);

        $result = $this->repository->getPaginated(null, 'emails');

        $this->assertCount(1, $result->items());
        $this->assertEquals('emails', $result->items()[0]->queue);
    }

    public function test_find_by_id_returns_job(): void
    {
        $id = DB::table('failed_jobs')->insertGetId([
            'uuid' => 'find-uuid',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode([]),
            'exception' => 'Exception: test',
            'failed_at' => now(),
        ]);

        $job = $this->repository->findById($id);

        $this->assertNotNull($job);
        $this->assertEquals('find-uuid', $job->uuid);
    }

    public function test_find_by_id_returns_null_for_missing(): void
    {
        $job = $this->repository->findById(99999);

        $this->assertNull($job);
    }

    public function test_get_distinct_queues(): void
    {
        DB::table('failed_jobs')->insert([
            ['uuid' => 'uuid-1', 'connection' => 'database', 'queue' => 'emails', 'payload' => json_encode([]), 'exception' => 'e', 'failed_at' => now()],
            ['uuid' => 'uuid-2', 'connection' => 'database', 'queue' => 'emails', 'payload' => json_encode([]), 'exception' => 'e', 'failed_at' => now()],
            ['uuid' => 'uuid-3', 'connection' => 'database', 'queue' => 'default', 'payload' => json_encode([]), 'exception' => 'e', 'failed_at' => now()],
        ]);

        $queues = $this->repository->getDistinctQueues();

        $this->assertCount(2, $queues);
        $this->assertContains('emails', $queues);
        $this->assertContains('default', $queues);
    }

    public function test_get_stats_returns_correct_shape(): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => 'stats-uuid',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode([]),
            'exception' => 'Exception: test',
            'failed_at' => now(),
        ]);

        $stats = $this->repository->getStats();

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('queues', $stats);
        $this->assertEquals(1, $stats['total']);
        $this->assertEquals(1, $stats['queues']);
    }

    public function test_delete_by_id_removes_job(): void
    {
        $id = DB::table('failed_jobs')->insertGetId([
            'uuid' => 'delete-uuid',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode([]),
            'exception' => 'Exception: test',
            'failed_at' => now(),
        ]);

        $this->repository->deleteById($id);

        $this->assertDatabaseMissing('failed_jobs', ['id' => $id]);
    }

    public function test_extract_job_name_from_display_name(): void
    {
        $payload = ['displayName' => 'App\\Jobs\\SendEmailNotification'];

        $name = $this->repository->extractJobName($payload);

        $this->assertEquals('SendEmailNotification', $name);
    }

    public function test_extract_job_name_returns_unknown_for_null(): void
    {
        $name = $this->repository->extractJobName(null);

        $this->assertEquals('Unknown', $name);
    }

    public function test_extract_exception_summary_truncates_long_lines(): void
    {
        $longException = str_repeat('A', 200) . "\nStack trace...";

        $summary = $this->repository->extractExceptionSummary($longException);

        $this->assertLessThanOrEqual(153, mb_strlen($summary)); // 150 + "..."
        $this->assertStringEndsWith('...', $summary);
    }

    public function test_extract_exception_summary_returns_first_line(): void
    {
        $exception = "RuntimeException: Something failed\nStack trace\n#0 /app/...";

        $summary = $this->repository->extractExceptionSummary($exception);

        $this->assertEquals('RuntimeException: Something failed', $summary);
    }
}
