<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\SystemHealthRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SystemHealthRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SystemHealthRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SystemHealthRepository();
    }

    public function test_check_database_connection_returns_correct_shape(): void
    {
        $result = $this->repository->checkDatabaseConnection();

        $this->assertEquals('Database', $result['name']);
        $this->assertContains($result['status'], ['ok', 'warning', 'error']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('details', $result);
    }

    public function test_check_database_connection_succeeds(): void
    {
        $result = $this->repository->checkDatabaseConnection();

        $this->assertEquals('ok', $result['status']);
        $this->assertStringContainsString('Connected', $result['message']);
        $this->assertArrayHasKey('driver', $result['details']);
        $this->assertArrayHasKey('tables', $result['details']);
    }

    public function test_check_cache_connection_returns_correct_shape(): void
    {
        $result = $this->repository->checkCacheConnection();

        $this->assertEquals('Cache', $result['name']);
        $this->assertContains($result['status'], ['ok', 'warning', 'error']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('details', $result);
    }

    public function test_check_storage_status_returns_correct_shape(): void
    {
        $result = $this->repository->checkStorageStatus();

        $this->assertEquals('Storage', $result['name']);
        $this->assertContains($result['status'], ['ok', 'warning', 'error']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('details', $result);
    }

    public function test_check_queue_status_returns_correct_shape(): void
    {
        $result = $this->repository->checkQueueStatus();

        $this->assertEquals('Queue', $result['name']);
        $this->assertContains($result['status'], ['ok', 'warning', 'error']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('details', $result);
    }

    public function test_check_scheduler_status_returns_correct_shape(): void
    {
        $result = $this->repository->checkSchedulerStatus();

        $this->assertEquals('Scheduler', $result['name']);
        $this->assertContains($result['status'], ['ok', 'warning', 'error']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('details', $result);
    }

    public function test_all_checks_use_consistent_result_format(): void
    {
        $checks = [
            $this->repository->checkDatabaseConnection(),
            $this->repository->checkCacheConnection(),
            $this->repository->checkStorageStatus(),
            $this->repository->checkQueueStatus(),
            $this->repository->checkSchedulerStatus(),
        ];

        foreach ($checks as $check) {
            $this->assertCount(4, $check);
            $this->assertArrayHasKey('name', $check);
            $this->assertArrayHasKey('status', $check);
            $this->assertArrayHasKey('message', $check);
            $this->assertArrayHasKey('details', $check);
            $this->assertIsString($check['name']);
            $this->assertIsString($check['status']);
            $this->assertIsString($check['message']);
            $this->assertIsArray($check['details']);
        }
    }
}
