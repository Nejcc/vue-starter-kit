<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\SystemHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SystemHealthServiceTest extends TestCase
{
    use RefreshDatabase;

    private SystemHealthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SystemHealthService::class);
    }

    public function test_run_all_checks_returns_five_checks(): void
    {
        $checks = $this->service->runAllChecks();

        $this->assertCount(5, $checks);
    }

    public function test_run_all_checks_returns_correct_shape(): void
    {
        $checks = $this->service->runAllChecks();

        foreach ($checks as $check) {
            $this->assertArrayHasKey('name', $check);
            $this->assertArrayHasKey('status', $check);
            $this->assertArrayHasKey('message', $check);
            $this->assertArrayHasKey('details', $check);
            $this->assertContains($check['status'], ['ok', 'warning', 'error']);
        }
    }

    public function test_run_all_checks_contains_expected_check_names(): void
    {
        $checks = $this->service->runAllChecks();
        $names = array_column($checks, 'name');

        $this->assertContains('Database', $names);
        $this->assertContains('Cache', $names);
        $this->assertContains('Storage', $names);
        $this->assertContains('Queue', $names);
        $this->assertContains('Scheduler', $names);
    }

    public function test_get_system_info_returns_expected_keys(): void
    {
        $info = $this->service->getSystemInfo();

        $this->assertArrayHasKey('php_version', $info);
        $this->assertArrayHasKey('laravel_version', $info);
        $this->assertArrayHasKey('environment', $info);
        $this->assertArrayHasKey('debug_mode', $info);
        $this->assertArrayHasKey('timezone', $info);
        $this->assertArrayHasKey('locale', $info);
        $this->assertArrayHasKey('server_time', $info);
        $this->assertEquals(PHP_VERSION, $info['php_version']);
    }
}
