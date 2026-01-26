<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guests cannot access admin users index.
     */
    public function test_guests_cannot_access_users_index(): void
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that regular users cannot access admin users index.
     */
    public function test_regular_users_cannot_access_users_index(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    /**
     * Test that super-admin can access users index.
     */
    public function test_super_admin_can_access_users_index(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Users/Index')
            ->has('users.data')
            ->has('users.links')
            ->has('users.meta')
            ->has('filters')
        );
    }

    /**
     * Test that admin role can access users index.
     */
    public function test_admin_can_access_users_index(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $admin->assignRole($adminRole);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Users/Index')
        );
    }

    /**
     * Test that users index displays all users.
     */
    public function test_users_index_displays_all_users(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $users = User::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Users/Index')
            ->has('users.data', 6) // 5 + admin
        );
    }

    /**
     * Test that users can be searched by name.
     */
    public function test_can_search_users_by_name(): void
    {
        $admin = User::factory()->create(['name' => 'Admin User']);
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Users/Index')
            ->has('users.data', 1)
            ->where('users.data.0.name', 'John Doe')
            ->where('filters.search', 'John')
        );
    }

    /**
     * Test that users can be searched by email.
     */
    public function test_can_search_users_by_email(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $user1 = User::factory()->create(['email' => 'john@example.com']);
        $user2 = User::factory()->create(['email' => 'jane@example.com']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'john@']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Users/Index')
            ->has('users.data', 1)
            ->where('users.data.0.email', 'john@example.com')
        );
    }

    /**
     * Test that guests cannot access user create form.
     */
    public function test_guests_cannot_access_user_create_form(): void
    {
        $response = $this->get(route('admin.users.create'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that regular users cannot access user create form.
     */
    public function test_regular_users_cannot_access_user_create_form(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $response = $this->actingAs($user)->get(route('admin.users.create'));

        $response->assertStatus(403);
    }

    /**
     * Test that super-admin can access user create form.
     */
    public function test_super_admin_can_access_user_create_form(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $response = $this->actingAs($admin)->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Users/Create')
            ->has('roles')
        );
    }

    /**
     * Test that guests cannot create users.
     */
    public function test_guests_cannot_create_users(): void
    {
        Role::create(['name' => RoleNames::USER]);

        $response = $this->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [RoleNames::USER],
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('users', ['email' => 'newuser@example.com']);
    }

    /**
     * Test that regular users cannot create users.
     */
    public function test_regular_users_cannot_create_users(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $response = $this->actingAs($user)->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [RoleNames::USER],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'newuser@example.com']);
    }

    /**
     * Test that super-admin can create users.
     */
    public function test_super_admin_can_create_users(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $userRole = Role::create(['name' => RoleNames::USER]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [RoleNames::USER],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status', 'User created successfully.');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);

        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($newUser->hasRole(RoleNames::USER));
    }

    /**
     * Test that user creation requires valid data.
     */
    public function test_user_creation_requires_valid_data(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    /**
     * Test that user creation requires unique email.
     */
    public function test_user_creation_requires_unique_email(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test that user creation requires password confirmation.
     */
    public function test_user_creation_requires_password_confirmation(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Test that users can be created without roles.
     */
    public function test_can_create_users_without_roles(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertCount(0, $newUser->roles);
    }

    /**
     * Test that users can be created with multiple roles.
     */
    public function test_can_create_users_with_multiple_roles(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $userRole = Role::create(['name' => RoleNames::USER]);
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [RoleNames::USER, RoleNames::ADMIN],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($newUser->hasRole(RoleNames::USER));
        $this->assertTrue($newUser->hasRole(RoleNames::ADMIN));
    }

    // ========================================
    // EDIT TESTS
    // ========================================

    /**
     * Test that guests cannot access user edit form.
     */
    public function test_guests_cannot_access_user_edit_form(): void
    {
        $user = User::factory()->create();

        $response = $this->get(route('admin.users.edit', $user));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that regular users cannot access user edit form.
     */
    public function test_regular_users_cannot_access_user_edit_form(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $targetUser = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.users.edit', $targetUser));

        $response->assertStatus(403);
    }

    /**
     * Test that super-admin can access user edit form.
     */
    public function test_super_admin_can_access_user_edit_form(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $targetUser = User::factory()->create(['name' => 'Target User']);

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $targetUser));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Users/Edit')
            ->where('user.name', 'Target User')
            ->has('roles')
        );
    }

    // ========================================
    // UPDATE TESTS
    // ========================================

    /**
     * Test that guests cannot update users.
     */
    public function test_guests_cannot_update_users(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        $response = $this->patch(route('admin.users.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Original Name']);
    }

    /**
     * Test that regular users cannot update users.
     */
    public function test_regular_users_cannot_update_users(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $targetUser = User::factory()->create(['name' => 'Original Name']);

        $response = $this->actingAs($user)->patch(route('admin.users.update', $targetUser), [
            'name' => 'Updated Name',
            'email' => $targetUser->email,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $targetUser->id, 'name' => 'Original Name']);
    }

    /**
     * Test that super-admin can update users.
     */
    public function test_super_admin_can_update_users(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $targetUser = User::factory()->create(['name' => 'Original Name']);

        $response = $this->actingAs($admin)->patch(route('admin.users.update', $targetUser), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status', 'User updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test that password is only updated when provided.
     */
    public function test_password_only_updated_when_provided(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $targetUser = User::factory()->create();
        $originalPassword = $targetUser->password;

        $response = $this->actingAs($admin)->patch(route('admin.users.update', $targetUser), [
            'name' => 'Updated Name',
            'email' => $targetUser->email,
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $targetUser->refresh();
        $this->assertEquals($originalPassword, $targetUser->password);
    }

    /**
     * Test that password can be updated.
     */
    public function test_password_can_be_updated(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $targetUser = User::factory()->create();
        $originalPassword = $targetUser->password;

        $response = $this->actingAs($admin)->patch(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $targetUser->refresh();
        $this->assertNotEquals($originalPassword, $targetUser->password);
    }

    /**
     * Test that user roles can be updated.
     */
    public function test_user_roles_can_be_updated(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $userRole = Role::create(['name' => RoleNames::USER]);
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);

        $targetUser = User::factory()->create();
        $targetUser->assignRole(RoleNames::USER);

        $response = $this->actingAs($admin)->patch(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'roles' => [RoleNames::ADMIN],
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $targetUser->refresh();
        $this->assertFalse($targetUser->hasRole(RoleNames::USER));
        $this->assertTrue($targetUser->hasRole(RoleNames::ADMIN));
    }

    /**
     * Test that update validates unique email.
     */
    public function test_update_validates_unique_email(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $targetUser = User::factory()->create(['email' => 'original@example.com']);

        $response = $this->actingAs($admin)->patch(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test that update allows same email.
     */
    public function test_update_allows_same_email(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $targetUser = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->actingAs($admin)->patch(route('admin.users.update', $targetUser), [
            'name' => 'Updated Name',
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
            'email' => 'test@example.com',
        ]);
    }

    // ========================================
    // DELETE TESTS
    // ========================================

    /**
     * Test that guests cannot delete users.
     */
    public function test_guests_cannot_delete_users(): void
    {
        $user = User::factory()->create();

        $response = $this->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * Test that regular users cannot delete users.
     */
    public function test_regular_users_cannot_delete_users(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $targetUser = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.users.destroy', $targetUser));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $targetUser->id]);
    }

    /**
     * Test that super-admin can delete users.
     */
    public function test_super_admin_can_delete_users(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $targetUser));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('status', 'User deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    /**
     * Test that users cannot delete themselves.
     */
    public function test_users_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create();
        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($superAdminRole);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'You cannot delete your own account.');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
