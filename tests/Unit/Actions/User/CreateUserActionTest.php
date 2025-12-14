<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\User;

use App\Actions\User\CreateUserAction;
use App\Contracts\Services\UserServiceInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user_action(): void
    {
        $userService = app(UserServiceInterface::class);
        $action = new CreateUserAction($userService);

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $result = $action->handle($data);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('John Doe', $result->name);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }
}
