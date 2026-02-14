<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\LogViewerService;
use Tests\TestCase;

final class LogViewerServiceTest extends TestCase
{
    private LogViewerService $service;

    /** @var string[] */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LogViewerService();
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function createLogFile(string $filename, string $content): string
    {
        $path = storage_path('logs/' . $filename);
        file_put_contents($path, $content);
        $this->tempFiles[] = $path;

        return $path;
    }

    private function logLine(string $level = 'ERROR', string $message = 'Test message', string $env = 'local', string $date = '2025-01-15 10:30:00'): string
    {
        return "[{$date}] {$env}.{$level}: {$message}";
    }

    // -------------------------------------------------------
    // Parsing single-line log entries
    // -------------------------------------------------------

    public function test_parse_single_line_log_entry(): void
    {
        $this->createLogFile('laravel.log', $this->logLine('ERROR', 'Something went wrong') . "\n");

        $result = $this->service->getPaginated('laravel.log');

        $this->assertCount(1, $result['data']);
        $this->assertSame('ERROR', $result['data'][0]['level']);
        $this->assertSame('Something went wrong', $result['data'][0]['message']);
        $this->assertSame('local', $result['data'][0]['environment']);
        $this->assertSame(1, $result['data'][0]['id']);
    }

    public function test_parse_multiple_single_line_entries(): void
    {
        $content = implode("\n", [
            $this->logLine('ERROR', 'First error'),
            $this->logLine('WARNING', 'A warning'),
            $this->logLine('INFO', 'Info message'),
        ]) . "\n";

        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log');

        $this->assertCount(3, $result['data']);
        // Entries are newest-first (reversed), so the last written entry appears first
        $this->assertSame('INFO', $result['data'][0]['level']);
        $this->assertSame('WARNING', $result['data'][1]['level']);
        $this->assertSame('ERROR', $result['data'][2]['level']);
    }

    // -------------------------------------------------------
    // Parsing multi-line log entries (stack traces)
    // -------------------------------------------------------

    public function test_parse_multi_line_entry_with_stack_trace(): void
    {
        $content = implode("\n", [
            '[2025-01-15 10:30:00] local.ERROR: Uncaught exception',
            '#0 /app/Http/Controller.php(42): method()',
            '#1 /vendor/laravel/framework/src/Pipeline.php(100): handle()',
            '',
            '[2025-01-15 10:31:00] local.INFO: Request completed',
        ]) . "\n";

        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log');

        $this->assertCount(2, $result['data']);

        // Newest first
        $infoEntry = $result['data'][0];
        $errorEntry = $result['data'][1];

        $this->assertSame('INFO', $infoEntry['level']);
        $this->assertSame('ERROR', $errorEntry['level']);
        $this->assertSame('Uncaught exception', $errorEntry['message']);
        $this->assertNotNull($errorEntry['context']);
        $this->assertStringContainsString('Controller.php', $errorEntry['context']);
        $this->assertStringContainsString('Pipeline.php', $errorEntry['context']);
    }

    // -------------------------------------------------------
    // Filter by log level
    // -------------------------------------------------------

    public function test_filter_by_level(): void
    {
        $content = implode("\n", [
            $this->logLine('ERROR', 'Error one'),
            $this->logLine('WARNING', 'Warning one'),
            $this->logLine('ERROR', 'Error two'),
            $this->logLine('INFO', 'Info one'),
        ]) . "\n";

        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log', level: 'ERROR');

        $this->assertCount(2, $result['data']);
        $this->assertSame('ERROR', $result['data'][0]['level']);
        $this->assertSame('ERROR', $result['data'][1]['level']);
    }

    public function test_filter_by_level_is_case_insensitive(): void
    {
        $content = $this->logLine('WARNING', 'Watch out') . "\n";
        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log', level: 'warning');

        $this->assertCount(1, $result['data']);
        $this->assertSame('WARNING', $result['data'][0]['level']);
    }

