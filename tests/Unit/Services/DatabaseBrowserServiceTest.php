<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\DatabaseBrowserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class DatabaseBrowserServiceTest extends TestCase
{
    use RefreshDatabase;

    private DatabaseBrowserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DatabaseBrowserService::class);
    }

    public function test_get_tables_index_returns_correct_shape(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = Request::create('/admin/database', 'GET', ['connection' => 'sqlite']);

        $result = $this->service->getTablesIndex($request, 'sqlite');

        $this->assertNotNull($result);
        $this->assertArrayHasKey('tables', $result);
        $this->assertArrayHasKey('connections', $result);
        $this->assertArrayHasKey('currentConnection', $result);
        $this->assertArrayHasKey('driver', $result);
    }

    public function test_get_tables_index_returns_null_without_connection(): void
    {
        $request = Request::create('/admin/database');

        $result = $this->service->getTablesIndex($request, null);

        $this->assertNull($result);
    }

    public function test_get_table_detail_returns_table_info(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = Request::create('/admin/database/sqlite/users');

        $result = $this->service->getTableDetail($request, 'sqlite', 'users', 'structure');

        $this->assertArrayHasKey('table', $result);
        $this->assertArrayHasKey('connections', $result);
        $this->assertArrayHasKey('currentConnection', $result);
        $this->assertArrayHasKey('driver', $result);
        $this->assertEquals('users', $result['table']['name']);
    }

    public function test_execute_query_valid_select(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $result = $this->service->executeQuery('SELECT 1 as value', 'sqlite');

        $this->assertArrayHasKey('columns', $result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayNotHasKey('error', $result);
    }

    public function test_execute_query_blocked_sql(): void
    {
        $result = $this->service->executeQuery('DELETE FROM users', 'sqlite');

        $this->assertArrayHasKey('error', $result);
    }

    public function test_execute_query_logs_audit_for_successful_query(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->service->executeQuery('SELECT COUNT(*) FROM users', 'sqlite');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'database.viewed',
        ]);
    }

    public function test_resolve_connection_returns_null_without_connection_param(): void
    {
        $request = Request::create('/admin/database');

        $result = $this->service->resolveConnection($request, null);

        $this->assertNull($result);
    }

    public function test_test_connection_returns_success_for_valid_connection(): void
    {
        $result = $this->service->testConnection('sqlite');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('driver', $result);
    }
}
