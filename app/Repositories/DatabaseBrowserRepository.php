<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\DatabaseBrowserRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DatabaseBrowserRepository extends AbstractNonModelRepository implements DatabaseBrowserRepositoryInterface
{
    /**
     * @return array{success: bool, driver?: string, error?: string}
     */
    public function testConnection(string $connection): array
    {
        try {
            $db = DB::connection($connection);
            $db->getPdo();
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
     * @return array<int, array<string, mixed>>
     */
    public function getTablesForConnection(string $connection): array
    {
        $db = DB::connection($connection);
        $driver = $db->getDriverName();

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
            $tables = [];
        }

        return $tables;
    }

    /**
     * @return array<string, mixed>
     */
    public function getTableDetail(string $connection, string $table, ?string $view, Request $request): array
    {
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

                    $maskedColumns = config('security.database_browser.masked_columns', []);
                    $data = array_map(fn (array $row): array => $this->maskRowData($row, $maskedColumns), $data);

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

            // Get columns and indexes based on driver
            $this->populateColumnsAndIndexes($tableInfo, $db, $driver, $table);
        } catch (Exception $e) {
            abort(404, 'Table not found or error accessing table information.');
        }

        return $tableInfo;
    }

    /**
     * @return array{columns: array<int, string>, rows: array<int, array<string, mixed>>, total: int, truncated: bool, duration_ms: float}|array{error: string}
     */
    public function executeReadOnlyQuery(string $connection, string $sql): array
    {
        $sql = mb_trim($sql);

        // Only allow read-only queries
        $allowedPrefixes = ['select', 'show', 'explain', 'describe', 'desc', 'pragma'];
        $sqlLower = mb_strtolower($sql);

        $isAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($sqlLower, $prefix)) {
                $isAllowed = true;

                break;
            }
        }

        if (!$isAllowed) {
            return ['error' => 'Only read-only queries are allowed (SELECT, SHOW, EXPLAIN, DESCRIBE).'];
        }

        // Block dangerous patterns even in SELECT
        $dangerousPatterns = [
            '/\binto\s+outfile\b/i',
            '/\binto\s+dumpfile\b/i',
            '/\bload_file\b/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                return ['error' => 'Query contains disallowed operations.'];
            }
        }

        try {
            $db = DB::connection($connection);
            $startTime = microtime(true);
            $results = $db->select($sql);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $columns = [];
            $rows = [];

            if (count($results) > 0) {
                $columns = array_keys((array) $results[0]);
                $rows = array_map(fn ($row) => (array) $row, array_slice($results, 0, 1000));
            }

            return [
                'columns' => $columns,
                'rows' => $rows,
                'total' => count($results),
                'truncated' => count($results) > 1000,
                'duration_ms' => $duration,
            ];
        } catch (Exception $e) {
            return ['error' => 'Query failed: ' . $e->getMessage()];
        }
    }

    /**
     * @return array<int, string>
     */
    public function getTableColumnsForExport(string $connection, string $table): array
    {
        return DB::connection($connection)->getSchemaBuilder()->getColumnListing($table);
    }

    public function streamTableData(string $connection, string $table): StreamedResponse
    {
        $db = DB::connection($connection);
        $maskedColumns = config('security.database_browser.masked_columns', []);
        $filename = $connection . '_' . $table . '_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($db, $table, $maskedColumns): void {
            $handle = fopen('php://output', 'w');

            $first = true;
            $db->table($table)->orderBy(
                $db->getSchemaBuilder()->getColumnListing($table)[0] ?? 'id'
            )->chunk(1000, function ($rows) use ($handle, &$first, $maskedColumns): void {
                foreach ($rows as $row) {
                    $rowArray = (array) $row;

                    if ($first) {
                        fputcsv($handle, array_keys($rowArray));
                        $first = false;
                    }

                    $rowArray = $this->maskRowData($rowArray, $maskedColumns);

                    fputcsv($handle, $rowArray);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getConnectionInfo(): array
    {
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
                    $databaseName = 'database/' . basename($databaseName);
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

        return $connectionInfo;
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableConnections(): array
    {
        return array_keys(config('database.connections'));
    }

    public function validateConnection(string $connection): string
    {
        $connections = $this->getAvailableConnections();

        if (!in_array($connection, $connections, true)) {
            return config('database.default');
        }

        return $connection;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $maskedColumns
     * @return array<string, mixed>
     */
    private function maskRowData(array $row, array $maskedColumns): array
    {
        foreach ($row as $column => $value) {
            if ($value !== null && in_array($column, $maskedColumns, true)) {
                $row[$column] = str_repeat("\u{2022}", 8);
            }
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $tableInfo
     * @param  \Illuminate\Database\Connection  $db
     */
    private function populateColumnsAndIndexes(array &$tableInfo, $db, string $driver, string $table): void
    {
        if ($driver === 'sqlite') {
            $this->populateSqliteDetails($tableInfo, $db, $table);
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->populateMysqlDetails($tableInfo, $db, $table);
        } elseif ($driver === 'pgsql') {
            $this->populatePostgresDetails($tableInfo, $db, $table);
        } elseif ($driver === 'sqlsrv') {
            $this->populateSqlServerDetails($tableInfo, $db, $table);
        }
    }

    /**
     * @param  array<string, mixed>  $tableInfo
     * @param  \Illuminate\Database\Connection  $db
     */
    private function populateSqliteDetails(array &$tableInfo, $db, string $table): void
    {
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

        $indexes = $db->select("PRAGMA index_list({$table})");
        foreach ($indexes as $index) {
            $indexColumns = $db->select("PRAGMA index_info({$index->name})");
            $tableInfo['indexes'][] = [
                'name' => $index->name,
                'unique' => (bool) $index->unique,
                'columns' => array_map(fn ($col) => $col->name, $indexColumns),
            ];
        }

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
    }

    /**
     * @param  array<string, mixed>  $tableInfo
     * @param  \Illuminate\Database\Connection  $db
     */
    private function populateMysqlDetails(array &$tableInfo, $db, string $table): void
    {
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

        $indexes = $db->select('SELECT DISTINCT INDEX_NAME as name, NON_UNIQUE as non_unique FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?', [$database, $table]);
        foreach ($indexes as $index) {
            $indexColumns = $db->select('SELECT COLUMN_NAME as name FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? ORDER BY SEQ_IN_INDEX', [$database, $table, $index->name]);
            $tableInfo['indexes'][] = [
                'name' => $index->name,
                'unique' => !$index->non_unique,
                'columns' => array_map(fn ($col) => $col->name, $indexColumns),
            ];
        }

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
    }

    /**
     * @param  array<string, mixed>  $tableInfo
     * @param  \Illuminate\Database\Connection  $db
     */
    private function populatePostgresDetails(array &$tableInfo, $db, string $table): void
    {
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

        $indexes = $db->select('SELECT indexname as name, indexdef as definition FROM pg_indexes WHERE tablename = ?', [$table]);
        foreach ($indexes as $index) {
            $isUnique = str_contains($index->definition, 'UNIQUE');
            preg_match('/\(([^)]+)\)/', $index->definition, $matches);
            $columns = $matches[1] ?? '';
            $tableInfo['indexes'][] = [
                'name' => $index->name,
                'unique' => $isUnique,
                'columns' => array_map('trim', explode(',', $columns)),
            ];
        }

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
    }

    /**
     * @param  array<string, mixed>  $tableInfo
     * @param  \Illuminate\Database\Connection  $db
     */
    private function populateSqlServerDetails(array &$tableInfo, $db, string $table): void
    {
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

        $indexes = $db->select('SELECT i.name as name, i.is_unique as is_unique FROM sys.indexes i WHERE i.object_id = OBJECT_ID(?) AND i.name IS NOT NULL', [$table]);
        foreach ($indexes as $index) {
            $indexColumns = $db->select('SELECT c.name as name FROM sys.index_columns ic JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id WHERE ic.object_id = OBJECT_ID(?) AND ic.index_id = (SELECT index_id FROM sys.indexes WHERE name = ? AND object_id = OBJECT_ID(?)) ORDER BY ic.key_ordinal', [$table, $index->name, $table]);
            $tableInfo['indexes'][] = [
                'name' => $index->name,
                'unique' => (bool) $index->is_unique,
                'columns' => array_map(fn ($col) => $col->name, $indexColumns),
            ];
        }

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
}
