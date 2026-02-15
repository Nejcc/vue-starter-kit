<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\CacheManagementRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CacheManagementRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CacheManagementRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CacheManagementRepository();
    }

    public function test_get_driver_info_has_expected_keys(): void
    {
        $result = $this->repository->getDriverInfo();

        $this->assertArrayHasKey('default', $result);
        $this->assertArrayHasKey('stores', $result);
        $this->assertArrayHasKey('prefix', $result);
        $this->assertIsArray($result['stores']);
    }

    public function test_get_cache_stats_returns_expected_shape(): void
    {
        $result = $this->repository->getCacheStats();

        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('expired', $result);
        $this->assertArrayHasKey('active', $result);
    }

    public function test_get_cache_items_returns_array(): void
    {
        $result = $this->repository->getCacheItems();

        $this->assertIsArray($result);
    }

    public function test_get_maintenance_status_returns_is_down(): void
    {
        $result = $this->repository->getMaintenanceStatus();

        $this->assertArrayHasKey('is_down', $result);
        $this->assertIsBool($result['is_down']);
    }

    public function test_clear_artisan_cache_runs_command(): void
    {
        // Should not throw
        $this->repository->clearArtisanCache('cache:clear');

        $this->assertTrue(true);
    }

    public function test_clear_all_caches_returns_errors_array(): void
    {
        $errors = $this->repository->clearAllCaches();

        $this->assertIsArray($errors);
    }

    public function test_enable_and_disable_maintenance(): void
    {
        $this->repository->enableMaintenance();

        $this->assertTrue(app()->isDownForMaintenance());

        $this->repository->disableMaintenance();

        $this->assertFalse(app()->isDownForMaintenance());
    }
}
