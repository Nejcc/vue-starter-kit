<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NotificationsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_guests_cannot_access_notifications(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_renders_notifications_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('notifications/Index')
            ->has('notifications')
            ->has('filter')
            ->has('unreadCount')
        );
    }

    public function test_index_returns_paginated_notifications(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->user->notify(new GeneralNotification("Test {$i}", "Body {$i}"));
        }

        $response = $this->actingAs($this->user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('notifications.total', 20)
            ->where('notifications.per_page', 15)
        );
    }

    public function test_index_filters_unread_notifications(): void
    {
        $this->user->notify(new GeneralNotification('Unread', 'Body'));
        $this->user->notify(new GeneralNotification('Read', 'Body'));
        $this->user->notifications()->first()->markAsRead();

        $response = $this->actingAs($this->user)->get(route('notifications.index', ['filter' => 'unread']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('notifications.total', 1)
            ->where('filter', 'unread')
        );
    }

    public function test_index_filters_read_notifications(): void
    {
        $this->user->notify(new GeneralNotification('Unread', 'Body'));
        $this->user->notify(new GeneralNotification('Read', 'Body'));
        $this->user->notifications()->first()->markAsRead();

        $response = $this->actingAs($this->user)->get(route('notifications.index', ['filter' => 'read']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('notifications.total', 1)
            ->where('filter', 'read')
        );
    }

    public function test_recent_returns_json(): void
    {
        $this->user->notify(new GeneralNotification('Test', 'Body'));

        $response = $this->actingAs($this->user)
            ->getJson(route('notifications.recent'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'notifications',
            'unreadCount',
        ]);
        $response->assertJsonCount(1, 'notifications');
    }

    public function test_mark_as_read_marks_notification(): void
    {
        $this->user->notify(new GeneralNotification('Test', 'Body'));
        $notification = $this->user->notifications()->first();

        $response = $this->actingAs($this->user)
            ->patch(route('notifications.mark-as-read', $notification->id));

        $response->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read(): void
    {
        $this->user->notify(new GeneralNotification('Test 1', 'Body 1'));
        $this->user->notify(new GeneralNotification('Test 2', 'Body 2'));

        $response = $this->actingAs($this->user)
            ->post(route('notifications.mark-all-read'));

        $response->assertRedirect();
        $this->assertSame(0, $this->user->unreadNotifications()->count());
    }

    public function test_destroy_deletes_notification(): void
    {
        $this->user->notify(new GeneralNotification('Test', 'Body'));
        $notification = $this->user->notifications()->first();

        $response = $this->actingAs($this->user)
            ->delete(route('notifications.destroy', $notification->id));

        $response->assertRedirect();
        $this->assertSame(0, $this->user->notifications()->count());
    }

    public function test_users_cannot_access_other_users_notifications(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->notify(new GeneralNotification('Secret', 'Body'));
        $notification = $otherUser->notifications()->first();

        // Try to mark as read - should not affect the notification
        $this->actingAs($this->user)
            ->patch(route('notifications.mark-as-read', $notification->id));

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_users_cannot_delete_other_users_notifications(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->notify(new GeneralNotification('Secret', 'Body'));
        $notification = $otherUser->notifications()->first();

        $this->actingAs($this->user)
            ->delete(route('notifications.destroy', $notification->id));

        $this->assertSame(1, $otherUser->notifications()->count());
    }

    public function test_inertia_shares_unread_count(): void
    {
        $this->user->notify(new GeneralNotification('Test', 'Body'));

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('notifications.unreadCount', 1)
        );
    }
}
