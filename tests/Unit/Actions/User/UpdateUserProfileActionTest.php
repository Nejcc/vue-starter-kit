<?php

namespace Tests\Unit\Actions\User;

use App\Actions\User\UpdateUserProfileAction;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserProfileActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateUserProfileAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new UpdateUserProfileAction(new UserService(app(\App\Repositories\UserRepository::class)));
    }

    public function test_can_update_user_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $data = [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ];

        $updated = $this->action->handle($user->id, $data);

        $this->assertInstanceOf(User::class, $updated);
        $this->assertEquals('New Name', $updated->name);
        $this->assertEquals('new@example.com', $updated->email);
    }

    public function test_update_profile_resets_email_verification_when_email_changes(): void
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified_at' => now(),
        ]);

        $data = [
            'name' => $user->name,
            'email' => 'new@example.com',
        ];

        $updated = $this->action->handle($user->id, $data);

        $this->assertNull($updated->email_verified_at);
    }

    public function test_update_profile_keeps_email_verification_when_email_unchanged(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);

        $data = [
            'name' => 'New Name',
            'email' => 'test@example.com',
        ];

        $updated = $this->action->handle($user->id, $data);

        $this->assertNotNull($updated->email_verified_at);
    }

    public function test_cannot_update_profile_with_invalid_email(): void
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $data = [
            'name' => 'Test Name',
            'email' => 'invalid-email',
        ];

        $this->action->handle($user->id, $data);
    }

    public function test_cannot_update_profile_with_duplicate_email(): void
    {
        $user1 = User::factory()->create(['email' => 'existing@example.com']);
        $user2 = User::factory()->create(['email' => 'other@example.com']);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $data = [
            'name' => 'Test Name',
            'email' => 'existing@example.com',
        ];

        $this->action->handle($user2->id, $data);
    }

    public function test_cannot_update_profile_when_user_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $data = [
            'name' => 'Test Name',
            'email' => 'test@example.com',
        ];

        $this->action->handle(99999, $data);
    }

    public function test_update_profile_requires_name(): void
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $data = [
            'email' => 'test@example.com',
        ];

        $this->action->handle($user->id, $data);
    }

    public function test_update_profile_requires_email(): void
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $data = [
            'name' => 'Test Name',
        ];

        $this->action->handle($user->id, $data);
    }
}
