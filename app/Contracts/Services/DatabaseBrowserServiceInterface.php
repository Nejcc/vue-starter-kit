<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface DatabaseBrowserServiceInterface
{
    /**
     * @return array{tables: array<int, array<string, mixed>>, connections: array<int, string>, currentConnection: string, driver: string}|null
     */
    public function getTablesIndex(Request $request, ?string $connection): ?array;

    /**
     * @return array<string, mixed>
     */
    public function getTableDetail(Request $request, string $connection, string $table, ?string $view): array;

    /**
     * @return array<string, mixed>
     */
    public function executeQuery(string $sql, string $connection): array;

    public function exportTable(string $connection, string $table): StreamedResponse;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getConnectionsListing(): array;

    public function resolveConnection(Request $request, ?string $connection): ?string;

    /**
     * @return array{success: bool, driver?: string, error?: string}
     */
    public function testConnection(string $connection): array;
}
