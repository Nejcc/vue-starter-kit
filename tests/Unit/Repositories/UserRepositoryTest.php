<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository;
    }

    public function test_can_find_user_by_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $found = $this->repository->findByEmail('test@example.com');

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->id, $found->id);
        $this->assertEquals('test@example.com', $found->email);
    }

    public function test_can_find_user_by_id(): void
    {
        $user = User::factory()->create();

        $found = $this->repository->findById($user->id);

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_can_create_user(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ];

        $user = $this->repository->createUser($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_can_update_user(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $result = $this->repository->updateUser($user->id, ['name' => 'New Name']);

        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);
    }

    public function test_can_delete_user(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->deleteUser($user->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_can_update_password(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->updatePassword($user->id, 'new_password');

        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    public function test_find_by_email_returns_null_for_non_existent_email(): void
    {
        $found = $this->repository->findByEmail('nonexistent@example.com');

        $this->assertNull($found);
    }

    public function test_find_by_id_returns_null_for_non_existent_id(): void
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    public function test_update_user_returns_false_for_non_existent_id(): void
    {
        $result = $this->repository->updateUser(99999, ['name' => 'Test']);

        $this->assertFalse($result);
    }

    public function test_delete_user_returns_false_for_non_existent_id(): void
    {
        $result = $this->repository->deleteUser(99999);

        $this->assertFalse($result);
    }

    public function test_update_password_returns_false_for_non_existent_id(): void
    {
        $result = $this->repository->updatePassword(99999, 'new_password');

        $this->assertFalse($result);
    }

    public function test_find_by_email_matches_exact_case(): void
    {
        $user = User::factory()->create(['email' => 'Test@Example.com']);

        // Find with exact case
        $found = $this->repository->findByEmail('Test@Example.com');

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }
}
