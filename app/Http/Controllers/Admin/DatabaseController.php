<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\RoleNames;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin database controller.
 *
 * Handles displaying database tables and their structure.
 */
final class DatabaseController extends Controller
{
    /**
     * Create a new admin database controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Check if user has admin or super-admin role.
     */
    private function authorizeAdmin(): void
    {
        $user = auth()->user();

        if (!$user || (!$user->hasRole(RoleNames::SUPER_ADMIN) && !$user->hasRole(RoleNames::ADMIN))) {
            abort(403, 'Unauthorized. Admin access required.');
        }
    }

    /**
     * Test database connection.
     *
     * @param  string  $connection  The connection name to test
     * @return array{success: bool, driver?: string, error?: string}
     */
    private function testConnection(string $connection): array
    {
        try {
            $db = DB::connection($connection);
            $db->getPdo(); // This will throw exception if connection fails
            $driver = $db->getDriverName();

            return [
                'success' => true,
                'driver' => $driver,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Show the database tables index page.
     *
     * @param  Request  $request  The incoming request
     * @param  string|null  $connection  The database connection name (optional route parameter)
     * @return Response|RedirectResponse The Inertia response with tables list or redirect
     */
    public function index(Request $request, ?string $connection = null): Response|RedirectResponse
    {
        $this->authorizeAdmin();

        $connections = array_keys(config('database.connections'));

        // Get connection from route parameter or query parameter
        if ($connection === null) {
            // If no connection specified, redirect to databases listing page
            if (!$request->has('connection')) {
                return redirect()->route('admin.databases.index');
            }
            $connection = $request->get('connection', config('database.default'));
        }

        // Validate connection exists
        if (!in_array($connection, $connections, true)) {
            $connection = config('database.default');
        }

        // Test the connection before proceeding
        $connectionTest = $this->testConnection($connection);
        if (!$connectionTest['success']) {
            return redirect()
                ->route('admin.databases.index')
                ->withErrors(['connection' => 'Failed to connect to database: '.$connectionTest['error']]);
        }

        $db = DB::connection($connection);
        $driver = $connectionTest['driver'];

        $tables = [];

        try {
            if ($driver === 'sqlite') {
                $tables = $db->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
                $tables = array_map(fn ($table) => ['name' => $table->name], $tables);
            } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
                $database = $db->getDatabaseName();
                $tables = $db->select('SELECT TABLE_NAME as name, TABLE_ROWS as rows, DATA_LENGTH + INDEX_LENGTH as size FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME', [$database]);
            } elseif ($driver === 'pgsql') {
                $tables = $db->select("SELECT tablename as name FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
            } elseif ($driver === 'sqlsrv') {
                $tables = $db->select("SELECT TABLE_NAME as name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME");
            }

            // Get row counts for each table
            foreach ($tables as &$table) {
                try {
                    $rowCount = $db->table($table['name'])->count();
                    $table['rows'] = $rowCount;
                } catch (Exception $e) {
                    $table['rows'] = null;
                }

                // Get table size if not already set
                if (!isset($table['size'])) {
                    try {
                        if ($driver === 'sqlite') {
                            $size = $db->selectOne('SELECT page_count * page_size as size FROM pragma_page_count(), pragma_page_size()');
                            $table['size'] = $size->size ?? null;
                        } elseif ($driver === 'pgsql') {
                            $size = $db->selectOne('SELECT pg_total_relation_size(?) as size', ["\"{$table['name']}\""]);
                            $table['size'] = $size->size ?? null;
                        } elseif ($driver === 'sqlsrv') {
                            $size = $db->selectOne('SELECT SUM(reserved_page_count) * 8.0 * 1024 as size FROM sys.dm_db_partition_stats WHERE object_id = OBJECT_ID(?)', [$table['name']]);
                            $table['size'] = $size->size ?? null;
                        }
                    } catch (Exception $e) {
                        $table['size'] = null;
                    }
                }
            }
        } catch (Exception $e) {
            // If there's an error, return empty tables array
            $tables = [];
        }

        return Inertia::render('admin/Database/Index', [
            'tables' => $tables,
            'connections' => $connections,
            'currentConnection' => $connection,
            'driver' => $driver,
        ]);
    }

    /**
     * Show details for a specific table.
     *
     * @param  Request  $request  The incoming request
     * @param  string  $connection  The database connection name
     * @param  string  $table  The table name
     * @param  string|null  $view  The view type (structure, data, indexes, actions)
     * @return Response The Inertia response with table details
     */
    public function show(Request $request, string $connection, string $table, ?string $view = null): Response
    {
        $this->authorizeAdmin();

        $connections = array_keys(config('database.connections'));

        // Validate connection exists
        if (!in_array($connection, $connections, true)) {
            $connection = config('database.default');
        }

        $db = DB::connection($connection);
        $driver = $db->getDriverName();

        $tableInfo = [
            'name' => $table,
            'columns' => [],
            'indexes' => [],
            'foreignKeys' => [],
            'rowCount' => 0,
            'data' => [],
        ];

        try {
            // Get row count
            $tableInfo['rowCount'] = $db->table($table)->count();

            // Get data with pagination (only when view is 'data')
            $tableInfo['data'] = [];
            $tableInfo['pagination'] = null;
            if ($view === 'data') {
                try {
                    $perPage = (int) $request->get('per_page', 25);
                    $page = (int) $request->get('page', 1);
                    $total = $db->table($table)->count();
                    $offset = ($page - 1) * $perPage;

                    $data = $db->table($table)
                        ->offset($offset)
                        ->limit($perPage)
                        ->get()
                        ->map(fn ($row) => (array) $row)
                        ->toArray();

                    $paginator = new LengthAwarePaginator(
                        $data,
                        $total,
                        $perPage,
                        $page,
                        [
                            'path' => $request->url(),
                            'query' => $request->query(),
                        ]
                    );

                    $tableInfo['data'] = $data;
                    $tableInfo['pagination'] = [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                        'from' => $paginator->firstItem(),
                        'to' => $paginator->lastItem(),
                        'links' => $paginator->linkCollection()->toArray(),
                    ];
                } catch (Exception $e) {
                    $tableInfo['data'] = [];
                    $tableInfo['pagination'] = null;
                }
            }

            // Get columns
            if ($driver === 'sqlite') {
                $columns = $db->select("PRAGMA table_info({$table})");
                foreach ($columns as $column) {
                    $tableInfo['columns'][] = [
                        'name' => $column->name,
                        'type' => $column->type,
                        'nullable' => !$column->notnull,
                        'default' => $column->dflt_value,
                        'primary' => (bool) $column->pk,
                    ];
                }

                // Get indexes
                $indexes = $db->select("PRAGMA index_list({$table})");
                foreach ($indexes as $index) {
                    $indexColumns = $db->select("PRAGMA index_info({$index->name})");
                    $tableInfo['indexes'][] = [
                        'name' => $index->name,
                        'unique' => (bool) $index->unique,
                        'columns' => array_map(fn ($col) => $col->name, $indexColumns),
                    ];
                }

                // Get foreign keys
                $foreignKeys = $db->select("PRAGMA foreign_key_list({$table})");
                foreach ($foreignKeys as $fk) {
                    $tableInfo['foreignKeys'][] = [
                        'name' => $fk->id,
                        'columns' => [$fk->from],
                        'referencedTable' => $fk->table,
                        'referencedColumns' => [$fk->to],
                        'onDelete' => $fk->on_delete ?? null,
                        'onUpdate' => $fk->on_update ?? null,
                    ];
                }
            } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
                $database = $db->getDatabaseName();
                $columns = $db->select('SELECT COLUMN_NAME as name, DATA_TYPE as type, IS_NULLABLE as nullable, COLUMN_DEFAULT as default, COLUMN_KEY as key_type, EXTRA as extra FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION', [$database, $table]);
                foreach ($columns as $column) {
                    $tableInfo['columns'][] = [
                        'name' => $column->name,
                        'type' => $column->type,
                        'nullable' => $column->nullable === 'YES',
                        'default' => $column->default,
                        'primary' => $column->key_type === 'PRI',
                    ];
                }

                // Get indexes
                $indexes = $db->select('SELECT DISTINCT INDEX_NAME as name, NON_UNIQUE as non_unique FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$database, $table]);
                foreach ($indexes as $index) {
                    $indexColumns = $db->select('SELECT COLUMN_NAME as name FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? ORDER BY SEQ_IN_INDEX', [$database, $table, $index->name]);
                    $tableInfo['indexes'][] = [
                        'name' => $index->name,
                        'unique' => !$index->non_unique,
                        'columns' => array_map(fn ($col) => $col->name, $indexColumns),
                    ];
                }

                // Get foreign keys
                $foreignKeys = $db->select('SELECT CONSTRAINT_NAME as name, COLUMN_NAME as column_name, REFERENCED_TABLE_NAME as referenced_table, REFERENCED_COLUMN_NAME as referenced_column, DELETE_RULE as on_delete, UPDATE_RULE as on_update FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL', [$database, $table]);
                $fkGroups = [];
                foreach ($foreignKeys as $fk) {
                    if (!isset($fkGroups[$fk->name])) {
                        $fkGroups[$fk->name] = [
                            'name' => $fk->name,
                            'columns' => [],
                            'referencedTable' => $fk->referenced_table,
                            'referencedColumns' => [],
                            'onDelete' => $fk->on_delete,
                            'onUpdate' => $fk->on_update,
                        ];
                    }
                    $fkGroups[$fk->name]['columns'][] = $fk->column_name;
                    $fkGroups[$fk->name]['referencedColumns'][] = $fk->referenced_column;
                }
                $tableInfo['foreignKeys'] = array_values($fkGroups);
            } elseif ($driver === 'pgsql') {
                $columns = $db->select('SELECT column_name as name, data_type as type, is_nullable as nullable, column_default as default FROM information_schema.columns WHERE table_name = ? ORDER BY ordinal_position', [$table]);
                foreach ($columns as $column) {
                    $isPrimary = $db->selectOne("SELECT COUNT(*) as count FROM information_schema.table_constraints tc JOIN information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name WHERE tc.table_name = ? AND tc.constraint_type = 'PRIMARY KEY' AND kcu.column_name = ?", [$table, $column->name]);
                    $tableInfo['columns'][] = [
                        'name' => $column->name,
                        'type' => $column->type,
                        'nullable' => $column->nullable === 'YES',
                        'default' => $column->default,
                        'primary' => $isPrimary->count > 0,
                    ];
                }

                // Get indexes
                $indexes = $db->select('SELECT indexname as name, indexdef as definition FROM pg_indexes WHERE tablename = ?', [$table]);
                foreach ($indexes as $index) {
                    // Extract unique and columns from definition
                    $isUnique = str_contains($index->definition, 'UNIQUE');
                    preg_match('/\(([^)]+)\)/', $index->definition, $matches);
                    $columns = $matches[1] ?? '';
                    $tableInfo['indexes'][] = [
                        'name' => $index->name,
                        'unique' => $isUnique,
                        'columns' => array_map('trim', explode(',', $columns)),
                    ];
                }

                // Get foreign keys
                $foreignKeys = $db->select("SELECT tc.constraint_name as name, kcu.column_name as column_name, ccu.table_name as referenced_table, ccu.column_name as referenced_column, rc.delete_rule as on_delete, rc.update_rule as on_update FROM information_schema.table_constraints AS tc JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name JOIN information_schema.referential_constraints AS rc ON rc.constraint_name = tc.constraint_name WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_name = ?", [$table]);
                $fkGroups = [];
                foreach ($foreignKeys as $fk) {
                    if (!isset($fkGroups[$fk->name])) {
                        $fkGroups[$fk->name] = [
                            'name' => $fk->name,
                            'columns' => [],
                            'referencedTable' => $fk->referenced_table,
                            'referencedColumns' => [],
                            'onDelete' => $fk->on_delete,
                            'onUpdate' => $fk->on_update,
                        ];
                    }
                    $fkGroups[$fk->name]['columns'][] = $fk->column_name;
                    $fkGroups[$fk->name]['referencedColumns'][] = $fk->referenced_column;
                }
                $tableInfo['foreignKeys'] = array_values($fkGroups);
            } elseif ($driver === 'sqlsrv') {
                $columns = $db->select('SELECT COLUMN_NAME as name, DATA_TYPE as type, IS_NULLABLE as nullable, COLUMN_DEFAULT as default FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? ORDER BY ORDINAL_POSITION', [$table]);
                foreach ($columns as $column) {
                    $isPrimary = $db->selectOne("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = ? AND COLUMN_NAME = ? AND CONSTRAINT_NAME LIKE 'PK_%'", [$table, $column->name]);
                    $tableInfo['columns'][] = [
                        'name' => $column->name,
                        'type' => $column->type,
                        'nullable' => $column->nullable === 'YES',
                        'default' => $column->default,
                        'primary' => $isPrimary->count > 0,
                    ];
                }

                // Get indexes
                $indexes = $db->select('SELECT i.name as name, i.is_unique as is_unique FROM sys.indexes i WHERE i.object_id = OBJECT_ID(?) AND i.name IS NOT NULL', [$table]);
                foreach ($indexes as $index) {
                    $indexColumns = $db->select('SELECT c.name as name FROM sys.index_columns ic JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id WHERE ic.object_id = OBJECT_ID(?) AND ic.index_id = (SELECT index_id FROM sys.indexes WHERE name = ? AND object_id = OBJECT_ID(?)) ORDER BY ic.key_ordinal', [$table, $index->name, $table]);
                    $tableInfo['indexes'][] = [
                        'name' => $index->name,
                        'unique' => (bool) $index->is_unique,
                        'columns' => array_map(fn ($col) => $col->name, $indexColumns),
                    ];
                }

                // Get foreign keys
                $foreignKeys = $db->select('SELECT fk.name as name, c.name as column_name, rt.name as referenced_table, rc.name as referenced_column, fk.delete_referential_action_desc as on_delete, fk.update_referential_action_desc as on_update FROM sys.foreign_keys fk JOIN sys.foreign_key_columns fkc ON fk.object_id = fkc.constraint_object_id JOIN sys.columns c ON fkc.parent_object_id = c.object_id AND fkc.parent_column_id = c.column_id JOIN sys.tables rt ON fkc.referenced_object_id = rt.object_id JOIN sys.columns rc ON fkc.referenced_object_id = rc.object_id AND fkc.referenced_column_id = rc.column_id WHERE rt.name = ?', [$table]);
                $fkGroups = [];
                foreach ($foreignKeys as $fk) {
                    if (!isset($fkGroups[$fk->name])) {
                        $fkGroups[$fk->name] = [
                            'name' => $fk->name,
                            'columns' => [],
                            'referencedTable' => $fk->referenced_table,
                            'referencedColumns' => [],
                            'onDelete' => $fk->on_delete,
                            'onUpdate' => $fk->on_update,
                        ];
                    }
                    $fkGroups[$fk->name]['columns'][] = $fk->column_name;
                    $fkGroups[$fk->name]['referencedColumns'][] = $fk->referenced_column;
                }
                $tableInfo['foreignKeys'] = array_values($fkGroups);
            }
        } catch (Exception $e) {
            // If table doesn't exist or error occurs, return error
            abort(404, 'Table not found or error accessing table information.');
        }

        // Validate view parameter
        $validViews = ['structure', 'data', 'indexes', 'actions'];
        if ($view !== null && !in_array($view, $validViews, true)) {
            $view = null;
        }

        return Inertia::render('admin/Database/Show', [
            'table' => $tableInfo,
            'connections' => $connections,
            'currentConnection' => $connection,
            'driver' => $driver,
            'view' => $view,
        ]);
    }

    /**
     * List all database connections.
     *
     * @return Response The Inertia response with connections list
     */
    public function listConnections(): Response
    {
        $this->authorizeAdmin();

        $connections = array_keys(config('database.connections'));
        $defaultConnection = config('database.default');

        $connectionInfo = [];

        foreach ($connections as $connection) {
            $config = config("database.connections.{$connection}");

            if (empty($config)) {
                continue;
            }

            $driver = $config['driver'] ?? 'unknown';
            $databaseName = $config['database'] ?? 'N/A';

            // For SQLite, show the file path relative to database_path if it's a file
            if ($driver === 'sqlite' && $databaseName && !str_starts_with($databaseName, ':memory:')) {
                if (str_starts_with($databaseName, database_path())) {
                    $databaseName = 'database/'.basename($databaseName);
                }
            }

            $connectionInfo[] = [
                'name' => $connection,
                'driver' => $driver,
                'database' => $databaseName,
                'host' => $config['host'] ?? null,
                'port' => $config['port'] ?? null,
                'isDefault' => $connection === $defaultConnection,
            ];
        }

        return Inertia::render('admin/Databases/Index', [
            'connections' => $connectionInfo,
        ]);
    }
}
