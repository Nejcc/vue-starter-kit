<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\DeleteUserAction;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

final class DeleteUserActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new DeleteUserAction(new UserService(app(\App\Repositories\UserRepository::class)));
    }

    public function test_can_delete_user_with_correct_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $result = $this->action->handle($user->id, 'password123');

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_cannot_delete_user_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->action->handle($user->id, 'wrong_password');
    }

    public function test_delete_user_invalidates_session_when_request_provided(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $session = Mockery::mock(\Illuminate\Session\Store::class);

        $request->shouldReceive('session')->andReturn($session);
        $session->shouldReceive('invalidate')->once();
        $session->shouldReceive('regenerateToken')->once();

        $this->action->handle($user->id, 'password123', $request);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_delete_user_does_not_invalidate_session_when_no_request(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $result = $this->action->handle($user->id, 'password123');

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_delete_user_returns_false_when_user_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->action->handle(99999, 'password123');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
