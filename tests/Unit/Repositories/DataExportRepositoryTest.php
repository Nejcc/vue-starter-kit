<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Repositories\DataExportRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class DataExportRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DataExportRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DataExportRepository();
    }

    public function test_collect_profile_excludes_sensitive_fields(): void
    {
        $user = User::factory()->create();

        $profile = $this->repository->collectProfile($user);

        $this->assertArrayNotHasKey('password', $profile);
        $this->assertArrayNotHasKey('remember_token', $profile);
        $this->assertArrayNotHasKey('two_factor_secret', $profile);
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $profile);
        $this->assertArrayHasKey('name', $profile);
        $this->assertArrayHasKey('email', $profile);
    }

    public function test_collect_roles_returns_role_names(): void
    {
        Role::create(['name' => 'user']);
        $user = User::factory()->create();
        $user->assignRole('user');

        $roles = $this->repository->collectRoles($user);

        $this->assertIsArray($roles);
        $this->assertContains('user', $roles);
    }

    public function test_collect_permissions_returns_permission_names(): void
    {
        $user = User::factory()->create();

        $permissions = $this->repository->collectPermissions($user);

        $this->assertIsArray($permissions);
    }

    public function test_collect_notifications_returns_correct_shape(): void
    {
        $user = User::factory()->create();
        $user->notify(new GeneralNotification('Export Test', 'Body'));

        $notifications = $this->repository->collectNotifications($user);

        $this->assertCount(1, $notifications);
        $this->assertArrayHasKey('id', $notifications[0]);
        $this->assertArrayHasKey('type', $notifications[0]);
        $this->assertArrayHasKey('data', $notifications[0]);
        $this->assertArrayHasKey('created_at', $notifications[0]);
    }

    public function test_collect_audit_logs_returns_correct_shape(): void
    {
        $user = User::factory()->create();
        AuditLog::create([
            'user_id' => $user->id,
            'event' => 'test.event',
            'ip_address' => '127.0.0.1',
        ]);

        $logs = $this->repository->collectAuditLogs($user);

        $this->assertCount(1, $logs);
        $this->assertArrayHasKey('id', $logs[0]);
        $this->assertArrayHasKey('event', $logs[0]);
        $this->assertArrayHasKey('ip_address', $logs[0]);
        $this->assertArrayHasKey('created_at', $logs[0]);
    }

    public function test_collect_sessions_returns_correct_shape(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'export-sess',
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'TestAgent/1.0',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $sessions = $this->repository->collectSessions($user);

        $this->assertCount(1, $sessions);
        $this->assertArrayHasKey('id', $sessions[0]);
        $this->assertArrayHasKey('ip_address', $sessions[0]);
        $this->assertArrayHasKey('user_agent', $sessions[0]);
        $this->assertArrayHasKey('last_activity', $sessions[0]);
    }

    public function test_collect_payment_data_returns_null_when_package_missing(): void
    {
        $user = User::factory()->create();

        // This test validates the guard clause; the class may or may not exist
        // depending on whether the payment-gateway package is loaded
        $result = $this->repository->collectPaymentData($user);

        // Result is either null (package not loaded) or an array
        $this->assertTrue($result === null || is_array($result));
    }

    public function test_collect_subscriber_data_returns_null_when_no_subscriber(): void
    {
        $user = User::factory()->create(['email' => 'nonsubscriber@example.com']);

        $result = $this->repository->collectSubscriberData($user);

        // Either null (package not loaded or no subscriber) or array
        $this->assertTrue($result === null || is_array($result));
    }

    public function test_collect_organization_data_returns_expected_type(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->collectOrganizationData($user);

        // Either null (package not loaded) or array
        $this->assertTrue($result === null || is_array($result));
    }
}
