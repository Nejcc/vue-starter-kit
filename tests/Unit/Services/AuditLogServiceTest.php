<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditLogService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AuditLogService::class);
    }

    public function test_get_filtered_paginated_returns_results(): void
    {
        $user = User::factory()->create();
        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'user.created',
            'ip_address' => '127.0.0.1',
        ]);

        $result = $this->service->getFilteredPaginated(null, null);

        $this->assertCount(1, $result->items());
    }

    public function test_get_filtered_paginated_filters_by_search(): void
    {
        $user = User::factory()->create();
        AuditLog::create(['user_id' => $user->id, 'event' => 'user.created', 'ip_address' => '127.0.0.1']);
        AuditLog::create(['user_id' => $user->id, 'event' => 'role.deleted', 'ip_address' => '127.0.0.1']);

        $result = $this->service->getFilteredPaginated('role', null);

        $this->assertCount(1, $result->items());
    }

    public function test_get_filtered_paginated_filters_by_event(): void
    {
        $user = User::factory()->create();
        AuditLog::create(['user_id' => $user->id, 'event' => 'user.created', 'ip_address' => '127.0.0.1']);
        AuditLog::create(['user_id' => $user->id, 'event' => 'role.deleted', 'ip_address' => '127.0.0.1']);

        $result = $this->service->getFilteredPaginated(null, 'user.created');

        $this->assertCount(1, $result->items());
    }

    public function test_get_distinct_event_types(): void
    {
        $user = User::factory()->create();
        AuditLog::create(['user_id' => $user->id, 'event' => 'user.created', 'ip_address' => '127.0.0.1']);
        AuditLog::create(['user_id' => $user->id, 'event' => 'role.deleted', 'ip_address' => '127.0.0.1']);

        $events = $this->service->getDistinctEventTypes();

        $this->assertCount(2, $events);
    }

    public function test_get_recent_with_user(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            AuditLog::create([
                'user_id' => $user->id,
                'event' => "event.{$i}",
                'ip_address' => '127.0.0.1',
            ]);
        }

        $recent = $this->service->getRecentWithUser(3);

        $this->assertCount(3, $recent);
    }
}
