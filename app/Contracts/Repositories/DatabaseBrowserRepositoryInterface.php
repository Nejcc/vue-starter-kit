<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface DatabaseBrowserRepositoryInterface
{
    /**
     * @return array{success: bool, driver?: string, error?: string}
     */
    public function testConnection(string $connection): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTablesForConnection(string $connection): array;

    /**
     * @return array<string, mixed>
     */
    public function getTableDetail(string $connection, string $table, ?string $view, Request $request): array;

    /**
     * @return array{columns: array<int, string>, rows: array<int, array<string, mixed>>, total: int, truncated: bool, duration_ms: float}|array{error: string}
     */
    public function executeReadOnlyQuery(string $connection, string $sql): array;

    /**
     * @return array<int, string>
     */
    public function getTableColumnsForExport(string $connection, string $table): array;

    public function streamTableData(string $connection, string $table): StreamedResponse;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getConnectionInfo(): array;

    /**
     * @return array<int, string>
     */
    public function getAvailableConnections(): array;

    public function validateConnection(string $connection): string;
}
