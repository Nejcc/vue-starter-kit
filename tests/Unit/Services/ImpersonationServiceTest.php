<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Constants\RoleNames;
use App\Models\Role;
use App\Models\User;
use App\Services\ImpersonationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

final class ImpersonationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ImpersonationService $service;

    private User $admin;

    private User $targetUser;

    private Role $superAdminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ImpersonationService::class);
        $this->superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        Role::create(['name' => RoleNames::ADMIN]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->superAdminRole);

        $this->targetUser = User::factory()->create();
    }

    private function createRequestWithSession(): Request
    {
        $request = Request::create('/');
        $session = new Store('test', new ArraySessionHandler(120));
        $session->start();
        $request->setLaravelSession($session);

        return $request;
    }

    // ─── canImpersonate ──────────────────────────────────────────────

    public function test_super_admin_can_impersonate(): void
    {
        $this->assertTrue($this->service->canImpersonate($this->admin));
    }

    public function test_admin_can_impersonate(): void
    {
        $adminUser = User::factory()->create();
        $adminUser->assignRole(RoleNames::ADMIN);

        $this->assertTrue($this->service->canImpersonate($adminUser));
    }

    public function test_regular_user_cannot_impersonate(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($this->service->canImpersonate($user));
    }

    // ─── startImpersonation ──────────────────────────────────────────

    public function test_start_impersonation_succeeds(): void
    {
        $this->actingAs($this->admin);
        $request = $this->createRequestWithSession();

        $result = $this->service->startImpersonation($this->admin, $this->targetUser->id, $request);

        $this->assertTrue($result['success']);
        $this->assertEquals($this->targetUser->id, $result['user']->id);
        $this->assertEquals($this->targetUser->id, Auth::id());
    }

    public function test_start_impersonation_creates_audit_log(): void
    {
        $this->actingAs($this->admin);
        $request = $this->createRequestWithSession();

        $this->service->startImpersonation($this->admin, $this->targetUser->id, $request);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'impersonation.started',
            'user_id' => $this->admin->id,
            'auditable_type' => User::class,
            'auditable_id' => $this->targetUser->id,
        ]);
    }

    public function test_cannot_impersonate_yourself(): void
    {
        $this->actingAs($this->admin);
        $request = $this->createRequestWithSession();

        $result = $this->service->startImpersonation($this->admin, $this->admin->id, $request);

        $this->assertFalse($result['success']);
        $this->assertEquals('You cannot impersonate yourself.', $result['error']);
    }

    public function test_cannot_impersonate_nonexistent_user(): void
    {
        $this->actingAs($this->admin);
        $request = $this->createRequestWithSession();

        $result = $this->service->startImpersonation($this->admin, 99999, $request);

        $this->assertFalse($result['success']);
        $this->assertEquals('User not found.', $result['error']);
    }

    public function test_start_impersonation_stores_session_flag(): void
    {
        $this->actingAs($this->admin);
        $request = $this->createRequestWithSession();

        $this->service->startImpersonation($this->admin, $this->targetUser->id, $request);

        $this->assertTrue($this->service->isImpersonating($request));
    }

    // ─── stopImpersonation ───────────────────────────────────────────

    public function test_stop_impersonation_returns_to_original_user(): void
    {
        $this->actingAs($this->admin);
        $request = $this->createRequestWithSession();

        $this->service->startImpersonation($this->admin, $this->targetUser->id, $request);
        $result = $this->service->stopImpersonation($request);

        $this->assertTrue($result['success']);
        $this->assertEquals($this->admin->id, $result['impersonator']->id);
        $this->assertEquals($this->admin->id, Auth::id());
    }

    public function test_stop_impersonation_clears_session(): void
    {
        $this->actingAs($this->admin);
        $request = $this->createRequestWithSession();

        $this->service->startImpersonation($this->admin, $this->targetUser->id, $request);
        $this->service->stopImpersonation($request);

        $this->assertFalse($this->service->isImpersonating($request));
    }

    public function test_stop_impersonation_creates_audit_log(): void
    {
        $this->actingAs($this->admin);
        $request = $this->createRequestWithSession();

        $this->service->startImpersonation($this->admin, $this->targetUser->id, $request);
        $this->service->stopImpersonation($request);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'impersonation.stopped',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_stop_impersonation_fails_when_not_impersonating(): void
    {
        $this->actingAs($this->admin);
        $request = $this->createRequestWithSession();

        $result = $this->service->stopImpersonation($request);

        $this->assertFalse($result['success']);
        $this->assertEquals('No active impersonation session.', $result['error']);
    }

    public function test_stop_impersonation_handles_deleted_impersonator(): void
    {
        $this->actingAs($this->admin);
        $request = $this->createRequestWithSession();

        $this->service->startImpersonation($this->admin, $this->targetUser->id, $request);

        // Delete the original admin
        $this->admin->delete();

        $result = $this->service->stopImpersonation($request);

        $this->assertFalse($result['success']);
        $this->assertTrue($result['logout'] ?? false);
    }

    // ─── isImpersonating ─────────────────────────────────────────────

    public function test_is_not_impersonating_by_default(): void
    {
        $request = $this->createRequestWithSession();

        $this->assertFalse($this->service->isImpersonating($request));
    }

    // ─── getImpersonator ─────────────────────────────────────────────

    public function test_get_impersonator_returns_null_when_not_impersonating(): void
    {
        $request = $this->createRequestWithSession();

        $this->assertNull($this->service->getImpersonator($request));
    }

    public function test_get_impersonator_returns_original_user(): void
    {
        $this->actingAs($this->admin);
        $request = $this->createRequestWithSession();

        $this->service->startImpersonation($this->admin, $this->targetUser->id, $request);

        $impersonator = $this->service->getImpersonator($request);

        $this->assertNotNull($impersonator);
        $this->assertEquals($this->admin->id, $impersonator->id);
    }

    // ─── getUsersForImpersonation ────────────────────────────────────

    public function test_get_users_for_impersonation_excludes_current_user(): void
    {
        $users = $this->service->getUsersForImpersonation($this->admin->id);

        $this->assertFalse($users->contains('id', $this->admin->id));
        $this->assertTrue($users->contains('id', $this->targetUser->id));
    }

    // ─── searchUsers ─────────────────────────────────────────────────

    public function test_search_users_finds_by_name(): void
    {
        $users = $this->service->searchUsers($this->targetUser->name);

        $this->assertTrue($users->contains('id', $this->targetUser->id));
    }

    public function test_search_users_finds_by_email(): void
    {
        $users = $this->service->searchUsers($this->targetUser->email);

        $this->assertTrue($users->contains('id', $this->targetUser->id));
    }
}
