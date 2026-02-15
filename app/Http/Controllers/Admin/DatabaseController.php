<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\DatabaseBrowserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExecuteQueryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin database controller.
 *
 * Handles displaying database tables and their structure.
 */
final class DatabaseController extends Controller
{
    public function __construct(
        private readonly DatabaseBrowserServiceInterface $databaseService,
    ) {}

    /**
     * Show the database tables index page.
     */
    public function index(Request $request, ?string $connection = null): Response|RedirectResponse
    {
        if ($connection === null && !$request->has('connection')) {
            return redirect()->route('admin.databases.index');
        }

        $data = $this->databaseService->getTablesIndex($request, $connection);

        if ($data === null) {
            $connectionTest = $this->databaseService->testConnection(
                $connection ?? $request->get('connection', config('database.default'))
            );

            if (!$connectionTest['success']) {
                return redirect()
                    ->route('admin.databases.index')
                    ->withErrors(['connection' => 'Failed to connect to database: ' . $connectionTest['error']]);
            }

            return redirect()->route('admin.databases.index');
        }

        return Inertia::render('admin/Database/Index', $data);
    }

    /**
     * Show details for a specific table.
     */
    public function show(Request $request, string $connection, string $table, ?string $view = null): Response
    {
        return Inertia::render(
            'admin/Database/Show',
            $this->databaseService->getTableDetail($request, $connection, $table, $view),
        );
    }

    /**
     * Execute a read-only SQL query.
     */
    public function query(ExecuteQueryRequest $request): JsonResponse
    {
        $sql = mb_trim($request->input('query'));
        $connection = $request->input('connection', config('database.default'));

        $result = $this->databaseService->executeQuery($sql, $connection);

        if (isset($result['error'])) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Export table data as CSV.
     */
    public function export(string $connection, string $table): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->databaseService->exportTable($connection, $table);
    }

    /**
     * List all database connections.
     */
    public function listConnections(): Response
    {
        return Inertia::render('admin/Databases/Index', [
            'connections' => $this->databaseService->getConnectionsListing(),
        ]);
    }
}
