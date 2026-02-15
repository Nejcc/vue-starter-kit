<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\SessionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SessionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SessionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SessionRepository();
    }

    public function test_get_user_sessions_returns_sessions(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'sess-1',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'TestAgent/1.0',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $sessions = $this->repository->getUserSessions($user->id);

        $this->assertCount(1, $sessions);
        $this->assertEquals('sess-1', $sessions->first()->id);
        $this->assertEquals('127.0.0.1', $sessions->first()->ip_address);
    }

    public function test_get_user_sessions_returns_empty_when_none(): void
    {
        $user = User::factory()->create();

        $sessions = $this->repository->getUserSessions($user->id);

        $this->assertCount(0, $sessions);
    }

    public function test_delete_session_removes_correct_session(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            ['id' => 'sess-keep', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Agent', 'payload' => 'p', 'last_activity' => now()->timestamp],
            ['id' => 'sess-delete', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Agent', 'payload' => 'p', 'last_activity' => now()->timestamp],
        ]);

        $this->repository->deleteSession('sess-delete', $user->id);

        $this->assertDatabaseHas('sessions', ['id' => 'sess-keep']);
        $this->assertDatabaseMissing('sessions', ['id' => 'sess-delete']);
    }

    public function test_delete_all_except_current_keeps_only_current(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            ['id' => 'current', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Agent', 'payload' => 'p', 'last_activity' => now()->timestamp],
            ['id' => 'other-1', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Agent', 'payload' => 'p', 'last_activity' => now()->timestamp],
            ['id' => 'other-2', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Agent', 'payload' => 'p', 'last_activity' => now()->timestamp],
        ]);

        $this->repository->deleteAllExceptCurrent($user->id, 'current');

        $sessions = $this->repository->getUserSessions($user->id);
        $this->assertCount(1, $sessions);
        $this->assertEquals('current', $sessions->first()->id);
    }
}
