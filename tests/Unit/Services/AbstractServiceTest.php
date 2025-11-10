<?php

namespace Tests\Unit\Services;

use App\Contracts\RepositoryInterface;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\AbstractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Test service implementation for testing AbstractService.
 */
class TestService extends AbstractService
{
    public function __construct(RepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Expose protected method for testing.
     */
    public function testTransaction(callable $callback): mixed
    {
        return $this->transaction($callback);
    }

    /**
     * Expose protected method for testing.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    public function testValidate(array $data, array $rules): array
    {
        return $this->validate($data, $rules);
    }

    /**
     * Expose protected method for testing.
     */
    public function testGetRepository(): RepositoryInterface
    {
        return $this->getRepository();
    }
}

class AbstractServiceTest extends TestCase
{
    use RefreshDatabase;

    private TestService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TestService(new UserRepository);
    }

    public function test_can_access_repository(): void
    {
        $repository = $this->service->testGetRepository();

        $this->assertInstanceOf(RepositoryInterface::class, $repository);
    }

    public function test_transaction_commits_on_success(): void
    {
        $user = $this->service->testTransaction(function () {
            return User::factory()->create([
                'name' => 'Transaction User',
                'email' => 'transaction@example.com',
            ]);
        });

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'email' => 'transaction@example.com',
        ]);
    }

    public function test_transaction_rolls_back_on_exception(): void
    {
        $this->expectException(\Exception::class);

        try {
            $this->service->testTransaction(function () {
                $user = User::factory()->create([
                    'name' => 'Should Rollback',
                    'email' => 'rollback@example.com',
                ]);

                throw new \Exception('Test exception');

                return $user;
            });
        } catch (\Exception $e) {
            // Verify the user was not created (transaction rolled back)
            $this->assertDatabaseMissing('users', [
                'email' => 'rollback@example.com',
            ]);

            throw $e;
        }
    }

    public function test_transaction_returns_value(): void
    {
        $result = $this->service->testTransaction(function () {
            return 'test value';
        });

        $this->assertEquals('test value', $result);
    }

    public function test_validate_passes_with_valid_data(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $rules = [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
        ];

        $validated = $this->service->testValidate($data, $rules);

        $this->assertEquals($data, $validated);
    }

    public function test_validate_throws_exception_with_invalid_data(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'name' => '',
            'email' => 'invalid-email',
        ];

        $rules = [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
        ];

        $this->service->testValidate($data, $rules);
    }

    public function test_validate_returns_only_validated_fields(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'extra_field' => 'should not be in result',
        ];

        $rules = [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
        ];

        $validated = $this->service->testValidate($data, $rules);

        $this->assertArrayHasKey('name', $validated);
        $this->assertArrayHasKey('email', $validated);
        $this->assertArrayNotHasKey('extra_field', $validated);
    }

    public function test_validate_with_nested_rules(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $validated = $this->service->testValidate($data, $rules);

        $this->assertArrayHasKey('name', $validated);
        $this->assertArrayHasKey('email', $validated);
        $this->assertArrayHasKey('password', $validated);
    }

    public function test_transaction_can_nest_operations(): void
    {
        $result = $this->service->testTransaction(function () {
            $user1 = User::factory()->create(['email' => 'user1@example.com']);

            return $this->service->testTransaction(function () use ($user1) {
                $user2 = User::factory()->create(['email' => 'user2@example.com']);

                return [$user1, $user2];
            });
        });

        $this->assertCount(2, $result);
        $this->assertDatabaseHas('users', ['email' => 'user1@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'user2@example.com']);
    }

    public function test_validate_with_custom_messages(): void
    {
        $this->expectException(ValidationException::class);

        $data = ['email' => 'invalid'];

        $rules = ['email' => ['required', 'email']];

        try {
            $this->service->testValidate($data, $rules);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
            throw $e;
        }
    }
}
