<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\AdminNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

final class AdminNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AdminNotificationService::class);
    }

    public function test_get_index_data_returns_correct_shape(): void
    {
        $request = Request::create('/admin/notifications');

        $result = $this->service->getIndexData($request);

        $this->assertArrayHasKey('notifications', $result);
        $this->assertArrayHasKey('users', $result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertArrayHasKey('filters', $result);
    }

    public function test_get_index_data_stats_shape(): void
    {
        $request = Request::create('/admin/notifications');

        $result = $this->service->getIndexData($request);

        $this->assertArrayHasKey('total', $result['stats']);
        $this->assertArrayHasKey('unread', $result['stats']);
        $this->assertArrayHasKey('read', $result['stats']);
    }

    public function test_send_notification_dispatches_to_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $count = $this->service->sendNotification([
            'user_ids' => [$user1->id, $user2->id],
            'title' => 'Test Notification',
            'body' => 'This is a test',
        ]);

        $this->assertEquals(2, $count);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user1->id,
            'notifiable_type' => $user1->getMorphClass(),
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user2->id,
        ]);
    }

    public function test_send_notification_with_action_url(): void
    {
        $user = User::factory()->create();

        $this->service->sendNotification([
            'user_ids' => [$user->id],
            'title' => 'Click here',
            'body' => 'Visit this link',
            'action_url' => '/dashboard',
        ]);

        $notification = DatabaseNotification::first();
        $this->assertEquals('/dashboard', $notification->data['action_url']);
    }

    public function test_mark_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test', 'Body'));

        $notification = DatabaseNotification::first();
        $this->assertNull($notification->read_at);

        $this->service->markAsRead($notification->id);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_delete_notification(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test', 'Body'));

        $notification = DatabaseNotification::first();

        $this->service->deleteNotification($notification->id);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_delete_all_returns_count(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test 1', 'Body'));
        $user->notify(new GeneralNotification('Test 2', 'Body'));

        $count = $this->service->deleteAll(null);

        $this->assertEquals(2, $count);
        $this->assertEquals(0, DatabaseNotification::count());
    }
}