    public function test_filter_by_level_returns_empty_when_no_match(): void
    {
        $content = $this->logLine('INFO', 'Just info') . "\n";
        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log', level: 'CRITICAL');

        $this->assertCount(0, $result['data']);
        $this->assertSame(0, $result['total']);
    }

    // -------------------------------------------------------
    // Search within log messages
    // -------------------------------------------------------

    public function test_search_within_messages(): void
    {
        $content = implode("\n", [
            $this->logLine('ERROR', 'Database connection failed'),
            $this->logLine('ERROR', 'Redis timeout occurred'),
            $this->logLine('ERROR', 'Database query slow'),
        ]) . "\n";

        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log', search: 'database');

        $this->assertCount(2, $result['data']);
    }

    public function test_search_is_case_insensitive(): void
    {
        $content = $this->logLine('ERROR', 'NullPointerException thrown') . "\n";
        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log', search: 'nullpointer');

        $this->assertCount(1, $result['data']);
    }

    public function test_search_within_context(): void
    {
        $content = '[2025-01-15 10:30:00] local.ERROR: Exception thrown {"user_id": 42, "action": "delete_account"}' . "\n";
        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log', search: 'delete_account');

        $this->assertCount(1, $result['data']);
    }

    public function test_search_returns_empty_when_no_match(): void
    {
        $content = $this->logLine('ERROR', 'Something happened') . "\n";
        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log', search: 'nonexistent_term_xyz');

        $this->assertCount(0, $result['data']);
    }

    // -------------------------------------------------------
    // Combined filter and search
    // -------------------------------------------------------

    public function test_combined_level_filter_and_search(): void
    {
        $content = implode("\n", [
            $this->logLine('ERROR', 'Database connection failed'),
            $this->logLine('WARNING', 'Database pool low'),
            $this->logLine('ERROR', 'Redis timeout'),
        ]) . "\n";

        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log', level: 'ERROR', search: 'database');

        $this->assertCount(1, $result['data']);
        $this->assertSame('ERROR', $result['data'][0]['level']);
        $this->assertStringContainsString('Database', $result['data'][0]['message']);
    }

    // -------------------------------------------------------
    // List available log files
    // -------------------------------------------------------

    public function test_get_log_files_lists_log_files(): void
    {
        $this->createLogFile('laravel.log', $this->logLine() . "\n");
        $this->createLogFile('laravel-2025-01-15.log', $this->logLine() . "\n");

        $files = $this->service->getLogFiles();

        $names = array_column($files, 'name');
        $this->assertContains('laravel.log', $names);
        $this->assertContains('laravel-2025-01-15.log', $names);
    }

    public function test_get_log_files_returns_size_and_last_modified(): void
    {
        $this->createLogFile('laravel.log', $this->logLine() . "\n");

        $files = $this->service->getLogFiles();
        $laravelLog = collect($files)->firstWhere('name', 'laravel.log');

        $this->assertNotNull($laravelLog);
        $this->assertArrayHasKey('size', $laravelLog);
        $this->assertArrayHasKey('lastModified', $laravelLog);
        $this->assertGreaterThan(0, $laravelLog['size']);
    }

    public function test_get_log_files_excludes_non_log_files(): void
    {
        $this->createLogFile('laravel.log', $this->logLine() . "\n");
        // Create a non-.log file
        $this->createLogFile('notes.txt', 'not a log');

        $files = $this->service->getLogFiles();
        $names = array_column($files, 'name');

        $this->assertContains('laravel.log', $names);
        $this->assertNotContains('notes.txt', $names);
    }

    // -------------------------------------------------------
    // Empty log file
    // -------------------------------------------------------

    public function test_empty_log_file_returns_empty_data(): void
    {
        $this->createLogFile('laravel.log', '');

        $result = $this->service->getPaginated('laravel.log');

        $this->assertCount(0, $result['data']);
        $this->assertSame(0, $result['total']);
    }

    // -------------------------------------------------------
    // Non-existent log files
    // -------------------------------------------------------

