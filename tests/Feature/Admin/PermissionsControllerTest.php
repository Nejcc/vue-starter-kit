<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PermissionsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Role $superAdminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->superAdminRole);
    }

    // ─── Authorization ───────────────────────────────────────────────

    public function test_guests_cannot_access_permissions_index(): void
    {
        $response = $this->get(route('admin.permissions.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_permissions_index(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $response = $this->actingAs($user)->get(route('admin.permissions.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_permissions_index(): void
    {
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        $response = $this->actingAs($adminUser)->get(route('admin.permissions.index'));

        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_permissions_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.permissions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Permissions/Index')
            ->has('permissions.data')
            ->has('filters')
        );
    }

    public function test_guests_cannot_access_create_permission(): void
    {
        $response = $this->get(route('admin.permissions.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_create_permission(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $response = $this->actingAs($user)->post(route('admin.permissions.store'), [
            'name' => 'test-permission',
        ]);

        $response->assertStatus(403);
    }

    public function test_regular_users_cannot_update_permission(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $permission = Permission::create(['name' => 'test-permission']);

        $response = $this->actingAs($user)->patch(route('admin.permissions.update', $permission), [
            'name' => 'updated-permission',
        ]);

        $response->assertStatus(403);
    }

    // ─── Index ───────────────────────────────────────────────────────

    public function test_permissions_index_displays_all_permissions(): void
    {
        Permission::create(['name' => 'edit-posts', 'group_name' => 'Posts']);
        Permission::create(['name' => 'delete-posts', 'group_name' => 'Posts']);
        Permission::create(['name' => 'manage-users', 'group_name' => 'Users']);

        $response = $this->actingAs($this->admin)->get(route('admin.permissions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Permissions/Index')
            ->has('permissions.data', 3)
        );
    }

    public function test_permissions_index_search_filters_by_name(): void
    {
        Permission::create(['name' => 'edit-posts', 'group_name' => 'Posts']);
        Permission::create(['name' => 'manage-users', 'group_name' => 'Users']);

        $response = $this->actingAs($this->admin)->get(route('admin.permissions.index', ['search' => 'edit']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Permissions/Index')
            ->has('permissions.data', 1)
            ->where('permissions.data.0.name', 'edit-posts')
        );
    }

    public function test_permissions_index_search_filters_by_group_name(): void
    {
        Permission::create(['name' => 'edit-posts', 'group_name' => 'Posts']);
        Permission::create(['name' => 'manage-users', 'group_name' => 'Users']);

        $response = $this->actingAs($this->admin)->get(route('admin.permissions.index', ['search' => 'Users']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Permissions/Index')
            ->has('permissions.data', 1)
            ->where('permissions.data.0.name', 'manage-users')
        );
    }

    public function test_permissions_index_search_returns_empty_for_no_match(): void
    {
        Permission::create(['name' => 'edit-posts']);

        $response = $this->actingAs($this->admin)->get(route('admin.permissions.index', ['search' => 'nonexistent']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('permissions.data', 0)
        );
    }

    public function test_permissions_index_includes_role_counts(): void
    {
        $permission = Permission::create(['name' => 'edit-posts']);
        $role = Role::create(['name' => 'editor']);
        $role->givePermissionTo($permission);

        $response = $this->actingAs($this->admin)->get(route('admin.permissions.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('permissions.data.0.roles_count', 1)
            ->has('permissions.data.0.roles', 1)
        );
    }

    // ─── Create ──────────────────────────────────────────────────────

    public function test_create_permission_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.permissions.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Permissions/Create')
        );
    }

    // ─── Store ───────────────────────────────────────────────────────

    public function test_admin_can_store_permission(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.permissions.store'), [
            'name' => 'new-permission',
            'group_name' => 'Testing',
        ]);

        $response->assertRedirect(route('admin.permissions.index'));
        $response->assertSessionHas('status', 'Permission created successfully.');
        $this->assertDatabaseHas('permissions', [
            'name' => 'new-permission',
            'group_name' => 'Testing',
        ]);
    }

    public function test_admin_can_store_permission_without_group(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.permissions.store'), [
            'name' => 'ungrouped-permission',
        ]);

        $response->assertRedirect(route('admin.permissions.index'));
        $this->assertDatabaseHas('permissions', [
            'name' => 'ungrouped-permission',
            'group_name' => null,
        ]);
    }

    public function test_store_permission_requires_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.permissions.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_permission_name_must_be_unique(): void
    {
        Permission::create(['name' => 'existing-permission']);

        $response = $this->actingAs($this->admin)->post(route('admin.permissions.store'), [
            'name' => 'existing-permission',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_permission_name_max_length(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.permissions.store'), [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ─── Edit ────────────────────────────────────────────────────────

    public function test_edit_permission_page_renders(): void
    {
        $permission = Permission::create(['name' => 'test-permission', 'group_name' => 'Test']);

        $response = $this->actingAs($this->admin)->get(route('admin.permissions.edit', $permission));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Permissions/Edit')
            ->where('permission.id', $permission->id)
            ->where('permission.name', 'test-permission')
            ->where('permission.group_name', 'Test')
        );
    }

    // ─── Update ──────────────────────────────────────────────────────

    public function test_admin_can_update_permission(): void
    {
        $permission = Permission::create(['name' => 'old-name', 'group_name' => 'Old']);

        $response = $this->actingAs($this->admin)->patch(route('admin.permissions.update', $permission), [
            'name' => 'new-name',
            'group_name' => 'New',
        ]);

        $response->assertRedirect(route('admin.permissions.index'));
        $response->assertSessionHas('status', 'Permission updated successfully.');
        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'name' => 'new-name',
            'group_name' => 'New',
        ]);
    }

    public function test_update_permission_name_must_be_unique_except_self(): void
    {
        Permission::create(['name' => 'other-permission']);
        $permission = Permission::create(['name' => 'my-permission']);

        $response = $this->actingAs($this->admin)->patch(route('admin.permissions.update', $permission), [
            'name' => 'other-permission',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_permission_can_keep_same_name(): void
    {
        $permission = Permission::create(['name' => 'keep-name']);

        $response = $this->actingAs($this->admin)->patch(route('admin.permissions.update', $permission), [
            'name' => 'keep-name',
            'group_name' => 'Updated Group',
        ]);

        $response->assertRedirect(route('admin.permissions.index'));
        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'name' => 'keep-name',
            'group_name' => 'Updated Group',
        ]);
    }

    public function test_update_permission_requires_name(): void
    {
        $permission = Permission::create(['name' => 'test-permission']);

        $response = $this->actingAs($this->admin)->patch(route('admin.permissions.update', $permission), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_permission_can_clear_group_name(): void
    {
        $permission = Permission::create(['name' => 'test-permission', 'group_name' => 'Old Group']);

        $response = $this->actingAs($this->admin)->patch(route('admin.permissions.update', $permission), [
            'name' => 'test-permission',
            'group_name' => null,
        ]);

        $response->assertRedirect(route('admin.permissions.index'));
        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'group_name' => null,
        ]);
    }
}
