<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_appearance_settings(): void
    {
        $response = $this->get(route('appearance.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_appearance_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('appearance.edit'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('settings/Appearance')
        );
    }
}
