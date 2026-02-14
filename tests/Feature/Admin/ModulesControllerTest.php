<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ModulesControllerTest extends TestCase
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

    public function test_guests_cannot_access_modules_page(): void
    {
        $this->get(route('admin.modules.index'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_modules_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.modules.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_modules_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.modules.index'))
            ->assertOk();
    }

    public function test_admin_role_can_access_modules_page(): void
    {
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin)
            ->get(route('admin.modules.index'))
            ->assertOk();
    }

    public function test_modules_page_returns_correct_inertia_component(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.modules.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Modules/Index')
            );
    }

    public function test_modules_page_contains_modules_array(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.modules.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('modules')
            );
    }

    public function test_modules_array_includes_horizon_entry(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.modules.index'))
            ->assertOk();

        $modules = $response->inertiaProps('modules');
        $horizonModule = collect($modules)->firstWhere('key', 'horizon');

        $this->assertNotNull($horizonModule, 'Horizon module should be present in the modules array.');
        $this->assertSame('Horizon', $horizonModule['name']);
        $this->assertSame('laravel/horizon', $horizonModule['package']);
        $this->assertSame('Activity', $horizonModule['icon']);
        $this->assertFalse($horizonModule['required']);
    }
}
