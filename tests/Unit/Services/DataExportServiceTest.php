<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\DataExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class DataExportServiceTest extends TestCase
{
    use RefreshDatabase;

    private DataExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DataExportService::class);
    }

    public function test_compile_export_data_returns_all_required_sections(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = $this->service->compileExportData($user);

        $this->assertArrayHasKey('export_metadata', $data);
        $this->assertArrayHasKey('profile', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertArrayHasKey('permissions', $data);
        $this->assertArrayHasKey('notifications', $data);
        $this->assertArrayHasKey('audit_logs', $data);
        $this->assertArrayHasKey('sessions', $data);
    }

    public function test_compile_export_data_has_metadata(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = $this->service->compileExportData($user);

        $this->assertArrayHasKey('exported_at', $data['export_metadata']);
        $this->assertArrayHasKey('version', $data['export_metadata']);
        $this->assertArrayHasKey('user_id', $data['export_metadata']);
        $this->assertEquals($user->id, $data['export_metadata']['user_id']);
    }

    public function test_compile_export_data_logs_audit_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->service->compileExportData($user);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.data_exported',
            'auditable_id' => $user->id,
        ]);
    }

    public function test_compile_export_data_excludes_sensitive_profile_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = $this->service->compileExportData($user);

        $this->assertArrayNotHasKey('password', $data['profile']);
        $this->assertArrayNotHasKey('remember_token', $data['profile']);
        $this->assertArrayNotHasKey('two_factor_secret', $data['profile']);
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $data['profile']);
    }

    public function test_compile_export_data_includes_user_roles(): void
    {
        Role::create(['name' => 'user']);
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->actingAs($user);

        $data = $this->service->compileExportData($user);

        $this->assertIsArray($data['roles']);
        $this->assertContains('user', $data['roles']);
    }
}
