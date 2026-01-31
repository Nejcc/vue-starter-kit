<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DatabaseControllerTest extends TestCase
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

    public function test_guests_cannot_access_database_index(): void
    {
        $response = $this->get(route('admin.database.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_database_index(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $response = $this->actingAs($user)->get(route('admin.database.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_database_index(): void
    {
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        $response = $this->actingAs($adminUser)->get(route('admin.database.index'));

        // Without a connection param, redirects to databases listing
        $response->assertRedirect(route('admin.databases.index'));
    }

    public function test_super_admin_can_access_database_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.database.index'));

        $response->assertRedirect(route('admin.databases.index'));
    }

    // ─── Databases Listing ───────────────────────────────────────────

    public function test_guests_cannot_access_databases_listing(): void
    {
        $response = $this->get(route('admin.databases.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_databases_listing(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $response = $this->actingAs($user)->get(route('admin.databases.index'));

        $response->assertStatus(403);
    }

    public function test_databases_listing_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.databases.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Databases/Index')
            ->has('connections')
        );
    }

    public function test_databases_listing_shows_connection_info(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.databases.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Databases/Index')
            ->where('connections.0.name', fn ($name) => is_string($name))
            ->where('connections.0.driver', fn ($driver) => is_string($driver))
        );
    }

    // ─── Connection Index (tables list) ──────────────────────────────

    public function test_connection_index_renders_tables(): void
    {
        $defaultConnection = config('database.default');

        $response = $this->actingAs($this->admin)->get(route('admin.database.connection.index', $defaultConnection));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Database/Index')
            ->has('tables')
            ->has('connections')
            ->where('currentConnection', $defaultConnection)
            ->has('driver')
        );
    }

    public function test_connection_index_falls_back_to_default_for_invalid_connection(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.database.connection.index', 'nonexistent'));

        // Invalid connection falls back to default, which for testing is sqlite :memory:
        $defaultConnection = config('database.default');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('currentConnection', $defaultConnection)
        );
    }

    public function test_connection_index_shows_known_tables(): void
    {
        $defaultConnection = config('database.default');

        $response = $this->actingAs($this->admin)->get(route('admin.database.connection.index', $defaultConnection));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Database/Index')
            ->where('tables', fn ($tables) => collect($tables)->pluck('name')->contains('users'))
        );
    }

    // ─── Table Show ──────────────────────────────────────────────────

    public function test_guests_cannot_access_table_show(): void
    {
        $defaultConnection = config('database.default');

        $response = $this->get(route('admin.database.connection.show', [$defaultConnection, 'users']));

        $response->assertRedirect(route('login'));
    }

    public function test_table_show_renders_structure(): void
    {
        $defaultConnection = config('database.default');

        $response = $this->actingAs($this->admin)->get(route('admin.database.connection.show', [$defaultConnection, 'users']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Database/Show')
            ->where('table.name', 'users')
            ->has('table.columns')
            ->has('table.indexes')
            ->has('table.foreignKeys')
            ->has('table.rowCount')
            ->has('connections')
            ->where('currentConnection', $defaultConnection)
        );
    }

    public function test_table_show_returns_column_info(): void
    {
        $defaultConnection = config('database.default');

        $response = $this->actingAs($this->admin)->get(route('admin.database.connection.show', [$defaultConnection, 'users']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('table.columns', fn ($columns) => collect($columns)->pluck('name')->contains('email'))
        );
    }

    public function test_table_show_with_data_view(): void
    {
        User::factory()->count(3)->create();
        $defaultConnection = config('database.default');

        $response = $this->actingAs($this->admin)->get(route('admin.database.connection.show.view', [$defaultConnection, 'users', 'data']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Database/Show')
            ->where('view', 'data')
            ->has('table.data')
            ->has('table.pagination')
        );
    }

    public function test_table_show_data_view_supports_pagination(): void
    {
        User::factory()->count(30)->create();
        $defaultConnection = config('database.default');

        $response = $this->actingAs($this->admin)->get(route('admin.database.connection.show.view', [
            $defaultConnection,
            'users',
            'data',
            'per_page' => 10,
            'page' => 2,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('table.pagination.current_page', 2)
            ->where('table.pagination.per_page', 10)
        );
    }

    public function test_table_show_with_structure_view(): void
    {
        $defaultConnection = config('database.default');

        $response = $this->actingAs($this->admin)->get(route('admin.database.connection.show.view', [$defaultConnection, 'users', 'structure']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('view', 'structure')
        );
    }

    public function test_table_show_with_indexes_view(): void
    {
        $defaultConnection = config('database.default');

        $response = $this->actingAs($this->admin)->get(route('admin.database.connection.show.view', [$defaultConnection, 'users', 'indexes']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('view', 'indexes')
        );
    }

    public function test_table_show_invalid_view_treated_as_null(): void
    {
        $defaultConnection = config('database.default');

        $response = $this->actingAs($this->admin)->get(route('admin.database.connection.show.view', [$defaultConnection, 'users', 'invalid']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('view', null)
        );
    }

    public function test_table_show_nonexistent_table_returns_404(): void
    {
        $defaultConnection = config('database.default');

        $response = $this->actingAs($this->admin)->get(route('admin.database.connection.show', [$defaultConnection, 'nonexistent_table_xyz']));

        $response->assertStatus(404);
    }

    public function test_table_show_row_count_is_accurate(): void
    {
        // One admin user from setUp + 5 more
        User::factory()->count(5)->create();
        $defaultConnection = config('database.default');

        $response = $this->actingAs($this->admin)->get(route('admin.database.connection.show', [$defaultConnection, 'users']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('table.rowCount', 6)
        );
    }
}
