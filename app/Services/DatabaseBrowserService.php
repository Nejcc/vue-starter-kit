<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\AuditEvent;
use App\Contracts\Repositories\DatabaseBrowserRepositoryInterface;
use App\Contracts\Services\DatabaseBrowserServiceInterface;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DatabaseBrowserService extends AbstractNonModelService implements DatabaseBrowserServiceInterface
{
    public function __construct(
        private readonly DatabaseBrowserRepositoryInterface $repository,
    ) {}

    /**
     * @return array{tables: array<int, array<string, mixed>>, connections: array<int, string>, currentConnection: string, driver: string}|null
     */
    public function getTablesIndex(Request $request, ?string $connection): ?array
    {
        $connections = $this->repository->getAvailableConnections();

        if ($connection === null) {
            if (!$request->has('connection')) {
                return null;
            }
            $connection = $request->get('connection', config('database.default'));
        }

        $connection = $this->repository->validateConnection($connection);

        $connectionTest = $this->repository->testConnection($connection);
        if (!$connectionTest['success']) {
            return null;
        }

        return [
            'tables' => $this->repository->getTablesForConnection($connection),
            'connections' => $connections,
            'currentConnection' => $connection,
            'driver' => $connectionTest['driver'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTableDetail(Request $request, string $connection, string $table, ?string $view): array
    {
        $connection = $this->repository->validateConnection($connection);

        // Validate view parameter
        $validViews = ['structure', 'data', 'indexes', 'actions'];
        if ($view !== null && !in_array($view, $validViews, true)) {
            $view = null;
        }

        $tableInfo = $this->repository->getTableDetail($connection, $table, $view, $request);

        AuditLog::log(AuditEvent::DATABASE_VIEWED, null, null, [
            'connection' => $connection,
            'table' => $table,
            'view' => $view ?? 'structure',
        ]);

        return [
            'table' => $tableInfo,
            'connections' => $this->repository->getAvailableConnections(),
            'currentConnection' => $connection,
            'driver' => \Illuminate\Support\Facades\DB::connection($connection)->getDriverName(),
            'view' => $view,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function executeQuery(string $sql, string $connection): array
    {
        $result = $this->repository->executeReadOnlyQuery($connection, $sql);

        if (!isset($result['error'])) {
            AuditLog::log(AuditEvent::DATABASE_VIEWED, null, null, [
                'connection' => $connection,
                'query' => mb_substr($sql, 0, 500),
                'rows_returned' => $result['total'],
            ]);
        }

        return $result;
    }

    public function exportTable(string $connection, string $table): StreamedResponse
    {
        $connection = $this->repository->validateConnection($connection);

        $columnListing = $this->repository->getTableColumnsForExport($connection, $table);
        if (empty($columnListing)) {
            abort(404, "Table '{$table}' not found.");
        }

        AuditLog::log(AuditEvent::DATABASE_VIEWED, null, null, [
            'connection' => $connection,
            'table' => $table,
            'action' => 'export',
        ]);

        return $this->repository->streamTableData($connection, $table);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getConnectionsListing(): array
    {
        return $this->repository->getConnectionInfo();
    }

    public function resolveConnection(Request $request, ?string $connection): ?string
    {
        if ($connection === null && !$request->has('connection')) {
            return null;
        }

        $connection = $connection ?? $request->get('connection', config('database.default'));

        return $this->repository->validateConnection($connection);
    }

    /**
     * @return array{success: bool, driver?: string, error?: string}
     */
    public function testConnection(string $connection): array
    {
        return $this->repository->testConnection($connection);
    }
}
