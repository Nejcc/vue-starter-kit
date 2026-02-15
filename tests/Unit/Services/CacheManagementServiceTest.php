<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\CacheManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Tests\TestCase;

final class CacheManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private CacheManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CacheManagementService::class);
    }

    public function test_get_index_data_returns_correct_shape(): void
    {
        $result = $this->service->getIndexData();

        $this->assertArrayHasKey('driver', $result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('maintenance', $result);
    }

    public function test_clear_cache_with_valid_type(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->service->clearCache('cache');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'cache.cleared',
        ]);
    }

    public function test_clear_cache_with_invalid_type_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown cache type: invalid');

        $this->service->clearCache('invalid');
    }

    public function test_clear_cache_logs_correct_type(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->service->clearCache('views');

        $log = AuditLog::where('event', 'cache.cleared')->first();
        $this->assertNotNull($log);
        $this->assertEquals('views', $log->new_values['type']);
    }

    public function test_clear_all_caches_succeeds(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->service->clearAllCaches();

        $log = AuditLog::where('event', 'cache.cleared')->first();
        $this->assertNotNull($log);
        $this->assertEquals('all', $log->new_values['type']);
    }

    public function test_toggle_maintenance_mode_up(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Ensure app is up first
        if (app()->isDownForMaintenance()) {
            $this->service->toggleMaintenance(new Request());
        }

        $result = $this->service->toggleMaintenance(new Request());

        $this->assertArrayHasKey('message', $result);
        $this->assertStringContainsString('maintenance mode', $result['message']);

        // Bring back up
        $this->service->toggleMaintenance(new Request());
    }

    public function test_toggle_maintenance_logs_audit_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Ensure app is up
        if (app()->isDownForMaintenance()) {
            $this->service->toggleMaintenance(new Request());
            AuditLog::truncate();
        }

        $this->service->toggleMaintenance(new Request());

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'maintenance.toggled',
        ]);

        // Clean up: bring back up
        $this->service->toggleMaintenance(new Request());
    }
}
