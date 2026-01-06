<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService(new UserRepository());
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

    public function test_update_profile_with_invalid_data_throws_exception(): void
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->service->updateProfile($user->id, [
            'name' => '',
            'email' => 'invalid-email',
        ]);
    }

    public function test_update_profile_with_duplicate_email_throws_exception(): void
    {
        $user1 = User::factory()->create(['email' => 'existing@example.com']);
        $user2 = User::factory()->create(['email' => 'other@example.com']);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->service->updateProfile($user2->id, [
            'name' => 'Test Name',
            'email' => 'existing@example.com',
        ]);
    }

    public function test_delete_with_wrong_password_throws_exception(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct_password')]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->service->delete($user->id, 'wrong_password');
    }

    public function test_delete_with_non_existent_user_throws_exception(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->delete(99999, 'password');
    }

    public function test_update_password_with_validation_failure_throws_exception(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old_password')]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        // Password too short
        $this->service->updatePassword($user->id, 'old_password', 'short');
    }

    public function test_update_password_with_non_existent_user_throws_exception(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->updatePassword(99999, 'old_password', 'new_password123');
    }

    public function test_update_profile_with_non_existent_user_throws_exception(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->updateProfile(99999, [
            'name' => 'Test Name',
            'email' => 'test@example.com',
        ]);
    }

    public function test_create_user_with_duplicate_email_throws_exception(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->service->create([
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ]);
    }

    public function test_create_user_with_weak_password_throws_exception(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->service->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123', // Too short
        ]);
    }
}
