<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ImpersonateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that users can be listed for impersonation.
     */
    public function test_can_list_users_for_impersonation(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/impersonate');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Impersonate/Index')
            ->has('users', 1)
            ->where('users.0.id', $otherUser->id)
        );
    }

    /**
     * Test that current user is excluded from impersonation list.
     */
    public function test_current_user_is_excluded_from_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/impersonate');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Impersonate/Index')
            ->has('users', 0)
        );
    }

    /**
     * Test that users can be searched.
     */
    public function test_can_search_users(): void
    {
        $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $otherUser = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->actingAs($user)
            ->get('/impersonate?search=Jane');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Impersonate/Index')
            ->has('users', 1)
            ->where('users.0.name', 'Jane Smith')
        );
    }

    /**
     * Test that a user can impersonate another user.
     */
    public function test_can_impersonate_user(): void
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/impersonate', [
                'user_id' => $targetUser->id,
            ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($targetUser);
        $this->assertEquals($user->id, session('impersonator_id'));
    }

    /**
     * Test that a user cannot impersonate themselves.
     */
    public function test_cannot_impersonate_self(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/impersonate', [
                'user_id' => $user->id,
            ]);

        $response->assertSessionHasErrors(['user_id']);
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test that impersonation requires authentication.
     */
    public function test_impersonation_requires_authentication(): void
    {
        $targetUser = User::factory()->create();

        $response = $this->post('/impersonate', [
            'user_id' => $targetUser->id,
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test that user can stop impersonating.
     */
    public function test_can_stop_impersonating(): void
    {
        $originalUser = User::factory()->create();
        $impersonatedUser = User::factory()->create();

        // Start impersonation
        $this->actingAs($originalUser)
            ->post('/impersonate', [
                'user_id' => $impersonatedUser->id,
            ]);

        $this->assertAuthenticatedAs($impersonatedUser);

        // Stop impersonation
        $response = $this->delete('/impersonate');

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($originalUser);
        $this->assertNull(session('impersonator_id'));
    }
}
