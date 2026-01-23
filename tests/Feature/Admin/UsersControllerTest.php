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
}
