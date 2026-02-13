<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LogViewerService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class LogsController extends Controller
{
    public function __construct(
        private readonly LogViewerService $logViewerService,
    ) {}

    public function index(Request $request): Response
    {
        $file = $request->get('file');
        $logs = $this->logViewerService->getPaginated(
            file: $file,
            page: (int) $request->get('page', 1),
            perPage: 50,
            level: $request->get('level'),
            search: $request->get('search'),
        );

        $levels = $this->logViewerService->getDistinctLevels($file);
        $files = $this->logViewerService->getLogFiles();

        return Inertia::render('admin/Logs/Index', [
            'logs' => $logs,
            'levels' => $levels,
            'files' => $files,
            'filters' => [
                'search' => $request->get('search', ''),
                'level' => $request->get('level', ''),
                'file' => $request->get('file', ''),
            ],
        ]);
    }
}
