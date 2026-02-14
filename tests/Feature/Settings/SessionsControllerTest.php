<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SessionsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Insert a fake session row for a given user.
     */
    private function insertSession(int $userId, ?string $id = null, ?string $ipAddress = null, ?string $userAgent = null, ?int $lastActivity = null): string
    {
        $sessionId = $id ?? fake()->sha256();

        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $userId,
            'ip_address' => $ipAddress ?? '127.0.0.1',
            'user_agent' => $userAgent ?? 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'payload' => base64_encode(serialize([])),
            'last_activity' => $lastActivity ?? time(),
        ]);

        return $sessionId;
    }

    public function test_guests_cannot_access_sessions_page(): void
    {
        $response = $this->get(route('sessions.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_sessions_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('sessions.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/Sessions')
            ->has('sessions')
            ->has('currentSessionId'));
    }

    public function test_sessions_page_shows_user_sessions(): void
    {
        $user = User::factory()->create();

        $this->insertSession($user->id, 'session-abc', '192.168.1.1');

        $response = $this->actingAs($user)->get(route('sessions.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/Sessions')
            ->has('sessions', fn ($sessions) => $sessions->has(0, fn ($session) => $session
                ->where('id', 'session-abc')
                ->where('ip_address', '192.168.1.1')
                ->etc())->etc()));
    }

    public function test_sessions_page_shows_multiple_sessions(): void
    {
        $user = User::factory()->create();

        $this->insertSession($user->id, 'session-1', '192.168.1.1');
        $this->insertSession($user->id, 'session-2', '10.0.0.1');

        $response = $this->actingAs($user)->get(route('sessions.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/Sessions')
            ->has('sessions', 2));
    }

    public function test_sessions_do_not_include_other_users_sessions(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->insertSession($user->id, 'my-session');
        $this->insertSession($otherUser->id, 'other-user-session');

        $response = $this->actingAs($user)->get(route('sessions.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/Sessions')
            ->has('sessions', 1));
    }

    public function test_session_data_includes_expected_fields(): void
    {
        $user = User::factory()->create();

        $this->insertSession($user->id, 'test-session-fields', '192.168.1.100', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        $response = $this->actingAs($user)->get(route('sessions.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/Sessions')
            ->has('sessions.0', fn ($session) => $session
                ->has('id')
                ->has('ip_address')
                ->has('is_current')
                ->has('last_active')
                ->has('last_active_at')
                ->has('device.browser')
                ->has('device.platform')
                ->has('device.is_desktop')
                ->has('device.is_mobile')));
    }

    public function test_user_agent_parsing_detects_chrome_on_linux(): void
    {
        $user = User::factory()->create();

        $this->insertSession($user->id, 'chrome-linux-session', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

        $response = $this->actingAs($user)->get(route('sessions.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/Sessions')
            ->has('sessions', fn ($sessions) => $sessions->has(0, fn ($session) => $session
                ->where('device.browser', 'Chrome')
                ->where('device.platform', 'Linux')
                ->where('device.is_desktop', true)
                ->etc())->etc()));
    }

    public function test_user_agent_parsing_detects_firefox_on_windows(): void
    {
        $user = User::factory()->create();

        $this->insertSession($user->id, 'firefox-windows-session', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0');

        $response = $this->actingAs($user)->get(route('sessions.index'));

        $response->assertInertia(fn ($page) => $page->component('settings/Sessions')
            ->has('sessions', fn ($sessions) => $sessions->has(0, fn ($session) => $session
                ->where('device.browser', 'Firefox')
                ->where('device.platform', 'Windows')
                ->etc())->etc()));
    }

    public function test_user_agent_parsing_detects_safari_on_macos(): void
    {
        $user = User::factory()->create();

        $this->insertSession($user->id, 'safari-mac-session', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15');

        $response = $this->actingAs($user)->get(route('sessions.index'));

        $response->assertInertia(fn ($page) => $page->component('settings/Sessions')
            ->has('sessions', fn ($sessions) => $sessions->has(0, fn ($session) => $session
                ->where('device.browser', 'Safari')
                ->where('device.platform', 'macOS')
                ->etc())->etc()));
    }

    public function test_user_agent_parsing_detects_mobile_device(): void
    {
        $user = User::factory()->create();

        $this->insertSession($user->id, 'mobile-session', '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1');

        $response = $this->actingAs($user)->get(route('sessions.index'));

        $response->assertInertia(fn ($page) => $page->component('settings/Sessions')
            ->has('sessions', fn ($sessions) => $sessions->has(0, fn ($session) => $session
                ->where('device.platform', 'iOS')
                ->where('device.is_mobile', true)
                ->where('device.is_desktop', false)
                ->etc())->etc()));
    }

    public function test_user_agent_parsing_detects_edge_browser(): void
    {
        $user = User::factory()->create();

        $this->insertSession($user->id, 'edge-session', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0');

        $response = $this->actingAs($user)->get(route('sessions.index'));

        $response->assertInertia(fn ($page) => $page->component('settings/Sessions')
            ->has('sessions', fn ($sessions) => $sessions->has(0, fn ($session) => $session
                ->where('device.browser', 'Edge')
                ->where('device.platform', 'Windows')
                ->etc())->etc()));
    }

    public function test_user_agent_parsing_handles_unknown_agent(): void
    {
        $user = User::factory()->create();

        $this->insertSession($user->id, 'unknown-session', '127.0.0.1', 'curl/7.88.1');

        $response = $this->actingAs($user)->get(route('sessions.index'));

        $response->assertInertia(fn ($page) => $page->component('settings/Sessions')
            ->has('sessions', fn ($sessions) => $sessions->has(0, fn ($session) => $session
                ->where('device.browser', 'Unknown')
                ->where('device.platform', 'Unknown')
                ->etc())->etc()));
    }

    public function test_user_can_revoke_other_session(): void
    {
        $user = User::factory()->create();

        $sessionId = $this->insertSession($user->id, 'revokable-session');

        $response = $this->actingAs($user)->delete(route('sessions.destroy', $sessionId), [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Session revoked successfully.');

        $this->assertDatabaseMissing('sessions', ['id' => $sessionId]);
    }

    public function test_user_cannot_revoke_session_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $sessionId = $this->insertSession($user->id, 'protected-session');

        $response = $this->actingAs($user)->delete(route('sessions.destroy', $sessionId), [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertDatabaseHas('sessions', ['id' => $sessionId]);
    }

    public function test_revoke_requires_password(): void
    {
        $user = User::factory()->create();

        $sessionId = $this->insertSession($user->id, 'requires-pw-session');

        $response = $this->actingAs($user)->delete(route('sessions.destroy', $sessionId), [
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertDatabaseHas('sessions', ['id' => $sessionId]);
    }

    public function test_user_cannot_revoke_another_users_session(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $sessionId = $this->insertSession($otherUser->id, 'other-user-session-to-revoke');

        $response = $this->actingAs($user)->delete(route('sessions.destroy', $sessionId), [
            'password' => 'password',
        ]);

        // The session should still exist because the query scopes to the authenticated user
        $this->assertDatabaseHas('sessions', ['id' => $sessionId]);
    }

    public function test_user_can_revoke_all_other_sessions(): void
    {
        $user = User::factory()->create();

        $session1 = $this->insertSession($user->id, 'session-1');
        $session2 = $this->insertSession($user->id, 'session-2');

        $response = $this->actingAs($user)->delete(route('sessions.destroyAll'), [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'All other sessions have been revoked.');

        $this->assertDatabaseMissing('sessions', ['id' => $session1]);
        $this->assertDatabaseMissing('sessions', ['id' => $session2]);
    }

    public function test_revoke_all_requires_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('sessions.destroyAll'), [
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_revoke_all_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->insertSession($user->id, 'session-to-keep');

        $response = $this->actingAs($user)->delete(route('sessions.destroyAll'), [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertDatabaseHas('sessions', ['id' => 'session-to-keep']);
    }

    public function test_guests_cannot_revoke_sessions(): void
    {
        $response = $this->delete(route('sessions.destroy', 'some-session'), [
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_revoke_all_sessions(): void
    {
        $response = $this->delete(route('sessions.destroyAll'), [
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_sessions_ordered_by_most_recent(): void
    {
        $user = User::factory()->create();

        $this->insertSession($user->id, 'old-session', '10.0.0.1', null, time() - 3600);
        $this->insertSession($user->id, 'new-session', '10.0.0.2', null, time() + 60);

        $response = $this->actingAs($user)->get(route('sessions.index'));

        $response->assertInertia(function ($page): void {
            $page->component('settings/Sessions');
            $sessions = $page->toArray()['props']['sessions'];

            $sessionIds = array_column($sessions, 'id');
            $newIndex = array_search('new-session', $sessionIds);
            $oldIndex = array_search('old-session', $sessionIds);

            $this->assertLessThan($oldIndex, $newIndex, 'Newest session should appear before older session');
        });
    }

    public function test_revoke_all_does_not_affect_other_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->insertSession($user->id, 'my-session');
        $this->insertSession($otherUser->id, 'their-session');

        $response = $this->actingAs($user)->delete(route('sessions.destroyAll'), [
            'password' => 'password',
        ]);

        $response->assertRedirect();

        // Other user's session should remain
        $this->assertDatabaseHas('sessions', ['id' => 'their-session']);
        // My session should be deleted (it's not the "current" session since we use array driver)
        $this->assertDatabaseMissing('sessions', ['id' => 'my-session']);
    }
}
