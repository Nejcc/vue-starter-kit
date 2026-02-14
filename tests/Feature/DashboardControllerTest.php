<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_unverified_users_can_access_dashboard(): void
    {
        // User model does not implement MustVerifyEmail, so the verified
        // middleware allows unverified users through.
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_verified_users_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_dashboard_renders_correct_inertia_component(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
        );
    }

    public function test_dashboard_returns_200_status(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
    }

    public function test_post_method_is_not_allowed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('dashboard'));

        $response->assertStatus(405);
    }

    public function test_put_method_is_not_allowed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('dashboard'));

        $response->assertStatus(405);
    }

    public function test_patch_method_is_not_allowed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('dashboard'));

        $response->assertStatus(405);
    }

    public function test_delete_method_is_not_allowed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('dashboard'));

        $response->assertStatus(405);
    }
}