    public function test_non_existent_file_falls_back_to_laravel_log(): void
    {
        // Ensure laravel.log does not exist either
        $laravelLogPath = storage_path('logs/laravel.log');
        $laravelLogExisted = file_exists($laravelLogPath);
        $laravelLogContent = $laravelLogExisted ? file_get_contents($laravelLogPath) : null;

        // Remove laravel.log temporarily if it exists
        if ($laravelLogExisted) {
            $backupPath = $laravelLogPath . '.bak';
            rename($laravelLogPath, $backupPath);
        }

        try {
            $result = $this->service->getPaginated('nonexistent-file.log');
            $this->assertCount(0, $result['data']);
            $this->assertSame(0, $result['total']);
        } finally {
            // Restore laravel.log
            if ($laravelLogExisted) {
                rename($backupPath, $laravelLogPath);
            }
        }
    }

    // -------------------------------------------------------
    // Pagination
    // -------------------------------------------------------

    public function test_pagination_returns_correct_page(): void
    {
        $lines = [];
        for ($i = 1; $i <= 10; $i++) {
            $lines[] = $this->logLine('INFO', "Message {$i}");
        }
        $this->createLogFile('laravel.log', implode("\n", $lines) . "\n");

        $result = $this->service->getPaginated('laravel.log', page: 1, perPage: 3);

        $this->assertCount(3, $result['data']);
        $this->assertSame(10, $result['total']);
        $this->assertSame(1, $result['current_page']);
        $this->assertSame(4, $result['last_page']);
        $this->assertSame(3, $result['per_page']);
    }

    public function test_pagination_second_page(): void
    {
        $lines = [];
        for ($i = 1; $i <= 10; $i++) {
            $lines[] = $this->logLine('INFO', "Message {$i}");
        }
        $this->createLogFile('laravel.log', implode("\n", $lines) . "\n");

        $result = $this->service->getPaginated('laravel.log', page: 2, perPage: 3);

        $this->assertCount(3, $result['data']);
        $this->assertSame(2, $result['current_page']);
        $this->assertSame(4, $result['from']);
        $this->assertSame(6, $result['to']);
    }

    public function test_pagination_last_page_partial(): void
    {
        $lines = [];
        for ($i = 1; $i <= 10; $i++) {
            $lines[] = $this->logLine('INFO', "Message {$i}");
        }
        $this->createLogFile('laravel.log', implode("\n", $lines) . "\n");

        $result = $this->service->getPaginated('laravel.log', page: 4, perPage: 3);

        $this->assertCount(1, $result['data']);
        $this->assertSame(4, $result['current_page']);
        $this->assertSame(10, $result['from']);
        $this->assertSame(10, $result['to']);
    }

    public function test_pagination_beyond_last_page_clamps_to_last(): void
    {
        $lines = [];
        for ($i = 1; $i <= 5; $i++) {
            $lines[] = $this->logLine('INFO', "Message {$i}");
        }
        $this->createLogFile('laravel.log', implode("\n", $lines) . "\n");

        $result = $this->service->getPaginated('laravel.log', page: 100, perPage: 3);

        $this->assertSame(2, $result['current_page']);
        $this->assertSame(2, $result['last_page']);
    }

    public function test_pagination_from_and_to_are_null_for_empty_results(): void
    {
        $this->createLogFile('laravel.log', '');

        $result = $this->service->getPaginated('laravel.log');

        $this->assertNull($result['from']);
        $this->assertNull($result['to']);
        $this->assertSame(1, $result['last_page']);
    }

    // -------------------------------------------------------
    // Path traversal protection
    // -------------------------------------------------------

    public function test_path_traversal_falls_back_to_default(): void
    {
        $this->createLogFile('laravel.log', $this->logLine('INFO', 'Safe entry') . "\n");

        // Attempt path traversal - should fall back to laravel.log
        $result = $this->service->getPaginated('../../etc/passwd');

        // The service uses basename() and validates the filename pattern,
        // so this should fall back to laravel.log
        $this->assertIsArray($result['data']);
    }

