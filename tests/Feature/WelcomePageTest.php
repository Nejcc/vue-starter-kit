<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WelcomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_can_be_rendered(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_welcome_page_renders_correct_inertia_component(): void
    {
        $response = $this->get(route('home'));

        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
        );
    }

    public function test_welcome_page_has_can_register_prop(): void
    {
        $response = $this->get(route('home'));

        $response->assertInertia(fn ($page) => $page
            ->has('canRegister')
        );
    }

    public function test_can_register_defaults_to_false_when_no_settings_exist(): void
    {
        $response = $this->get(route('home'));

        $response->assertInertia(fn ($page) => $page
            ->where('canRegister', false)
        );
    }

    public function test_guests_can_access_the_welcome_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
        );
    }

    public function test_authenticated_users_can_access_the_welcome_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
        );
    }

    public function test_welcome_page_has_route_name_home(): void
    {
        $this->assertTrue(
            app('router')->has('home'),
            'Expected a route named "home" to be defined.'
        );

        $route = app('router')->getRoutes()->getByName('home');

        $this->assertNotNull($route);
        $this->assertSame('/', $route->uri());
    }
}
