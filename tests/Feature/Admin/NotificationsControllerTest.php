<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\Role;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NotificationsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($superAdminRole);
    }

    public function test_guests_cannot_access_admin_notifications(): void
    {
        $this->get(route('admin.notifications.index'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_admin_notifications(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.notifications.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_notifications_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Notifications/Index')
                ->has('notifications')
                ->has('users')
                ->has('stats')
                ->has('filters')
            );
    }

    public function test_notifications_index_shows_all_users_notifications(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->notify(new GeneralNotification('Test 1', 'Body 1'));
        $user2->notify(new GeneralNotification('Test 2', 'Body 2'));

        $this->actingAs($this->admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('notifications.total', 2)
            );
    }

    public function test_can_filter_by_unread(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Unread', 'Body'));
        $user->notify(new GeneralNotification('Read', 'Body'));

        $user->notifications()->latest()->first()->markAsRead();

        $this->actingAs($this->admin)
            ->get(route('admin.notifications.index', ['filter' => 'unread']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('notifications.total', 1)
            );
    }

    public function test_can_filter_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1->notify(new GeneralNotification('User1 Notif', 'Body'));
        $user2->notify(new GeneralNotification('User2 Notif', 'Body'));

        $this->actingAs($this->admin)
            ->get(route('admin.notifications.index', ['user_id' => $user1->id]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('notifications.total', 1)
            );
    }

    public function test_can_search_notifications(): void
    {
        $user = User::factory()->create();
        $uniqueTitle = 'UniqueSearch' . uniqid();
        $user->notify(new GeneralNotification($uniqueTitle, 'Body'));
        $user->notify(new GeneralNotification('Other Title', 'Body'));

        $this->actingAs($this->admin)
            ->get(route('admin.notifications.index', ['search' => $uniqueTitle]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('notifications.total', 1)
            );
    }

    public function test_admin_can_send_notification(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.notifications.send'), [
                'user_ids' => [$user->id],
                'title' => 'Test Notification',
                'body' => 'This is a test notification.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
        ]);
    }

    public function test_send_notification_to_multiple_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.notifications.send'), [
                'user_ids' => [$user1->id, $user2->id],
                'title' => 'Broadcast',
                'body' => 'Message for everyone.',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_send_notification_validates_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.notifications.send'), [])
            ->assertSessionHasErrors(['user_ids', 'title', 'body']);
    }

    public function test_admin_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test', 'Body'));
        $notification = $user->notifications()->first();

        $this->assertNull($notification->read_at);

        $this->actingAs($this->admin)
            ->patch(route('admin.notifications.mark-as-read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_admin_can_delete_notification(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Test', 'Body'));
        $notification = $user->notifications()->first();

        $this->actingAs($this->admin)
            ->delete(route('admin.notifications.destroy', $notification->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_admin_can_delete_all_read_notifications(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Read Notif', 'Body'));
        $user->notify(new GeneralNotification('Unread Notif', 'Body'));

        $user->notifications()->oldest()->first()->markAsRead();

        $this->actingAs($this->admin)
            ->delete(route('admin.notifications.destroy-all', ['filter' => 'read']))
            ->assertRedirect();

        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_stats_are_correct(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Notif 1', 'Body'));
        $user->notify(new GeneralNotification('Notif 2', 'Body'));
        $user->notify(new GeneralNotification('Notif 3', 'Body'));

        $user->notifications()->oldest()->first()->markAsRead();

        $this->actingAs($this->admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('stats.total', 3)
                ->where('stats.read', 1)
                ->where('stats.unread', 2)
            );
    }
}
