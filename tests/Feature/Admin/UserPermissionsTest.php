<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserPermissionsTest extends TestCase
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

    public function test_guests_cannot_access_user_permissions(): void
    {
        $response = $this->get(route('admin.users.permissions', $this->user));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_user_permissions(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.users.permissions', $this->admin));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_user_permissions(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.permissions', $this->user));

        $response->assertOk();
    }

    public function test_super_admin_can_access_user_permissions(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.permissions', $this->user));

        $response->assertOk();
    }

    // ========================================
    // PERMISSIONS PAGE TESTS
    // ========================================

    public function test_permissions_page_renders_correct_component(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.permissions', $this->user));

        $response->assertInertia(fn ($page) => $page
            ->component('admin/Users/Permissions')
            ->has('user')
            ->has('allPermissions', 3)
        );
    }

    public function test_permissions_page_shows_direct_permissions(): void
    {
        $this->user->givePermissionTo('view-users');

        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.permissions', $this->user));

        $response->assertInertia(fn ($page) => $page
            ->where('user.direct_permissions.0', 'view-users')
            ->has('user.direct_permissions', 1)
        );
    }

    public function test_permissions_page_shows_role_permissions(): void
    {
        $adminRole = Role::findByName(RoleNames::ADMIN);
        $adminRole->givePermissionTo(['view-users', 'edit-users']);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.permissions', $this->admin));

        $response->assertInertia(fn ($page) => $page
            ->has('user.role_permissions', 2)
        );
    }

    public function test_permissions_page_shows_user_info(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.permissions', $this->user));

        $response->assertInertia(fn ($page) => $page
            ->where('user.name', $this->user->name)
            ->where('user.email', $this->user->email)
            ->where('user.slug', $this->user->slug)
            ->has('user.roles')
        );
    }

    // ========================================
    // UPDATE PERMISSIONS TESTS
    // ========================================

    public function test_guests_cannot_update_user_permissions(): void
    {
        $response = $this->patch(route('admin.users.permissions.update', $this->user), [
            'permissions' => ['view-users'],
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_update_user_permissions(): void
    {
        $response = $this->actingAs($this->user)->patch(route('admin.users.permissions.update', $this->admin), [
            'permissions' => ['view-users'],
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_sync_direct_permissions(): void
    {
        $response = $this->actingAs($this->admin)->patch(route('admin.users.permissions.update', $this->user), [
            'permissions' => ['view-users', 'edit-users'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Permissions updated successfully.');

        $this->user->refresh();
        $directPermissions = $this->user->getDirectPermissions()->pluck('name')->toArray();
        $this->assertContains('view-users', $directPermissions);
        $this->assertContains('edit-users', $directPermissions);
    }

    public function test_sync_replaces_existing_direct_permissions(): void
    {
        $this->user->givePermissionTo(['view-users', 'edit-users', 'delete-users']);

        $response = $this->actingAs($this->superAdmin)->patch(route('admin.users.permissions.update', $this->user), [
            'permissions' => ['view-users'],
        ]);

        $response->assertRedirect();

        $this->user->refresh();
        $directPermissions = $this->user->getDirectPermissions()->pluck('name')->toArray();
        $this->assertContains('view-users', $directPermissions);
        $this->assertNotContains('edit-users', $directPermissions);
        $this->assertNotContains('delete-users', $directPermissions);
    }

    public function test_sync_with_empty_array_clears_all_direct_permissions(): void
    {
        $this->user->givePermissionTo(['view-users', 'edit-users']);

        $response = $this->actingAs($this->superAdmin)->patch(route('admin.users.permissions.update', $this->user), [
            'permissions' => [],
        ]);

        $response->assertRedirect();

        $this->user->refresh();
        $this->assertCount(0, $this->user->getDirectPermissions());
    }

    public function test_sync_validates_permission_names_exist(): void
    {
        $response = $this->actingAs($this->superAdmin)->patch(route('admin.users.permissions.update', $this->user), [
            'permissions' => ['non-existent-permission'],
        ]);

        $response->assertSessionHasErrors('permissions.0');
    }

    public function test_sync_creates_audit_log(): void
    {
        $this->actingAs($this->superAdmin)->patch(route('admin.users.permissions.update', $this->user), [
            'permissions' => ['view-users'],
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.permissions_synced',
            'auditable_type' => User::class,
            'auditable_id' => $this->user->id,
        ]);
    }
}
