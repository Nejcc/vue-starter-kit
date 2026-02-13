<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\AuditLog;
use App\Models\User;
use App\Repositories\AuditLogRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuditLogRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AuditLogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AuditLogRepository();
    }

    public function test_get_filtered_paginated_returns_paginated_results(): void
    {
        $user = User::factory()->create();
        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'user.created',
            'ip_address' => '127.0.0.1',
        ]);

        $result = $this->repository->getFilteredPaginated(null, null, 25);

        $this->assertCount(1, $result->items());
    }

    public function test_get_filtered_paginated_filters_by_search(): void
    {
        $user = User::factory()->create();
        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'user.created',
            'ip_address' => '127.0.0.1',
        ]);
        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'role.deleted',
            'ip_address' => '127.0.0.1',
        ]);

        $result = $this->repository->getFilteredPaginated('role', null, 25);

        $this->assertCount(1, $result->items());
        $this->assertEquals('role.deleted', $result->items()[0]->event);
    }

    public function test_get_filtered_paginated_filters_by_event(): void
    {
        $user = User::factory()->create();
        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'user.created',
            'ip_address' => '127.0.0.1',
        ]);
        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'role.deleted',
            'ip_address' => '127.0.0.1',
        ]);

        $result = $this->repository->getFilteredPaginated(null, 'user.created', 25);

        $this->assertCount(1, $result->items());
        $this->assertEquals('user.created', $result->items()[0]->event);
    }

    public function test_get_distinct_event_types_returns_unique_events(): void
    {
        $user = User::factory()->create();
        AuditLog::create(['user_id' => $user->id, 'event' => 'user.created', 'ip_address' => '127.0.0.1']);
        AuditLog::create(['user_id' => $user->id, 'event' => 'user.created', 'ip_address' => '127.0.0.1']);
        AuditLog::create(['user_id' => $user->id, 'event' => 'role.deleted', 'ip_address' => '127.0.0.1']);

        $events = $this->repository->getDistinctEventTypes();

        $this->assertCount(2, $events);
        $this->assertContains('user.created', $events->toArray());
        $this->assertContains('role.deleted', $events->toArray());
    }

    public function test_get_recent_with_user_returns_limited_results(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 15; $i++) {
            AuditLog::create([
                'user_id' => $user->id,
                'event' => "event.{$i}",
                'ip_address' => '127.0.0.1',
            ]);
        }

        $recent = $this->repository->getRecentWithUser(10);

        $this->assertCount(10, $recent);
        $this->assertTrue($recent->first()->relationLoaded('user'));
    }
}
