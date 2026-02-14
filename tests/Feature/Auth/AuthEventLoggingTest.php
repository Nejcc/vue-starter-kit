<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AuthEventLoggingTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService(new UserRepository());
    }

    /**
     * Test that a Login event creates an audit log with event 'auth.login'.
     */
    public function test_login_event_creates_audit_log(): void
    {
        $user = User::factory()->create();

        Event::dispatch(new Login('web', $user, false));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'auth.login',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);
    }

    /**
     * Test that a Logout event creates an audit log with event 'auth.logout'.
     */
    public function test_logout_event_creates_audit_log(): void
    {
        $user = User::factory()->create();

        Event::dispatch(new Logout('web', $user));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'auth.logout',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);
    }

    /**
     * Test that a Registered event creates an audit log with event 'auth.registered'
     * and stores name/email in new_values.
     */
    public function test_registered_event_creates_audit_log_with_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);

        Event::dispatch(new Registered($user));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'auth.registered',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);

        $auditLog = AuditLog::query()
            ->where('event', 'auth.registered')
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('New User', $auditLog->new_values['name']);
        $this->assertEquals('newuser@example.com', $auditLog->new_values['email']);
    }

    /**
     * Test that a Failed login event creates an audit log with event 'auth.login_failed'
     * and stores email in new_values.
     */
    public function test_failed_login_event_creates_audit_log_with_email(): void
    {
        Event::dispatch(new Failed('web', null, [
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]));

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'auth.login_failed',
        ]);

        $auditLog = AuditLog::query()
            ->where('event', 'auth.login_failed')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertNull($auditLog->user_id);
        $this->assertEquals('nonexistent@example.com', $auditLog->new_values['email']);
    }

    /**
     * Test that a PasswordReset event creates an audit log with event 'auth.password_reset'.
     */
    public function test_password_reset_event_creates_audit_log(): void
    {
        $user = User::factory()->create();

        Event::dispatch(new PasswordReset($user));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'auth.password_reset',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);
    }

    /**
     * Test that a Verified event creates an audit log with event 'auth.email_verified'.
     */
    public function test_email_verified_event_creates_audit_log(): void
    {
        $user = User::factory()->create();

        Event::dispatch(new Verified($user));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'auth.email_verified',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);
    }

    /**
     * Test that UserService::updateProfile creates an audit log with event
     * 'user.profile_updated' containing old and new values.
     */
    public function test_profile_update_creates_audit_log_with_old_and_new_values(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $this->userService->updateProfile($user->id, [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.profile_updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);

        $auditLog = AuditLog::query()
            ->where('event', 'user.profile_updated')
            ->where('auditable_id', $user->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('Original Name', $auditLog->old_values['name']);
        $this->assertEquals('original@example.com', $auditLog->old_values['email']);
        $this->assertEquals('Updated Name', $auditLog->new_values['name']);
        $this->assertEquals('updated@example.com', $auditLog->new_values['email']);
    }

    /**
     * Test that UserService::updatePassword creates an audit log with event
     * 'user.password_changed'.
     */
    public function test_password_change_creates_audit_log(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->userService->updatePassword($user->id, 'password', 'new_password123');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.password_changed',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);
    }

    /**
     * Test that UserService::delete creates an audit log with event
     * 'user.account_deleted' containing user data in old_values.
     */
    public function test_account_deletion_creates_audit_log_with_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Deleted User',
            'email' => 'deleted@example.com',
            'password' => Hash::make('password'),
        ]);

        $userId = $user->id;

        $this->userService->delete($user->id, 'password');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.account_deleted',
        ]);

        $auditLog = AuditLog::query()
            ->where('event', 'user.account_deleted')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('Deleted User', $auditLog->old_values['name']);
        $this->assertEquals('deleted@example.com', $auditLog->old_values['email']);
        $this->assertEquals($userId, $auditLog->auditable_id);
        $this->assertEquals(User::class, $auditLog->auditable_type);
    }
}
