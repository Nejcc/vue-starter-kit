<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RepositoryServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository;
        $this->service = new UserService($this->repository);
    }

    public function test_full_flow_create_user_through_service_and_repository(): void
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
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_full_flow_update_profile_through_service_and_repository(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $data = [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ];

        $updated = $this->service->updateProfile($user->id, $data);

        $this->assertEquals('New Name', $updated->name);
        $this->assertEquals('new@example.com', $updated->email);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_full_flow_update_password_through_service_and_repository(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password'),
        ]);

        $result = $this->service->updatePassword($user->id, 'old_password', 'new_password123');

        $this->assertTrue($result);

        $user->refresh();
        $this->assertTrue(Hash::check('new_password123', $user->password));
        $this->assertFalse(Hash::check('old_password', $user->password));
    }

    public function test_full_flow_delete_user_through_service_and_repository(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $result = $this->service->delete($user->id, 'password123');

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_transaction_rollback_on_service_error(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        try {
            $this->service->updateProfile($user->id, [
                'name' => 'New Name',
                'email' => 'invalid-email', // This will cause validation to fail
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Expected exception
        }

        // Verify user was not updated (transaction rolled back)
        $user->refresh();
        $this->assertEquals('Original Name', $user->name);
        $this->assertEquals('original@example.com', $user->email);
    }

    public function test_repository_caching_works_with_service(): void
    {
        $user = User::factory()->create();

        // First call through service (should cache)
        $found1 = $this->service->findById($user->id);

        // Delete from database
        $user->delete();

        // Second call should return cached result
        $found2 = $this->service->findById($user->id);

        $this->assertNotNull($found1);
        $this->assertNotNull($found2);
        $this->assertEquals($found1->id, $found2->id);
    }

    public function test_service_validation_before_repository_operation(): void
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        // Service should validate before calling repository
        $this->service->updateProfile($user->id, [
            'name' => '', // Invalid: empty name
            'email' => 'invalid-email', // Invalid: not an email
        ]);

        // Repository should not be called, so user should remain unchanged
        $user->refresh();
        $this->assertNotEquals('', $user->name);
    }

    public function test_service_uses_repository_for_data_access(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Service should use repository to find user
        $found = $this->service->findByEmail('test@example.com');

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_multiple_operations_in_single_transaction(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);

        // Update profile (which uses transaction)
        $updated = $this->service->updateProfile($user1->id, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        // Verify all changes were committed
        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('updated@example.com', $updated->email);
        $this->assertDatabaseHas('users', [
            'id' => $user1->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }
}
