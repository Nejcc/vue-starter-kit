<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HealthControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($superAdminRole);
    }

    public function test_guests_cannot_access_health_page(): void
    {
        $this->get(route('admin.health.index'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_health_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.health.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_health_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.health.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Health/Index')
                ->has('checks')
                ->has('system')
            );
    }

    public function test_health_page_returns_checks_array(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.health.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('checks', 5)
                ->where('checks.0.name', 'Database')
                ->where('checks.1.name', 'Cache')
                ->where('checks.2.name', 'Storage')
                ->where('checks.3.name', 'Queue')
                ->where('checks.4.name', 'Scheduler')
            );
    }

    public function test_health_checks_have_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.health.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('checks.0', fn ($check) => $check
                    ->has('name')
                    ->has('status')
                    ->has('message')
                    ->has('details')
                )
            );
    }

    public function test_health_check_statuses_are_valid(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.health.index'));

        $response->assertOk();

        $checks = $response->original->getData()['page']['props']['checks'];
        $validStatuses = ['ok', 'warning', 'error'];

        foreach ($checks as $check) {
            $this->assertContains($check['status'], $validStatuses, "Check '{$check['name']}' has invalid status: {$check['status']}");
        }
    }

    public function test_database_check_returns_ok(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.health.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('checks.0.name', 'Database')
                ->where('checks.0.status', 'ok')
            );
    }

    public function test_cache_check_returns_ok(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.health.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('checks.1.name', 'Cache')
                ->where('checks.1.status', 'ok')
            );
    }

    public function test_storage_check_returns_valid_status(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.health.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('checks.2.name', 'Storage')
                ->where('checks.2.status', fn ($status) => in_array($status, ['ok', 'warning', 'error'], true))
            );
    }

    public function test_system_info_has_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.health.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('system.php_version')
                ->has('system.laravel_version')
                ->has('system.environment')
                ->has('system.debug_mode')
                ->has('system.timezone')
                ->has('system.locale')
                ->has('system.server_time')
            );
    }

    public function test_system_info_returns_correct_environment(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.health.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('system.environment', 'testing')
            );
    }

    public function test_system_info_returns_valid_php_version(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.health.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('system.php_version', PHP_VERSION)
            );
    }

    public function test_admin_role_can_access_health_page(): void
    {
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin)
            ->get(route('admin.health.index'))
            ->assertOk();
    }
}
