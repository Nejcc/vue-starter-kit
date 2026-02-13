<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use SplFileInfo;

final class LogViewerService
{
    private const LOG_PATTERN = '/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}\.?\d*[+-]?\d*:?\d*)\]\s+(\w+)\.(\w+):\s+(.*)/s';

    private const MAX_ENTRIES = 5000;

    private const MAX_READ_BYTES = 10 * 1024 * 1024; // 10 MB

    /**
     * @return array{
     *     data: array<int, array{id: int, timestamp: string, environment: string, level: string, message: string, context: string|null}>,
     *     total: int,
     *     per_page: int,
     *     current_page: int,
     *     last_page: int,
     *     from: int|null,
     *     to: int|null,
     * }
     */
    public function getPaginated(
        ?string $file = null,
        int $page = 1,
        int $perPage = 50,
        ?string $level = null,
        ?string $search = null,
    ): array {
        $logPath = $this->resolveLogPath($file);
        $entries = $this->parseLogFile($logPath);

        if ($level) {
            $entries = array_values(array_filter(
                $entries,
                fn (array $entry): bool => mb_strtolower($entry['level']) === mb_strtolower($level),
            ));
        }

        if ($search) {
            $searchLower = mb_strtolower($search);
            $entries = array_values(array_filter(
                $entries,
                fn (array $entry): bool => str_contains(mb_strtolower($entry['message']), $searchLower)
                    || ($entry['context'] !== null && str_contains(mb_strtolower($entry['context']), $searchLower)),
            ));
        }

        $total = count($entries);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;
        $data = array_slice($entries, $offset, $perPage);

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
            'from' => $total > 0 ? $offset + 1 : null,
            'to' => $total > 0 ? min($offset + $perPage, $total) : null,
        ];
    }

    /**
     * @return array<int, array{name: string, size: int, lastModified: string}>
     */
    public function getLogFiles(): array
    {
        $logsPath = storage_path('logs');

        if (!File::isDirectory($logsPath)) {
            return [];
        }

        $files = collect(File::files($logsPath))
            ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'log')
            ->map(fn (SplFileInfo $file): array => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'lastModified' => Carbon::createFromTimestamp($file->getMTime())->toDateTimeString(),
            ])
            ->sortByDesc('lastModified')
            ->values()
            ->all();

        return $files;
    }

    /**
     * @return string[]
     */
    public function getDistinctLevels(?string $file = null): array
    {
        return ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];
    }

    private function resolveLogPath(?string $file): string
    {
        if ($file === null || $file === '') {
            return storage_path('logs/laravel.log');
        }

        $filename = basename($file);

        if (!preg_match('/^laravel(-\d{4}-\d{2}-\d{2})?\.log$/', $filename)) {
            return storage_path('logs/laravel.log');
        }

        $path = storage_path('logs/' . $filename);

        return file_exists($path) ? $path : storage_path('logs/laravel.log');
    }

    /**
     * @return array<int, array{id: int, timestamp: string, environment: string, level: string, message: string, context: string|null}>
     */
    private function parseLogFile(string $logPath): array
    {
        if (!file_exists($logPath)) {
            return [];
        }

        $fileSize = filesize($logPath);
        if ($fileSize === 0 || $fileSize === false) {
            return [];
        }

        $readBytes = min($fileSize, self::MAX_READ_BYTES);
        $handle = fopen($logPath, 'r');
        if ($handle === false) {
            return [];
        }

        // Seek to read only the tail of large files
        if ($fileSize > self::MAX_READ_BYTES) {
            fseek($handle, $fileSize - self::MAX_READ_BYTES);
            // Skip partial first line
            fgets($handle);
        }

        $entries = [];
        $currentEntry = null;
        $id = 0;

        while (($line = fgets($handle)) !== false) {
            $line = mb_rtrim($line, "\r\n");

            if (preg_match(self::LOG_PATTERN, $line, $matches)) {
                if ($currentEntry !== null) {
                    $entries[] = $currentEntry;
                }

                $id++;
                $messageParts = $this->splitMessageAndContext($matches[4]);

                $currentEntry = [
                    'id' => $id,
                    'timestamp' => $this->parseTimestamp($matches[1]),
                    'environment' => $matches[2],
                    'level' => mb_strtoupper($matches[3]),
                    'message' => $messageParts['message'],
                    'context' => $messageParts['context'],
                ];

                // Limit total parsed entries
                if ($id > self::MAX_ENTRIES) {
                    array_shift($entries);
                }
            } elseif ($currentEntry !== null && mb_trim($line) !== '') {
                $currentEntry['context'] = ($currentEntry['context'] ?? '') . "\n" . $line;
            }
        }

        if ($currentEntry !== null) {
            $entries[] = $currentEntry;
        }

        fclose($handle);

        // Re-index IDs after potential shifts, newest first
        $reversed = array_reverse($entries);
        foreach ($reversed as $i => &$entry) {
            $entry['id'] = $i + 1;
        }

        return $reversed;
    }

    private function parseTimestamp(string $raw): string
    {
        try {
            return Carbon::parse($raw)->toDateTimeString();
        } catch (Exception) {
            return $raw;
        }
    }

    /**
     * @return array{message: string, context: string|null}
     */
    private function splitMessageAndContext(string $raw): array
    {
        $jsonStart = mb_strpos($raw, ' {');
        if ($jsonStart === false) {
            $jsonStart = mb_strpos($raw, ' [');
        }

        if ($jsonStart !== false) {
            return [
                'message' => mb_trim(mb_substr($raw, 0, $jsonStart)),
                'context' => mb_trim(mb_substr($raw, $jsonStart)),
            ];
        }

        return [
            'message' => mb_trim($raw),
            'context' => null,
        ];
    }
}
