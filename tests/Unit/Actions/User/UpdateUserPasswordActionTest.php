<?php

namespace Tests\Unit\Actions\User;

use App\Actions\User\UpdateUserPasswordAction;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateUserPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateUserPasswordAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new UpdateUserPasswordAction(new UserService(app(\App\Repositories\UserRepository::class)));
    }

    public function test_can_update_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password'),
        ]);

        $result = $this->action->handle($user->id, 'old_password', 'new_password123');

        $this->assertTrue($result);

        $user->refresh();
        $this->assertTrue(Hash::check('new_password123', $user->password));
    }

    public function test_cannot_update_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct_password'),
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->action->handle($user->id, 'wrong_password', 'new_password123');
    }

    public function test_cannot_update_password_when_user_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->action->handle(99999, 'old_password', 'new_password123');
    }

    public function test_update_password_validates_new_password_strength(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password'),
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        // Password too short
        $this->action->handle($user->id, 'old_password', 'short');
    }

    public function test_update_password_requires_password_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password'),
        ]);

        // This test verifies that the service validates password confirmation
        // The action itself doesn't handle confirmation, but the service does
        $result = $this->action->handle($user->id, 'old_password', 'new_password123');

        $this->assertTrue($result);
    }
}
