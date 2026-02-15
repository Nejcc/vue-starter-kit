<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Repositories\AdminNotificationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

final class AdminNotificationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AdminNotificationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AdminNotificationRepository();
    }

    public function test_get_paginated_returns_paginator(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test', 'Body'));

        $result = $this->repository->getPaginated(null, null, null);

        $this->assertCount(1, $result->items());
    }

    public function test_get_paginated_filters_by_unread(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test 1', 'Body'));
        $user->notify(new GeneralNotification('Test 2', 'Body'));

        // Mark one as read
        DatabaseNotification::first()->markAsRead();

        $result = $this->repository->getPaginated(null, 'unread', null);

        $this->assertCount(1, $result->items());
    }

    public function test_get_paginated_filters_by_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test 1', 'Body'));
        $user->notify(new GeneralNotification('Test 2', 'Body'));

        DatabaseNotification::first()->markAsRead();

        $result = $this->repository->getPaginated(null, 'read', null);

        $this->assertCount(1, $result->items());
    }

    public function test_get_paginated_filters_by_user_id(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->notify(new GeneralNotification('For User 1', 'Body'));
        $user2->notify(new GeneralNotification('For User 2', 'Body'));

        $result = $this->repository->getPaginated(null, null, (string) $user1->id);

        $this->assertCount(1, $result->items());
    }

    public function test_get_stats_returns_correct_shape(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test 1', 'Body'));
        $user->notify(new GeneralNotification('Test 2', 'Body'));

        DatabaseNotification::first()->markAsRead();

        $stats = $this->repository->getStats();

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('unread', $stats);
        $this->assertArrayHasKey('read', $stats);
        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['unread']);
        $this->assertEquals(1, $stats['read']);
    }

    public function test_find_or_fail_returns_notification(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test', 'Body'));

        $notification = DatabaseNotification::first();
        $found = $this->repository->findOrFail($notification->id);

        $this->assertEquals($notification->id, $found->id);
    }

    public function test_find_or_fail_throws_for_missing(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->findOrFail('nonexistent-uuid');
    }

    public function test_mark_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test', 'Body'));

        $notification = DatabaseNotification::first();
        $this->assertNull($notification->read_at);

        $this->repository->markAsRead($notification->id);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_delete_removes_notification(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test', 'Body'));

        $notification = DatabaseNotification::first();
        $this->repository->delete($notification->id);

        $this->assertEquals(0, DatabaseNotification::count());
    }

    public function test_delete_filtered_deletes_read_only(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test 1', 'Body'));
        $user->notify(new GeneralNotification('Test 2', 'Body'));

        DatabaseNotification::first()->markAsRead();

        $count = $this->repository->deleteFiltered('read');

        $this->assertEquals(1, $count);
        $this->assertEquals(1, DatabaseNotification::count());
    }
}
