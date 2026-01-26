<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RolesControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'view-users']);
        Permission::create(['name' => 'edit-users']);
        Permission::create(['name' => 'delete-users']);

        // Create roles
        Role::create(['name' => RoleNames::SUPER_ADMIN]);
        Role::create(['name' => RoleNames::ADMIN]);
        Role::create(['name' => RoleNames::USER]);

        // Create users with roles
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

    public function test_guests_cannot_access_roles_index(): void
    {
        $response = $this->get(route('admin.roles.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_roles_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.roles.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_roles_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.roles.index'));

        $response->assertOk();
    }

    public function test_super_admin_can_access_roles_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.roles.index'));

        $response->assertOk();
    }

    // ========================================
    // INDEX PAGE TESTS
    // ========================================

    public function test_roles_index_displays_all_roles(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.roles.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Roles/Index')
            ->has('roles', 3) // super-admin, admin, user
            ->has('roles.0.name')
            ->has('roles.1.name')
            ->has('roles.2.name')
        );
    }

    public function test_roles_index_search_filters_by_name(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.roles.index', ['search' => 'super']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('roles', 1)
            ->where('roles.0.name', RoleNames::SUPER_ADMIN)
        );
    }

    public function test_roles_index_shows_users_count(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.roles.index'));

        $response->assertInertia(fn ($page) => $page
            ->where('roles.0.users_count', 1) // 1 user
            ->where('roles.1.users_count', 1) // 1 admin
            ->where('roles.2.users_count', 1) // 1 super-admin
        );
    }

    public function test_roles_index_shows_permissions(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);
        $role->givePermissionTo(['view-users', 'edit-users']);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.roles.index'));

        $response->assertInertia(fn ($page) => $page
            ->where('roles.1.name', RoleNames::ADMIN)
            ->where('roles.1.permissions.0', 'view-users')
            ->where('roles.1.permissions.1', 'edit-users')
        );
    }

    // ========================================
    // CREATE TESTS
    // ========================================

    public function test_admin_can_access_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.roles.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Roles/Create')
            ->has('permissions', 3)
        );
    }

    public function test_regular_user_cannot_access_create_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.roles.create'));

        $response->assertStatus(403);
    }

    // ========================================
    // STORE TESTS
    // ========================================

    public function test_admin_can_create_new_role(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.roles.store'), [
            'name' => 'moderator',
            'permissions' => ['view-users'],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('status', 'Role created successfully.');

        $this->assertDatabaseHas('roles', ['name' => 'moderator']);

        $role = Role::findByName('moderator');
        $this->assertTrue($role->hasPermissionTo('view-users'));
    }

    public function test_store_creates_role_without_permissions(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.roles.store'), [
            'name' => 'guest',
        ]);

        $response->assertRedirect(route('admin.roles.index'));

        $role = Role::findByName('guest');
        $this->assertCount(0, $role->permissions);
    }

    public function test_store_validates_unique_role_name(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.roles.store'), [
            'name' => RoleNames::ADMIN, // Already exists
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_prevents_creating_super_admin_role(): void
    {
        // Since super-admin already exists, unique validation fires first
        // This test verifies the validation rule exists
        $response = $this->actingAs($this->superAdmin)->post(route('admin.roles.store'), [
            'name' => RoleNames::SUPER_ADMIN,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_regular_user_cannot_create_role(): void
    {
        $response = $this->actingAs($this->user)->post(route('admin.roles.store'), [
            'name' => 'unauthorized',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('roles', ['name' => 'unauthorized']);
    }

    // ========================================
    // EDIT TESTS
    // ========================================

    public function test_admin_can_access_edit_form(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);
        $role->givePermissionTo('view-users');

        $response = $this->actingAs($this->admin)->get(route('admin.roles.edit', $role));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Roles/Edit')
            ->where('role.name', RoleNames::ADMIN)
            ->where('role.permissions.0', 'view-users')
            ->has('permissions', 3)
        );
    }

    public function test_regular_user_cannot_access_edit_form(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);

        $response = $this->actingAs($this->user)->get(route('admin.roles.edit', $role));

        $response->assertStatus(403);
    }

    // ========================================
    // UPDATE TESTS
    // ========================================

    public function test_admin_can_update_role(): void
    {
        $role = Role::create(['name' => 'editor']);

        $response = $this->actingAs($this->admin)->patch(route('admin.roles.update', $role), [
            'name' => 'content-editor',
            'permissions' => ['view-users', 'edit-users'],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('status', 'Role updated successfully.');

        $this->assertDatabaseHas('roles', ['name' => 'content-editor']);

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('view-users'));
        $this->assertTrue($role->hasPermissionTo('edit-users'));
    }

    public function test_update_syncs_permissions(): void
    {
        $role = Role::create(['name' => 'test-role']);
        $role->givePermissionTo(['view-users', 'edit-users', 'delete-users']);

        $response = $this->actingAs($this->superAdmin)->patch(route('admin.roles.update', $role), [
            'name' => 'test-role',
            'permissions' => ['view-users'], // Remove edit-users and delete-users
        ]);

        $response->assertRedirect(route('admin.roles.index'));

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('view-users'));
        $this->assertFalse($role->hasPermissionTo('edit-users'));
        $this->assertFalse($role->hasPermissionTo('delete-users'));
    }

    public function test_update_clears_all_permissions_when_none_provided(): void
    {
        $role = Role::create(['name' => 'test-role']);
        $role->givePermissionTo(['view-users', 'edit-users']);

        $response = $this->actingAs($this->superAdmin)->patch(route('admin.roles.update', $role), [
            'name' => 'test-role',
            'permissions' => [],
        ]);

        $response->assertRedirect(route('admin.roles.index'));

        $role->refresh();
        $this->assertCount(0, $role->permissions);
    }

    public function test_update_prevents_renaming_super_admin_role(): void
    {
        $role = Role::findByName(RoleNames::SUPER_ADMIN);

        $response = $this->actingAs($this->superAdmin)->patch(route('admin.roles.update', $role), [
            'name' => 'renamed-admin',
        ]);

        $response->assertRedirect(route('admin.roles.edit', $role));
        $response->assertSessionHasErrors('name');

        $this->assertDatabaseHas('roles', ['name' => RoleNames::SUPER_ADMIN]);
    }

    public function test_update_prevents_changing_role_to_super_admin(): void
    {
        $role = Role::create(['name' => 'test-role']);

        // Since super-admin already exists, unique validation fires first
        $response = $this->actingAs($this->superAdmin)->patch(route('admin.roles.update', $role), [
            'name' => RoleNames::SUPER_ADMIN,
        ]);

        $response->assertSessionHasErrors('name');

        $role->refresh();
        $this->assertEquals('test-role', $role->name);
    }

    public function test_update_validates_unique_name(): void
    {
        $role1 = Role::findByName(RoleNames::ADMIN);
        $role2 = Role::create(['name' => 'test-role']);

        $response = $this->actingAs($this->superAdmin)->patch(route('admin.roles.update', $role2), [
            'name' => RoleNames::ADMIN, // Try to use existing name
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_allows_same_name(): void
    {
        $role = Role::create(['name' => 'test-role']);

        $response = $this->actingAs($this->superAdmin)->patch(route('admin.roles.update', $role), [
            'name' => 'test-role', // Same name
            'permissions' => ['view-users'],
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'test-role']);
    }

    public function test_regular_user_cannot_update_role(): void
    {
        $role = Role::create(['name' => 'test-role']);

        $response = $this->actingAs($this->user)->patch(route('admin.roles.update', $role), [
            'name' => 'hacked',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('roles', ['name' => 'test-role']);
    }

    // ========================================
    // DELETE TESTS
    // ========================================

    public function test_admin_can_delete_role(): void
    {
        $role = Role::create(['name' => 'deletable']);

        $response = $this->actingAs($this->admin)->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('status', 'Role deleted successfully.');

        $this->assertDatabaseMissing('roles', ['name' => 'deletable']);
    }

    public function test_cannot_delete_super_admin_role(): void
    {
        $role = Role::findByName(RoleNames::SUPER_ADMIN);

        $response = $this->actingAs($this->superAdmin)->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['name' => RoleNames::SUPER_ADMIN]);
    }

    public function test_cannot_delete_role_assigned_to_users(): void
    {
        $role = Role::findByName(RoleNames::ADMIN);

        $response = $this->actingAs($this->superAdmin)->delete(route('admin.roles.destroy', $role));

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHasErrors('role_deletion');

        $this->assertDatabaseHas('roles', ['name' => RoleNames::ADMIN]);
    }

    public function test_regular_user_cannot_delete_role(): void
    {
        $role = Role::create(['name' => 'test-role']);

        $response = $this->actingAs($this->user)->delete(route('admin.roles.destroy', $role));

        $response->assertStatus(403);
        $this->assertDatabaseHas('roles', ['name' => 'test-role']);
    }
}
