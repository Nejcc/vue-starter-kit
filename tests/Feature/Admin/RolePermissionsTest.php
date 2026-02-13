<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'view-users']);
        Permission::create(['name' => 'edit-users']);
        Permission::create(['name' => 'delete-users']);

        Role::create(['name' => RoleNames::SUPER_ADMIN]);
        Role::create(['name' => RoleNames::ADMIN]);
        Role::create(['name' => RoleNames::USER]);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole(RoleNames::SUPER_ADMIN);

        $this->admin = User::factory()->create();
        $this->admin->assignRole(RoleNames::ADMIN);

        $this->user = User::factory()->create();
        $this->user->assignRole(RoleNames::USER);
    }

    // ========================================
    // AUTHORIZATION TESTS
    // ========================================

    public function test_guests_cannot_access_role_permissions(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);

        $response = $this->get(route('admin.roles.permissions', $role));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_role_permissions(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);

        $response = $this->actingAs($this->user)->get(route('admin.roles.permissions', $role));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_role_permissions(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);

        $response = $this->actingAs($this->admin)->get(route('admin.roles.permissions', $role));

        $response->assertOk();
    }

    public function test_super_admin_can_access_role_permissions(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.roles.permissions', $role));

        $response->assertOk();
    }

    // ========================================
    // PERMISSIONS PAGE TESTS
    // ========================================

    public function test_permissions_page_renders_correct_component(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.roles.permissions', $role));

        $response->assertInertia(fn ($page) => $page
            ->component('admin/Roles/Permissions')
            ->has('role')
            ->has('allPermissions', 3)
        );
    }

    public function test_permissions_page_shows_role_data(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);
        $role->givePermissionTo('view-users');

        $response = $this->actingAs($this->superAdmin)->get(route('admin.roles.permissions', $role));

        $response->assertInertia(fn ($page) => $page
            ->where('role.name', RoleNames::ADMIN)
            ->where('role.is_super_admin', false)
            ->where('role.users_count', 1)
            ->has('role.permissions', 1)
            ->where('role.permissions.0', 'view-users')
        );
    }

    public function test_super_admin_role_shows_is_super_admin_flag(): void
    {
        $role = Role::findByName(RoleNames::SUPER_ADMIN);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.roles.permissions', $role));

        $response->assertInertia(fn ($page) => $page
            ->where('role.is_super_admin', true)
        );
    }

    // ========================================
    // UPDATE PERMISSIONS TESTS
    // ========================================

    public function test_guests_cannot_update_role_permissions(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);

        $response = $this->patch(route('admin.roles.permissions.update', $role), [
            'permissions' => ['view-users'],
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_update_role_permissions(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);

        $response = $this->actingAs($this->user)->patch(route('admin.roles.permissions.update', $role), [
            'permissions' => ['view-users'],
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_sync_role_permissions(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);

        $response = $this->actingAs($this->admin)->patch(route('admin.roles.permissions.update', $role), [
            'permissions' => ['view-users', 'edit-users'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Permissions updated successfully.');

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('view-users'));
        $this->assertTrue($role->hasPermissionTo('edit-users'));
    }

    public function test_sync_replaces_existing_role_permissions(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);
        $role->givePermissionTo(['view-users', 'edit-users', 'delete-users']);

        $response = $this->actingAs($this->superAdmin)->patch(route('admin.roles.permissions.update', $role), [
            'permissions' => ['view-users'],
        ]);

        $response->assertRedirect();

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('view-users'));
        $this->assertFalse($role->hasPermissionTo('edit-users'));
        $this->assertFalse($role->hasPermissionTo('delete-users'));
    }

    public function test_sync_with_empty_array_clears_all_permissions(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);
        $role->givePermissionTo(['view-users', 'edit-users']);

        $response = $this->actingAs($this->superAdmin)->patch(route('admin.roles.permissions.update', $role), [
            'permissions' => [],
        ]);

        $response->assertRedirect();

        $role->refresh();
        $this->assertCount(0, $role->permissions);
    }

    public function test_sync_validates_permission_names_exist(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);

        $response = $this->actingAs($this->superAdmin)->patch(route('admin.roles.permissions.update', $role), [
            'permissions' => ['non-existent-permission'],
        ]);

        $response->assertSessionHasErrors('permissions.0');
    }

    public function test_sync_on_super_admin_role_is_rejected(): void
    {
        $role = Role::findByName(RoleNames::SUPER_ADMIN);

        $response = $this->actingAs($this->superAdmin)->patch(route('admin.roles.permissions.update', $role), [
            'permissions' => ['view-users'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_sync_creates_audit_log(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);

        $this->actingAs($this->superAdmin)->patch(route('admin.roles.permissions.update', $role), [
            'permissions' => ['view-users'],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'role.permissions_synced',
            'auditable_type' => Role::class,
            'auditable_id' => $role->id,
        ]);
    }
}
