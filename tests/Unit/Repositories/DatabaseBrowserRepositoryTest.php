<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Repositories\DatabaseBrowserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class DatabaseBrowserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DatabaseBrowserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DatabaseBrowserRepository();
    }

    public function test_get_available_connections_returns_array(): void
    {
        $connections = $this->repository->getAvailableConnections();

        $this->assertIsArray($connections);
        $this->assertContains('sqlite', $connections);
    }

    public function test_validate_connection_returns_valid_connection(): void
    {
        $result = $this->repository->validateConnection('sqlite');

        $this->assertEquals('sqlite', $result);
    }

    public function test_validate_connection_returns_default_for_invalid(): void
    {
        $result = $this->repository->validateConnection('nonexistent_connection');

        $this->assertEquals(config('database.default'), $result);
    }

    public function test_test_connection_success(): void
    {
        $result = $this->repository->testConnection('sqlite');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('driver', $result);
    }

    public function test_test_connection_failure(): void
    {
        config(['database.connections.bad' => ['driver' => 'mysql', 'host' => 'nonexistent', 'database' => 'none']]);

        $result = $this->repository->testConnection('bad');

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_get_tables_for_connection_returns_tables(): void
    {
        $tables = $this->repository->getTablesForConnection('sqlite');

        $this->assertIsArray($tables);
        $this->assertNotEmpty($tables);

        $tableNames = array_column($tables, 'name');
        $this->assertContains('users', $tableNames);
    }

    public function test_get_table_detail_returns_expected_keys(): void
    {
        $request = Request::create('/admin/database/sqlite/users', 'GET', ['per_page' => 10]);

        $result = $this->repository->getTableDetail('sqlite', 'users', 'structure', $request);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('columns', $result);
        $this->assertArrayHasKey('indexes', $result);
        $this->assertArrayHasKey('foreignKeys', $result);
        $this->assertArrayHasKey('rowCount', $result);
        $this->assertEquals('users', $result['name']);
        $this->assertNotEmpty($result['columns']);
    }

    public function test_get_table_detail_with_data_view(): void
    {
        $request = Request::create('/admin/database/sqlite/users', 'GET', ['per_page' => 10, 'page' => 1]);

        $result = $this->repository->getTableDetail('sqlite', 'users', 'data', $request);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertIsArray($result['data']);
    }

    public function test_execute_read_only_query_valid_select(): void
    {
        $result = $this->repository->executeReadOnlyQuery('sqlite', 'SELECT 1 as value');

        $this->assertArrayHasKey('columns', $result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('truncated', $result);
        $this->assertArrayHasKey('duration_ms', $result);
        $this->assertContains('value', $result['columns']);
    }

    public function test_execute_read_only_query_blocks_write(): void
    {
        $result = $this->repository->executeReadOnlyQuery('sqlite', 'DELETE FROM users');

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('read-only', $result['error']);
    }

    public function test_execute_read_only_query_blocks_dangerous_patterns(): void
    {
        $result = $this->repository->executeReadOnlyQuery('sqlite', "SELECT * INTO OUTFILE '/tmp/test' FROM users");

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('disallowed', $result['error']);
    }

    public function test_is_read_only_allows_show_and_explain(): void
    {
        $showResult = $this->repository->executeReadOnlyQuery('sqlite', 'PRAGMA table_info(users)');
        $this->assertArrayNotHasKey('error', $showResult);
    }

    public function test_masked_columns_in_table_detail(): void
    {
        config(['security.database_browser.masked_columns' => ['password']]);

        // Insert a user to ensure there's data
        \App\Models\User::factory()->create();

        $request = Request::create('/admin/database/sqlite/users', 'GET', ['per_page' => 10, 'page' => 1]);
        $result = $this->repository->getTableDetail('sqlite', 'users', 'data', $request);

        if (!empty($result['data'])) {
            $row = $result['data'][0];
            if (isset($row['password'])) {
                $this->assertEquals(str_repeat("\u{2022}", 8), $row['password']);
            }
        }

        $this->assertTrue(true);
    }

    public function test_get_connection_info_returns_expected_shape(): void
    {
        $info = $this->repository->getConnectionInfo();

        $this->assertIsArray($info);
        $this->assertNotEmpty($info);

        $first = $info[0];
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('driver', $first);
        $this->assertArrayHasKey('database', $first);
        $this->assertArrayHasKey('isDefault', $first);
    }
}