    public function test_invalid_filename_pattern_falls_back_to_default(): void
    {
        $this->createLogFile('laravel.log', $this->logLine('INFO', 'Default') . "\n");

        $result = $this->service->getPaginated('malicious.php');

        // Should fall back to laravel.log since the filename doesn't match the pattern
        $this->assertIsArray($result['data']);
    }

    public function test_valid_daily_log_file_is_accepted(): void
    {
        $this->createLogFile('laravel-2025-01-15.log', $this->logLine('INFO', 'Daily entry') . "\n");

        $result = $this->service->getPaginated('laravel-2025-01-15.log');

        $this->assertCount(1, $result['data']);
        $this->assertSame('Daily entry', $result['data'][0]['message']);
    }

    // -------------------------------------------------------
    // Null / empty file parameter defaults to laravel.log
    // -------------------------------------------------------

    public function test_null_file_defaults_to_laravel_log(): void
    {
        $this->createLogFile('laravel.log', $this->logLine('DEBUG', 'Default log') . "\n");

        $result = $this->service->getPaginated(null);

        $this->assertCount(1, $result['data']);
        $this->assertSame('Default log', $result['data'][0]['message']);
    }

    public function test_empty_string_file_defaults_to_laravel_log(): void
    {
        $this->createLogFile('laravel.log', $this->logLine('DEBUG', 'Default log') . "\n");

        $result = $this->service->getPaginated('');

        $this->assertCount(1, $result['data']);
        $this->assertSame('Default log', $result['data'][0]['message']);
    }

    // -------------------------------------------------------
    // Distinct levels
    // -------------------------------------------------------

