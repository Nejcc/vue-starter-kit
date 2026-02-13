<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class LogsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($superAdminRole);

        $this->logPath = storage_path('logs/laravel.log');
    }

    protected function tearDown(): void
    {
        // Clean up any test log files
        foreach (glob(storage_path('logs/laravel-*.log')) as $file) {
            if (str_contains($file, 'test-')) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    public function test_guests_cannot_access_logs(): void
    {
        $response = $this->get(route('admin.logs.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_logs(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $response = $this->actingAs($user)->get(route('admin.logs.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_logs(): void
    {
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        $response = $this->actingAs($adminUser)->get(route('admin.logs.index'));

        $response->assertStatus(200);
    }

    public function test_logs_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.logs.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Logs/Index')
            ->has('logs')
            ->has('levels')
            ->has('files')
            ->has('filters')
        );
    }

    public function test_logs_display_entries_from_log_file(): void
    {
        $this->writeTestLog("[2026-02-13 10:00:00] local.ERROR: Test error message {\"key\":\"value\"}\n");

        $response = $this->actingAs($this->admin)->get(route('admin.logs.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('logs.total', fn ($total) => $total >= 1)
        );
    }

    public function test_logs_filter_by_level(): void
    {
        $this->writeTestLog(
            "[2026-02-13 10:00:00] local.ERROR: Test error\n" .
            "[2026-02-13 10:00:01] local.INFO: Test info\n"
        );

        $response = $this->actingAs($this->admin)->get(route('admin.logs.index', ['level' => 'ERROR']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('logs.data', fn ($data) => collect($data)->every(fn ($entry) => $entry['level'] === 'ERROR'))
        );
    }

    public function test_logs_search_by_message(): void
    {
        $uniqueTerm = 'UNIQUELOGTERM_' . uniqid();
        $this->writeTestLog(
            "[2026-02-13 10:00:00] local.ERROR: {$uniqueTerm}\n" .
            "[2026-02-13 10:00:01] local.INFO: Regular message\n"
        );

        $response = $this->actingAs($this->admin)->get(route('admin.logs.index', ['search' => $uniqueTerm]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('logs.total', 1)
            ->where('logs.data.0.message', $uniqueTerm)
        );
    }

    public function test_logs_empty_state(): void
    {
        // Backup and remove the log file temporarily
        $backup = null;
        if (file_exists($this->logPath)) {
            $backup = file_get_contents($this->logPath);
            file_put_contents($this->logPath, '');
        }

        $response = $this->actingAs($this->admin)->get(route('admin.logs.index'));

        // Restore the log file
        if ($backup !== null) {
            file_put_contents($this->logPath, $backup);
        }

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('logs.total', 0)
        );
    }

    public function test_logs_returns_available_files(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.logs.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('files')
        );
    }

    public function test_logs_filters_are_passed_back(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.logs.index', [
            'search' => 'test',
            'level' => 'ERROR',
            'file' => 'laravel.log',
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.search', 'test')
            ->where('filters.level', 'ERROR')
            ->where('filters.file', 'laravel.log')
        );
    }

    public function test_logs_rejects_path_traversal_in_file_param(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.logs.index', [
            'file' => '../../.env',
        ]));

        $response->assertStatus(200);
    }

    private function writeTestLog(string $content): void
    {
        File::ensureDirectoryExists(dirname($this->logPath));
        file_put_contents($this->logPath, $content, FILE_APPEND);
    }
}
