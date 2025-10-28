<?php

namespace Tests\Unit\Actions\User;

use App\Actions\User\CreateUserAction;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CreateUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user_action(): void
    {
        $userService = Mockery::mock(UserService::class);
        $action = new CreateUserAction($userService);

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $expectedUser = User::factory()->make($data);

        $userService->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expectedUser);

        $result = $action->handle($data);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('John Doe', $result->name);
        $this->assertEquals('john@example.com', $result->email);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
