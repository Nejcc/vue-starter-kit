<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Constants\AuditEvent;
use App\Constants\RoleNames;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DataExportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_data_export_page(): void
    {
        $response = $this->get(route('data-export.show'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_download_data_export(): void
    {
        $response = $this->get(route('data-export.download'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_data_export_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('data-export.show'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/DataExport'));
    }

    public function test_download_returns_json_with_correct_content_type(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('data-export.download'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_download_contains_profile_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('data-export.download'));
        $data = json_decode($response->streamedContent(), true);

        $this->assertArrayHasKey('profile', $data);
        $this->assertEquals('Test User', $data['profile']['name']);
        $this->assertEquals('test@example.com', $data['profile']['email']);
    }

    public function test_download_excludes_sensitive_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('data-export.download'));
        $data = json_decode($response->streamedContent(), true);

        $this->assertArrayNotHasKey('password', $data['profile']);
        $this->assertArrayNotHasKey('remember_token', $data['profile']);
        $this->assertArrayNotHasKey('two_factor_secret', $data['profile']);
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $data['profile']);
    }

    public function test_download_contains_roles(): void
    {
        Role::create(['name' => RoleNames::USER]);
        $user = User::factory()->create();
        $user->assignRole(RoleNames::USER);

        $response = $this->actingAs($user)->get(route('data-export.download'));
        $data = json_decode($response->streamedContent(), true);

        $this->assertArrayHasKey('roles', $data);
        $this->assertContains('user', $data['roles']);
    }

    public function test_download_contains_permissions(): void
    {
        Role::create(['name' => RoleNames::USER]);
        Permission::create(['name' => 'view-dashboard']);
        $user = User::factory()->create();
        $user->assignRole(RoleNames::USER);
        $user->givePermissionTo('view-dashboard');

        $response = $this->actingAs($user)->get(route('data-export.download'));
        $data = json_decode($response->streamedContent(), true);

        $this->assertArrayHasKey('permissions', $data);
        $this->assertContains('view-dashboard', $data['permissions']);
    }

    public function test_download_contains_notifications(): void
    {
        $user = User::factory()->create();

        DB::table('notifications')->insert([
            'id' => fake()->uuid(),
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['message' => 'Hello']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('data-export.download'));
        $data = json_decode($response->streamedContent(), true);

        $this->assertArrayHasKey('notifications', $data);
        $this->assertCount(1, $data['notifications']);
        $this->assertEquals('App\\Notifications\\TestNotification', $data['notifications'][0]['type']);
    }

    public function test_download_contains_audit_logs(): void
    {
        $user = User::factory()->create();

        AuditLog::log('auth.login', $user, null, null, $user->id);

        $response = $this->actingAs($user)->get(route('data-export.download'));
        $data = json_decode($response->streamedContent(), true);

        $this->assertArrayHasKey('audit_logs', $data);
        $this->assertNotEmpty($data['audit_logs']);
        $this->assertEquals('auth.login', $data['audit_logs'][0]['event']);
    }

    public function test_download_contains_sessions(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'test-session-id',
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test Agent',
            'payload' => base64_encode(serialize(['secret' => 'data'])),
            'last_activity' => time(),
        ]);

        $response = $this->actingAs($user)->get(route('data-export.download'));
        $data = json_decode($response->streamedContent(), true);

        $this->assertArrayHasKey('sessions', $data);
        $this->assertNotEmpty($data['sessions']);
        $this->assertEquals('192.168.1.1', $data['sessions'][0]['ip_address']);
    }

    public function test_sessions_exclude_payload(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'payload-test-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'payload' => base64_encode(serialize(['secret' => 'sensitive-data'])),
            'last_activity' => time(),
        ]);

        $response = $this->actingAs($user)->get(route('data-export.download'));
        $data = json_decode($response->streamedContent(), true);

        foreach ($data['sessions'] as $session) {
            $this->assertArrayNotHasKey('payload', $session);
        }
    }

    public function test_download_contains_export_metadata(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('data-export.download'));
        $data = json_decode($response->streamedContent(), true);

        $this->assertArrayHasKey('export_metadata', $data);
        $this->assertArrayHasKey('exported_at', $data['export_metadata']);
        $this->assertEquals('1.0', $data['export_metadata']['version']);
        $this->assertEquals($user->id, $data['export_metadata']['user_id']);
    }

    public function test_download_does_not_include_other_users_data(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        AuditLog::log('auth.login', $user, null, null, $user->id);
        AuditLog::log('auth.login', $otherUser, null, null, $otherUser->id);

        DB::table('sessions')->insert([
            'id' => 'other-user-session',
            'user_id' => $otherUser->id,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Other Agent',
            'payload' => base64_encode(serialize([])),
            'last_activity' => time(),
        ]);

        $response = $this->actingAs($user)->get(route('data-export.download'));
        $data = json_decode($response->streamedContent(), true);

        // Audit logs should only be for the authenticated user
        foreach ($data['audit_logs'] as $log) {
            $this->assertNotEquals($otherUser->id, $log['id']);
        }

        // Sessions should only be for the authenticated user
        foreach ($data['sessions'] as $session) {
            $this->assertNotEquals('other-user-session', $session['id']);
        }
    }

    public function test_download_logs_audit_event(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('data-export.download'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => AuditEvent::USER_DATA_EXPORTED,
        ]);
    }

    public function test_download_returns_valid_json(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('data-export.download'));
        $content = $response->streamedContent();

        $this->assertNotNull(json_decode($content));
        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
    }

    public function test_download_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            $response = $this->actingAs($user)->get(route('data-export.download'));
            $response->assertOk();
        }

        $response = $this->actingAs($user)->get(route('data-export.download'));
        $response->assertStatus(429);
    }
}
