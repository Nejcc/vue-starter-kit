<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\SessionManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SessionManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private SessionManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SessionManagementService::class);
    }

    public function test_get_user_sessions_returns_parsed_data(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'session-abc',
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'payload' => 'test-payload',
            'last_activity' => now()->timestamp,
        ]);

        $sessions = $this->service->getUserSessions($user->id, 'other-session');

        $this->assertCount(1, $sessions);
        $this->assertEquals('session-abc', $sessions[0]['id']);
        $this->assertEquals('192.168.1.1', $sessions[0]['ip_address']);
        $this->assertFalse($sessions[0]['is_current']);
        $this->assertArrayHasKey('device', $sessions[0]);
        $this->assertArrayHasKey('last_active', $sessions[0]);
        $this->assertArrayHasKey('last_active_at', $sessions[0]);
    }

    public function test_get_user_sessions_marks_current_session(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'current-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'TestAgent/1.0',
            'payload' => 'test-payload',
            'last_activity' => now()->timestamp,
        ]);

        $sessions = $this->service->getUserSessions($user->id, 'current-session');

        $this->assertTrue($sessions[0]['is_current']);
    }

    public function test_revoke_session_deletes_correct_session(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'revoke-me',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'TestAgent/1.0',
            'payload' => 'test-payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->service->revokeSession('revoke-me', $user->id);

        $this->assertDatabaseMissing('sessions', ['id' => 'revoke-me']);
    }

    public function test_revoke_all_other_sessions_keeps_current(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            ['id' => 'keep-this', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Agent', 'payload' => 'p', 'last_activity' => now()->timestamp],
            ['id' => 'remove-1', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Agent', 'payload' => 'p', 'last_activity' => now()->timestamp],
            ['id' => 'remove-2', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Agent', 'payload' => 'p', 'last_activity' => now()->timestamp],
        ]);

        $this->service->revokeAllOtherSessions($user->id, 'keep-this');

        $this->assertDatabaseHas('sessions', ['id' => 'keep-this']);
        $this->assertDatabaseMissing('sessions', ['id' => 'remove-1']);
        $this->assertDatabaseMissing('sessions', ['id' => 'remove-2']);
    }

    public function test_get_user_sessions_returns_empty_when_no_sessions(): void
    {
        $user = User::factory()->create();

        $sessions = $this->service->getUserSessions($user->id, 'nonexistent');

        $this->assertEmpty($sessions);
    }
}
