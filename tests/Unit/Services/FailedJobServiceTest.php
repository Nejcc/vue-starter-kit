<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\FailedJobService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class FailedJobServiceTest extends TestCase
{
    use RefreshDatabase;

    private FailedJobService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FailedJobService::class);
    }

    public function test_get_index_data_returns_correct_shape(): void
    {
        $request = Request::create('/admin/failed-jobs');

        $result = $this->service->getIndexData($request);

        $this->assertArrayHasKey('failedJobs', $result);
        $this->assertArrayHasKey('queues', $result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertArrayHasKey('filters', $result);
        $this->assertArrayHasKey('total', $result['stats']);
        $this->assertArrayHasKey('queues', $result['stats']);
    }

    public function test_get_index_data_transforms_jobs_correctly(): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => 'test-uuid-123',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\TestJob']),
            'exception' => "RuntimeException: Something failed\nStack trace...",
            'failed_at' => now(),
        ]);

        $request = Request::create('/admin/failed-jobs');
        $result = $this->service->getIndexData($request);

        $items = $result['failedJobs']->items();
        $this->assertCount(1, $items);
        $this->assertEquals('test-uuid-123', $items[0]['uuid']);
        $this->assertEquals('TestJob', $items[0]['job_name']);
        $this->assertStringContainsString('RuntimeException', $items[0]['exception_summary']);
    }

    public function test_get_job_detail_returns_full_job_info(): void
    {
        $id = DB::table('failed_jobs')->insertGetId([
            'uuid' => 'detail-uuid',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\DetailJob']),
            'exception' => 'Exception: test',
            'failed_at' => now(),
        ]);

        $result = $this->service->getJobDetail($id);

        $this->assertEquals($id, $result['id']);
        $this->assertEquals('detail-uuid', $result['uuid']);
        $this->assertEquals('DetailJob', $result['job_name']);
        $this->assertArrayHasKey('payload', $result);
        $this->assertArrayHasKey('exception', $result);
    }

    public function test_get_job_detail_aborts_for_invalid_id(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $this->service->getJobDetail(99999);
    }

    public function test_retry_job_logs_audit_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        DB::table('failed_jobs')->insert([
            'uuid' => 'retry-uuid',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\RetryJob']),
            'exception' => 'Exception: test',
            'failed_at' => now(),
        ]);

        $this->service->retryJob('retry-uuid');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'failed_job.retried',
        ]);
    }

    public function test_delete_job_removes_and_logs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $id = DB::table('failed_jobs')->insertGetId([
            'uuid' => 'delete-uuid',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode([]),
            'exception' => 'Exception: test',
            'failed_at' => now(),
        ]);

        $this->service->deleteJob($id);

        $this->assertDatabaseMissing('failed_jobs', ['id' => $id]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'failed_job.deleted',
        ]);
    }

    public function test_retry_all_jobs_logs_audit_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->service->retryAllJobs();

        $log = AuditLog::where('event', 'failed_job.retried')->first();
        $this->assertNotNull($log);
        $newValues = $log->new_values;
        $this->assertEquals('all', $newValues['scope']);
    }

    public function test_delete_all_jobs_flushes_and_logs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        DB::table('failed_jobs')->insert([
            'uuid' => 'flush-uuid',
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode([]),
            'exception' => 'Exception: test',
            'failed_at' => now(),
        ]);

        $this->service->deleteAllJobs();

        $log = AuditLog::where('event', 'failed_job.deleted')->first();
        $this->assertNotNull($log);
        $newValues = $log->new_values;
        $this->assertEquals('all', $newValues['scope']);
    }
}
