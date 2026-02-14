<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Constants\RoleNames;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class QuickLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The quick-login and quick-register routes are only registered when
     * APP_ENV=local. In the testing environment they should not exist.
     */

    /**
     * Test that quick-login route is not accessible in non-local environment.
     */
    public function test_quick_login_route_not_available_in_testing_environment(): void
    {
        $response = $this->post('/quick-login/user');

        $response->assertNotFound();
    }

    /**
     * Test that quick-register route is not accessible in non-local environment.
     */
    public function test_quick_register_route_not_available_in_testing_environment(): void
    {
        $response = $this->post('/quick-register/user');

        $response->assertNotFound();
    }

    /**
     * Test that quick-login named route is not registered in non-local environment.
     */
    public function test_quick_login_named_route_not_registered(): void
    {
        $this->assertFalse(Route::has('quick-login'));
    }

    /**
     * Test that quick-register named route is not registered in non-local environment.
     */
    public function test_quick_register_named_route_not_registered(): void
    {
        $this->assertFalse(Route::has('quick-register'));
    }

    /**
     * Test that quick-login returns 404 for all allowed roles in non-local environment.
     */
    public function test_quick_login_returns_404_for_all_roles_in_testing(): void
    {
        $roles = [RoleNames::SUPER_ADMIN, RoleNames::ADMIN, RoleNames::USER];

        foreach ($roles as $role) {
            $response = $this->post("/quick-login/{$role}");
            $response->assertNotFound();
        }
    }

    /**
     * Test that quick-register returns 404 for all allowed roles in non-local environment.
     */
    public function test_quick_register_returns_404_for_all_roles_in_testing(): void
    {
        $roles = [RoleNames::SUPER_ADMIN, RoleNames::ADMIN, RoleNames::USER];

        foreach ($roles as $role) {
            $response = $this->post("/quick-register/{$role}");
            $response->assertNotFound();
        }
    }

    /**
     * Test that the application environment is not local during tests.
     */
    public function test_application_environment_is_not_local(): void
    {
        $this->assertFalse(app()->environment('local'));
        $this->assertTrue(app()->environment('testing'));
    }
}
