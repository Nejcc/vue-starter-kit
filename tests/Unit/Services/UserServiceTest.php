<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService(new UserRepository);
    }

    public function test_can_create_user(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $user = $this->service->create($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_can_update_profile(): void
    {
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

        $updatedUser = $this->service->updateProfile($user->id, [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('new@example.com', $updatedUser->email);
        $this->assertNull($updatedUser->email_verified_at); // Should be reset when email changes
    }

    public function test_can_update_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old_password')]);

        $result = $this->service->updatePassword($user->id, 'old_password', 'new_password');

        $this->assertTrue($result);

        $user->refresh();
        $this->assertTrue(Hash::check('new_password', $user->password));
    }

    public function test_cannot_update_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct_password')]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->service->updatePassword($user->id, 'wrong_password', 'new_password');
    }

    public function test_can_find_user_by_id(): void
    {
        $user = User::factory()->create();

        $found = $this->service->findById($user->id);

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_can_find_user_by_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $found = $this->service->findByEmail('test@example.com');

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->id, $found->id);
    }
}
