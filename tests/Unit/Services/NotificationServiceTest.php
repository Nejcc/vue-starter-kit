<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new NotificationService();
        $this->user = User::factory()->create();
    }

    public function test_get_unread_count_returns_zero_for_no_notifications(): void
    {
        $this->assertSame(0, $this->service->getUnreadCount($this->user));
    }

    public function test_get_unread_count_returns_correct_count(): void
    {
        $this->user->notify(new GeneralNotification('Test 1', 'Body 1'));
        $this->user->notify(new GeneralNotification('Test 2', 'Body 2'));

        $this->assertSame(2, $this->service->getUnreadCount($this->user));
    }

    public function test_get_unread_count_excludes_read_notifications(): void
    {
        $this->user->notify(new GeneralNotification('Test 1', 'Body 1'));
        $this->user->notify(new GeneralNotification('Test 2', 'Body 2'));

        $this->user->notifications()->first()->markAsRead();

        $this->assertSame(1, $this->service->getUnreadCount($this->user));
    }

    public function test_get_all_paginated_returns_all_notifications(): void
    {
        $this->user->notify(new GeneralNotification('Test 1', 'Body 1'));
        $this->user->notify(new GeneralNotification('Test 2', 'Body 2'));

        $result = $this->service->getAllPaginated($this->user);

        $this->assertSame(2, $result->total());
    }

    public function test_get_all_paginated_filters_unread(): void
    {
        $this->user->notify(new GeneralNotification('Test 1', 'Body 1'));
        $this->user->notify(new GeneralNotification('Test 2', 'Body 2'));

        $this->user->notifications()->first()->markAsRead();

        $result = $this->service->getAllPaginated($this->user, 'unread');

        $this->assertSame(1, $result->total());
    }

    public function test_get_all_paginated_filters_read(): void
    {
        $this->user->notify(new GeneralNotification('Test 1', 'Body 1'));
        $this->user->notify(new GeneralNotification('Test 2', 'Body 2'));

        $this->user->notifications()->first()->markAsRead();

        $result = $this->service->getAllPaginated($this->user, 'read');

        $this->assertSame(1, $result->total());
    }

    public function test_get_recent_returns_limited_notifications(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->user->notify(new GeneralNotification("Test {$i}", "Body {$i}"));
        }

        $result = $this->service->getRecent($this->user, 5);

        $this->assertCount(5, $result);
    }

    public function test_mark_as_read_marks_notification(): void
    {
        $this->user->notify(new GeneralNotification('Test', 'Body'));
        $notification = $this->user->notifications()->first();

        $this->assertNull($notification->read_at);

        $result = $this->service->markAsRead($this->user, $notification->id);

        $this->assertTrue($result);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_as_read_returns_false_for_invalid_id(): void
    {
        $result = $this->service->markAsRead($this->user, 'non-existent-id');

        $this->assertFalse($result);
    }

    public function test_mark_all_as_read(): void
    {
        $this->user->notify(new GeneralNotification('Test 1', 'Body 1'));
        $this->user->notify(new GeneralNotification('Test 2', 'Body 2'));

        $this->assertSame(2, $this->service->getUnreadCount($this->user));

        $this->service->markAllAsRead($this->user);

        $this->assertSame(0, $this->service->getUnreadCount($this->user));
    }

    public function test_delete_removes_notification(): void
    {
        $this->user->notify(new GeneralNotification('Test', 'Body'));
        $notification = $this->user->notifications()->first();

        $result = $this->service->delete($this->user, $notification->id);

        $this->assertTrue($result);
        $this->assertSame(0, $this->user->notifications()->count());
    }

    public function test_delete_returns_false_for_invalid_id(): void
    {
        $result = $this->service->delete($this->user, 'non-existent-id');

        $this->assertFalse($result);
    }

    public function test_general_notification_stores_correct_data(): void
    {
        $this->user->notify(new GeneralNotification(
            'Test Title',
            'Test Body',
            '/test-url',
            'bell',
        ));

        $notification = $this->user->notifications()->first();

        $this->assertSame('Test Title', $notification->data['title']);
        $this->assertSame('Test Body', $notification->data['body']);
        $this->assertSame('/test-url', $notification->data['action_url']);
        $this->assertSame('bell', $notification->data['icon']);
    }
}