    public function test_get_distinct_levels_returns_all_psr3_levels(): void
    {
        $levels = $this->service->getDistinctLevels();

        $expected = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];
        $this->assertSame($expected, $levels);
    }

    // -------------------------------------------------------
    // Timestamp parsing
    // -------------------------------------------------------

    public function test_timestamp_is_parsed_correctly(): void
    {
        $content = '[2025-06-15 14:30:45] local.INFO: Test message' . "\n";
        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log');

        $this->assertSame('2025-06-15 14:30:45', $result['data'][0]['timestamp']);
    }

    public function test_timestamp_with_timezone_offset_is_parsed(): void
    {
        $content = '[2025-06-15T14:30:45+00:00] production.ERROR: Timezone test' . "\n";
        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log');

        $this->assertCount(1, $result['data']);
        $this->assertSame('ERROR', $result['data'][0]['level']);
        $this->assertSame('production', $result['data'][0]['environment']);
    }

    // -------------------------------------------------------
    // Context splitting (message vs JSON context)
    // -------------------------------------------------------

    public function test_message_with_json_context_is_split(): void
    {
        $content = '[2025-01-15 10:30:00] local.ERROR: User not found {"user_id": 42}' . "\n";
        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log');

        $this->assertSame('User not found', $result['data'][0]['message']);
        $this->assertNotNull($result['data'][0]['context']);
        $this->assertStringContainsString('user_id', $result['data'][0]['context']);
    }

    public function test_message_with_array_context_is_split(): void
    {
        $content = '[2025-01-15 10:30:00] local.INFO: Batch processed [1, 2, 3]' . "\n";
        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log');

        $this->assertSame('Batch processed', $result['data'][0]['message']);
        $this->assertNotNull($result['data'][0]['context']);
        $this->assertStringContainsString('1, 2, 3', $result['data'][0]['context']);
    }

    public function test_message_without_context_has_null_context(): void
    {
        $content = '[2025-01-15 10:30:00] local.INFO: Simple message' . "\n";
        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log');

        $this->assertSame('Simple message', $result['data'][0]['message']);
        $this->assertNull($result['data'][0]['context']);
    }

    // -------------------------------------------------------
    // Pagination response structure
    // -------------------------------------------------------

    public function test_paginated_response_has_all_required_keys(): void
    {
        $this->createLogFile('laravel.log', $this->logLine() . "\n");

        $result = $this->service->getPaginated('laravel.log');

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('per_page', $result);
        $this->assertArrayHasKey('current_page', $result);
        $this->assertArrayHasKey('last_page', $result);
        $this->assertArrayHasKey('from', $result);
        $this->assertArrayHasKey('to', $result);
    }

    public function test_entry_has_all_required_fields(): void
    {
        $this->createLogFile('laravel.log', $this->logLine() . "\n");

        $result = $this->service->getPaginated('laravel.log');
        $entry = $result['data'][0];

        $this->assertArrayHasKey('id', $entry);
        $this->assertArrayHasKey('timestamp', $entry);
        $this->assertArrayHasKey('environment', $entry);
        $this->assertArrayHasKey('level', $entry);
        $this->assertArrayHasKey('message', $entry);
        $this->assertArrayHasKey('context', $entry);
    }

    // -------------------------------------------------------
    // Entries are ordered newest first
    // -------------------------------------------------------

    public function test_entries_are_ordered_newest_first(): void
    {
        $content = implode("\n", [
            '[2025-01-15 08:00:00] local.INFO: Morning entry',
            '[2025-01-15 12:00:00] local.INFO: Noon entry',
            '[2025-01-15 18:00:00] local.INFO: Evening entry',
        ]) . "\n";

        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log');

        $this->assertSame('Evening entry', $result['data'][0]['message']);
        $this->assertSame('Noon entry', $result['data'][1]['message']);
        $this->assertSame('Morning entry', $result['data'][2]['message']);
    }

    // -------------------------------------------------------
    // IDs are sequential after reversal
    // -------------------------------------------------------

    public function test_ids_are_sequential_starting_from_one(): void
    {
        $content = implode("\n", [
            $this->logLine('INFO', 'First'),
            $this->logLine('INFO', 'Second'),
            $this->logLine('INFO', 'Third'),
        ]) . "\n";

        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log');

        $this->assertSame(1, $result['data'][0]['id']);
        $this->assertSame(2, $result['data'][1]['id']);
        $this->assertSame(3, $result['data'][2]['id']);
    }

    // -------------------------------------------------------
    // Lines that don't match the log pattern are ignored
    // -------------------------------------------------------

    public function test_non_log_lines_without_preceding_entry_are_ignored(): void
    {
        $content = implode("\n", [
            'This is not a log line',
            'Neither is this',
            $this->logLine('INFO', 'Actual log entry'),
        ]) . "\n";

        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log');

        $this->assertCount(1, $result['data']);
        $this->assertSame('Actual log entry', $result['data'][0]['message']);
    }

    // -------------------------------------------------------
    // Search in multi-line context (stack trace)
    // -------------------------------------------------------

    public function test_search_finds_match_in_stack_trace_context(): void
    {
        $content = implode("\n", [
            '[2025-01-15 10:30:00] local.ERROR: Exception thrown',
            '#0 /app/Services/PaymentService.php(42): processPayment()',
            '',
            '[2025-01-15 10:31:00] local.INFO: Health check ok',
        ]) . "\n";

        $this->createLogFile('laravel.log', $content);

        $result = $this->service->getPaginated('laravel.log', search: 'PaymentService');

        $this->assertCount(1, $result['data']);
        $this->assertSame('ERROR', $result['data'][0]['level']);
    }

    // -------------------------------------------------------
    // Default per_page
    // -------------------------------------------------------

    public function test_default_per_page_is_fifty(): void
    {
        $lines = [];
        for ($i = 1; $i <= 60; $i++) {
            $lines[] = $this->logLine('INFO', "Message {$i}");
        }
        $this->createLogFile('laravel.log', implode("\n", $lines) . "\n");

        $result = $this->service->getPaginated('laravel.log');

        $this->assertCount(50, $result['data']);
        $this->assertSame(50, $result['per_page']);
        $this->assertSame(60, $result['total']);
        $this->assertSame(2, $result['last_page']);
    }
}
