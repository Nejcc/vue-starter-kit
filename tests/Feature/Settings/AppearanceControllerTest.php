<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AppearanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_appearance_settings(): void
    {
        $response = $this->get(route('appearance.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_appearance_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('appearance.edit'));

        $response->assertOk();
    }

    public function test_appearance_page_returns_correct_inertia_component(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('appearance.edit'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('settings/Appearance')
        );
    }

    public function test_appearance_page_does_not_accept_post_requests(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('appearance.edit'));

        $response->assertStatus(405);
    }

    public function test_appearance_page_does_not_accept_patch_requests(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('appearance.edit'));

        $response->assertStatus(405);
    }

    public function test_appearance_page_does_not_accept_put_requests(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('appearance.edit'));

        $response->assertStatus(405);
    }

    public function test_appearance_page_does_not_accept_delete_requests(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('appearance.edit'));

        $response->assertStatus(405);
    }
}
