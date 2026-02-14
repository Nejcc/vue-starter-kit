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

    // ─── Authorization: Database Index ───────────────────────────────

    public function test_guests_cannot_access_database_index(): void
    {
        $this->get(route('admin.database.index'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_database_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.database.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_database_index(): void
    {
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        $this->actingAs($adminUser)
            ->get(route('admin.database.index'))
            ->assertRedirect(route('admin.databases.index'));
    }

    public function test_super_admin_can_access_database_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.index'))
            ->assertRedirect(route('admin.databases.index'));
    }

    // ─── Authorization: Database Connection Index ────────────────────

    public function test_guests_cannot_access_database_connection_index(): void
    {
        $this->get(route('admin.database.connection.index', ['connection' => 'sqlite']))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_database_connection_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.database.connection.index', ['connection' => 'sqlite']))
            ->assertForbidden();
    }

    public function test_admin_can_access_database_connection_index(): void
    {
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        $this->actingAs($adminUser)
            ->get(route('admin.database.connection.index', ['connection' => 'sqlite']))
            ->assertOk();
    }

    // ─── Authorization: Database Table Show ──────────────────────────

    public function test_guests_cannot_access_table_show(): void
    {
        $this->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_table_show(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertForbidden();
    }

    public function test_admin_can_access_table_show(): void
    {
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        $this->actingAs($adminUser)
            ->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertOk();
    }

    // ─── Authorization: Databases Listing ────────────────────────────

    public function test_guests_cannot_access_databases_listing(): void
    {
        $this->get(route('admin.databases.index'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_databases_listing(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.databases.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_databases_listing(): void
    {
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        $this->actingAs($adminUser)
            ->get(route('admin.databases.index'))
            ->assertOk();
    }

    // ─── Database Index (without connection) ─────────────────────────

    public function test_database_index_without_connection_redirects_to_databases_listing(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.index'))
            ->assertRedirect(route('admin.databases.index'));
    }

    public function test_database_index_with_connection_query_param_renders_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.index', ['connection' => 'sqlite']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Database/Index')
                ->has('tables')
                ->has('connections')
                ->has('currentConnection')
                ->has('driver')
            );
    }

    // ─── Connection Index (tables list) ──────────────────────────────

    public function test_connection_index_renders_tables(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.index', ['connection' => 'sqlite']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Database/Index')
                ->has('tables')
                ->has('connections')
                ->where('currentConnection', 'sqlite')
                ->where('driver', 'sqlite')
            );
    }

    public function test_connection_index_falls_back_to_default_for_invalid_connection(): void
    {
        $defaultConnection = config('database.default');

        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.index', ['connection' => 'nonexistent']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('currentConnection', $defaultConnection)
            );
    }

    public function test_connection_index_shows_users_table(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.index', ['connection' => 'sqlite']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('tables', fn ($tables) => collect($tables)->pluck('name')->contains('users'))
            );
    }

    public function test_connection_index_tables_have_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.index', ['connection' => 'sqlite']))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $tables = $page->toArray()['props']['tables'];
                $this->assertNotEmpty($tables);

                foreach ($tables as $table) {
                    $this->assertArrayHasKey('name', $table);
                    $this->assertArrayHasKey('rows', $table);
                    $this->assertArrayHasKey('size', $table);
                }
            });
    }

    public function test_connection_index_returns_connections_list(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.index', ['connection' => 'sqlite']))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $connections = $page->toArray()['props']['connections'];
                $this->assertIsArray($connections);
                $this->assertNotEmpty($connections);
                $this->assertContains('sqlite', $connections);
            });
    }

    public function test_connection_index_shows_correct_row_counts(): void
    {
        User::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.index', ['connection' => 'sqlite']))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $tables = $page->toArray()['props']['tables'];
                $usersTable = collect($tables)->firstWhere('name', 'users');
                $this->assertNotNull($usersTable);
                // admin from setUp + 3 factory users = 4
                $this->assertEquals(4, $usersTable['rows']);
            });
    }

    // ─── Table Show ──────────────────────────────────────────────────

    public function test_table_show_renders_structure(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Database/Show')
                ->where('table.name', 'users')
                ->has('table.columns')
                ->has('table.indexes')
                ->has('table.foreignKeys')
                ->has('table.rowCount')
                ->has('table.data')
                ->has('table.pagination')
                ->has('connections')
                ->where('currentConnection', 'sqlite')
                ->where('driver', 'sqlite')
                ->has('view')
            );
    }

    public function test_table_show_returns_column_info_for_users_table(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $columns = $page->toArray()['props']['table']['columns'];
                $this->assertNotEmpty($columns);
                $columnNames = array_column($columns, 'name');
                $this->assertContains('id', $columnNames);
                $this->assertContains('email', $columnNames);
                $this->assertContains('name', $columnNames);
            });
    }

    public function test_table_show_columns_have_required_attributes(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $columns = $page->toArray()['props']['table']['columns'];
                $this->assertNotEmpty($columns);

                foreach ($columns as $column) {
                    $this->assertArrayHasKey('name', $column);
                    $this->assertArrayHasKey('type', $column);
                    $this->assertArrayHasKey('nullable', $column);
                    $this->assertArrayHasKey('default', $column);
                    $this->assertArrayHasKey('primary', $column);
                }
            });
    }

    public function test_table_show_row_count_is_accurate(): void
    {
        User::factory()->count(5)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                // admin from setUp + 5 factory users = 6
                ->where('table.rowCount', 6)
            );
    }

    public function test_table_show_without_view_has_null_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('view', null)
            );
    }

    public function test_table_show_without_data_view_returns_empty_data(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $table = $page->toArray()['props']['table'];
                $this->assertEmpty($table['data']);
                $this->assertNull($table['pagination']);
            });
    }

    public function test_table_show_nonexistent_table_returns_404(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'nonexistent_table_xyz']))
            ->assertNotFound();
    }

    public function test_table_show_invalid_connection_falls_back_to_default(): void
    {
        $defaultConnection = config('database.default');

        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show', ['connection' => 'nonexistent_connection', 'table' => 'users']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('currentConnection', $defaultConnection)
            );
    }

    // ─── Table Show: View Parameter ──────────────────────────────────

    public function test_table_show_with_structure_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show.view', ['connection' => 'sqlite', 'table' => 'users', 'view' => 'structure']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('view', 'structure')
            );
    }

    public function test_table_show_with_data_view(): void
    {
        User::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show.view', ['connection' => 'sqlite', 'table' => 'users', 'view' => 'data']))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $page->where('view', 'data');

                $table = $page->toArray()['props']['table'];
                $this->assertNotEmpty($table['data']);
                $this->assertNotNull($table['pagination']);
            });
    }

    public function test_table_show_data_view_has_pagination_fields(): void
    {
        User::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show.view', ['connection' => 'sqlite', 'table' => 'users', 'view' => 'data']))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $pagination = $page->toArray()['props']['table']['pagination'];
                $this->assertArrayHasKey('current_page', $pagination);
                $this->assertArrayHasKey('last_page', $pagination);
                $this->assertArrayHasKey('per_page', $pagination);
                $this->assertArrayHasKey('total', $pagination);
                $this->assertArrayHasKey('from', $pagination);
                $this->assertArrayHasKey('to', $pagination);
                $this->assertArrayHasKey('links', $pagination);
            });
    }

    public function test_table_show_data_view_respects_per_page(): void
    {
        User::factory()->count(10)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show.view', [
                'connection' => 'sqlite',
                'table' => 'users',
                'view' => 'data',
                'per_page' => 3,
            ]))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $table = $page->toArray()['props']['table'];
                $this->assertCount(3, $table['data']);
                $this->assertEquals(3, $table['pagination']['per_page']);
                // admin from setUp + 10 factory users = 11
                $this->assertEquals(11, $table['pagination']['total']);
            });
    }

    public function test_table_show_data_view_supports_page_navigation(): void
    {
        User::factory()->count(10)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show.view', [
                'connection' => 'sqlite',
                'table' => 'users',
                'view' => 'data',
                'per_page' => 5,
                'page' => 2,
            ]))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $table = $page->toArray()['props']['table'];
                $this->assertEquals(2, $table['pagination']['current_page']);
                $this->assertCount(5, $table['data']);
            });
    }

    public function test_table_show_with_indexes_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show.view', ['connection' => 'sqlite', 'table' => 'users', 'view' => 'indexes']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('view', 'indexes')
            );
    }

    public function test_table_show_with_actions_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show.view', ['connection' => 'sqlite', 'table' => 'users', 'view' => 'actions']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('view', 'actions')
            );
    }

    public function test_table_show_invalid_view_treated_as_null(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show.view', ['connection' => 'sqlite', 'table' => 'users', 'view' => 'invalid']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('view', null)
            );
    }

    // ─── Data Masking ────────────────────────────────────────────────

    public function test_table_show_data_view_masks_password_column(): void
    {
        User::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show.view', ['connection' => 'sqlite', 'table' => 'users', 'view' => 'data']))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $data = $page->toArray()['props']['table']['data'];
                $maskedValue = str_repeat("\u{2022}", 8);

                foreach ($data as $row) {
                    if (isset($row['password']) && $row['password'] !== null) {
                        $this->assertEquals($maskedValue, $row['password']);
                    }
                }
            });
    }

    // ─── Audit Logging ───────────────────────────────────────────────

    public function test_table_show_creates_audit_log_entry(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'database.viewed',
        ]);
    }

    public function test_table_show_audit_log_records_correct_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show.view', ['connection' => 'sqlite', 'table' => 'users', 'view' => 'data']))
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'database.viewed',
        ]);
    }

    // ─── List Connections ────────────────────────────────────────────

    public function test_databases_listing_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.databases.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Databases/Index')
                ->has('connections')
            );
    }

    public function test_databases_listing_connection_info_has_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.databases.index'))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $connections = $page->toArray()['props']['connections'];
                $this->assertNotEmpty($connections);

                foreach ($connections as $connection) {
                    $this->assertArrayHasKey('name', $connection);
                    $this->assertArrayHasKey('driver', $connection);
                    $this->assertArrayHasKey('database', $connection);
                    $this->assertArrayHasKey('host', $connection);
                    $this->assertArrayHasKey('port', $connection);
                    $this->assertArrayHasKey('isDefault', $connection);
                }
            });
    }

    public function test_databases_listing_marks_default_connection(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.databases.index'))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $connections = $page->toArray()['props']['connections'];
                $defaultConnection = config('database.default');

                $defaultFound = false;
                foreach ($connections as $connection) {
                    if ($connection['name'] === $defaultConnection) {
                        $this->assertTrue($connection['isDefault']);
                        $defaultFound = true;
                    } else {
                        $this->assertFalse($connection['isDefault']);
                    }
                }
                $this->assertTrue($defaultFound, 'Default connection should be present in the connections list.');
            });
    }

    public function test_databases_listing_includes_sqlite_connection(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.databases.index'))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $connections = $page->toArray()['props']['connections'];
                $connectionNames = array_column($connections, 'name');
                $this->assertContains('sqlite', $connectionNames);
            });
    }

    public function test_databases_listing_shows_correct_driver(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.databases.index'))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $connections = $page->toArray()['props']['connections'];
                $sqliteConnection = collect($connections)->firstWhere('name', 'sqlite');
                $this->assertNotNull($sqliteConnection);
                $this->assertEquals('sqlite', $sqliteConnection['driver']);
            });
    }

    // ─── Table Show with Roles Table ─────────────────────────────────

    public function test_table_show_works_for_roles_table(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'roles']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Database/Show')
                ->where('table.name', 'roles')
                ->has('table.columns')
            );
    }

    public function test_table_show_indexes_for_users_table(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.show', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $indexes = $page->toArray()['props']['table']['indexes'];
                $this->assertIsArray($indexes);

                if (!empty($indexes)) {
                    foreach ($indexes as $index) {
                        $this->assertArrayHasKey('name', $index);
                        $this->assertArrayHasKey('unique', $index);
                        $this->assertArrayHasKey('columns', $index);
                    }
                }
            });
    }

    public function test_connection_index_shows_multiple_tables(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.connection.index', ['connection' => 'sqlite']))
            ->assertOk()
            ->assertInertia(function ($page): void {
                $tables = $page->toArray()['props']['tables'];
                $tableNames = array_column($tables, 'name');
                $this->assertContains('users', $tableNames);
                $this->assertContains('roles', $tableNames);
                $this->assertGreaterThan(1, count($tables));
            });
    }

    // ─── Query Execution ────────────────────────────────────────────

    public function test_guests_cannot_execute_queries(): void
    {
        $this->postJson(route('admin.database.query'), ['query' => 'SELECT 1'])
            ->assertUnauthorized();
    }

    public function test_regular_users_cannot_execute_queries(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('admin.database.query'), ['query' => 'SELECT 1'])
            ->assertForbidden();
    }

    public function test_admin_can_execute_select_query(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => 'SELECT 1 AS result'])
            ->assertOk()
            ->assertJsonStructure(['columns', 'rows', 'total', 'truncated', 'duration_ms'])
            ->assertJsonFragment(['columns' => ['result']])
            ->assertJsonPath('total', 1)
            ->assertJsonPath('truncated', false);
    }

    public function test_query_returns_correct_data(): void
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => 'SELECT id, name, email FROM users ORDER BY id'])
            ->assertOk();

        $data = $response->json();
        $this->assertContains('id', $data['columns']);
        $this->assertContains('name', $data['columns']);
        $this->assertContains('email', $data['columns']);
        // admin from setUp + 3 factory users = 4
        $this->assertEquals(4, $data['total']);
        $this->assertCount(4, $data['rows']);
    }

    public function test_query_rejects_insert_statement(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => "INSERT INTO users (name) VALUES ('test')"])
            ->assertUnprocessable()
            ->assertJsonFragment(['error' => 'Only read-only queries are allowed (SELECT, SHOW, EXPLAIN, DESCRIBE).']);
    }

    public function test_query_rejects_update_statement(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => "UPDATE users SET name = 'test'"])
            ->assertUnprocessable();
    }

    public function test_query_rejects_delete_statement(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => 'DELETE FROM users'])
            ->assertUnprocessable();
    }

    public function test_query_rejects_drop_statement(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => 'DROP TABLE users'])
            ->assertUnprocessable();
    }

    public function test_query_rejects_dangerous_into_outfile(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => "SELECT * FROM users INTO OUTFILE '/tmp/test.csv'"])
            ->assertUnprocessable()
            ->assertJsonFragment(['error' => 'Query contains disallowed operations.']);
    }

    public function test_query_rejects_load_file(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => "SELECT LOAD_FILE('/etc/passwd')"])
            ->assertUnprocessable()
            ->assertJsonFragment(['error' => 'Query contains disallowed operations.']);
    }

    public function test_query_allows_show_tables(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => "SELECT name FROM sqlite_master WHERE type='table'"])
            ->assertOk();
    }

    public function test_query_allows_explain(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => 'EXPLAIN SELECT * FROM users'])
            ->assertOk();
    }

    public function test_query_allows_pragma(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => 'PRAGMA table_info(users)'])
            ->assertOk();
    }

    public function test_query_requires_query_parameter(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['query']);
    }

    public function test_query_creates_audit_log(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => 'SELECT 1'])
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'database.viewed',
        ]);
    }

    public function test_query_handles_invalid_sql_gracefully(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => 'SELECT FROM'])
            ->assertUnprocessable()
            ->assertJsonStructure(['error']);
    }

    public function test_query_returns_empty_result_set(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), ['query' => 'SELECT * FROM users WHERE id = -1'])
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonPath('columns', [])
            ->assertJsonPath('rows', []);
    }

    public function test_query_accepts_connection_parameter(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.database.query'), [
                'query' => 'SELECT 1 AS result',
                'connection' => 'sqlite',
            ])
            ->assertOk()
            ->assertJsonPath('total', 1);
    }

    // ─── Table Export ───────────────────────────────────────────────

    public function test_guests_cannot_export_table(): void
    {
        $this->get(route('admin.database.export', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_export_table(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.database.export', ['connection' => 'sqlite', 'table' => 'users']))
            ->assertForbidden();
    }

    public function test_admin_can_export_table_as_csv(): void
    {
        User::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.database.export', ['connection' => 'sqlite', 'table' => 'users']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertDownload();
    }

    public function test_export_csv_contains_headers_and_data(): void
    {
        User::factory()->create(['name' => 'Export Test User', 'email' => 'export@test.com']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.database.export', ['connection' => 'sqlite', 'table' => 'users']));

        $content = $response->streamedContent();
        $lines = explode("\n", mb_trim($content));

        // First line should be CSV headers
        $this->assertStringContainsString('id', $lines[0]);
        $this->assertStringContainsString('name', $lines[0]);
        $this->assertStringContainsString('email', $lines[0]);

        // Should contain our test data somewhere in the rows
        $this->assertStringContainsString('Export Test User', $content);
        $this->assertStringContainsString('export@test.com', $content);
    }

    public function test_export_masks_sensitive_columns(): void
    {
        User::factory()->create(['password' => bcrypt('secret')]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.database.export', ['connection' => 'sqlite', 'table' => 'users']));

        $content = $response->streamedContent();

        // Password column should be masked, not contain the actual hash
        $this->assertStringContainsString('********', $content);
        $this->assertStringNotContainsString('$2y$', $content);
    }

    public function test_export_nonexistent_table_returns_404(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.export', ['connection' => 'sqlite', 'table' => 'nonexistent_xyz']))
            ->assertNotFound();
    }

    public function test_export_creates_audit_log(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.export', ['connection' => 'sqlite', 'table' => 'users']));

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'database.viewed',
        ]);
    }

    public function test_export_invalid_connection_falls_back_to_default(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.database.export', ['connection' => 'nonexistent', 'table' => 'users']))
            ->assertOk()
            ->assertDownload();
    }
}
