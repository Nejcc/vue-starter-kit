<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Contracts\Services\DataExportServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DataExportController extends Controller
{
    public function __construct(
        private readonly DataExportServiceInterface $dataExportService,
    ) {}

    /**
     * Show the data export page.
     */
    public function show(): Response
    {
        return Inertia::render('settings/DataExport');
    }

    /**
     * Download user's personal data as JSON.
     */
    public function download(Request $request): StreamedResponse
    {
        $user = $request->user();
        $data = $this->dataExportService->compileExportData($user);
        $filename = 'user-data-export-' . now()->format('Y-m-d') . '.json';

        return response()->streamDownload(function () use ($data): void {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
}
