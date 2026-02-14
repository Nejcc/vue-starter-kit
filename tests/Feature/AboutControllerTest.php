<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AboutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_is_accessible(): void
    {
        $this->get(route('about'))
            ->assertOk();
    }

    public function test_about_page_returns_correct_inertia_component(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('About')
            );
    }

    public function test_about_page_is_accessible_without_authentication(): void
    {
        $this->assertGuest();

        $this->get(route('about'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('About')
            );
    }

    public function test_about_page_is_accessible_with_authentication(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('about'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('About')
            );
    }

    public function test_about_page_does_not_redirect_guests(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertDontSee('Redirecting');
    }

    public function test_about_route_has_correct_name(): void
    {
        $this->assertStringEndsWith('/about', route('about'));
    }

    public function test_about_page_returns_successful_response_via_url(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('About')
            );
    }
}
