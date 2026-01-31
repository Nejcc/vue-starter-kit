<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuditLogsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Role $superAdminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->superAdminRole);
    }

    public function test_guests_cannot_access_audit_logs(): void
    {
        $response = $this->get(route('admin.audit-logs.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_audit_logs(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $response = $this->actingAs($user)->get(route('admin.audit-logs.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_audit_logs(): void
    {
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        $response = $this->actingAs($adminUser)->get(route('admin.audit-logs.index'));

        $response->assertStatus(200);
    }

    public function test_audit_logs_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/AuditLogs/Index')
            ->has('logs')
            ->has('eventTypes')
            ->has('filters')
        );
    }

    public function test_audit_logs_display_entries(): void
    {
        AuditLog::log('impersonation.started', null, null, ['target_user_id' => 2], $this->admin->id);
        AuditLog::log('impersonation.stopped', null, null, null, $this->admin->id);

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('logs.total', 2)
        );
    }

    public function test_audit_logs_search_by_event(): void
    {
        AuditLog::log('impersonation.started', null, null, null, $this->admin->id);
        AuditLog::log('user.created', null, null, null, $this->admin->id);

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index', ['search' => 'impersonation']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('logs.total', 1)
        );
    }

    public function test_audit_logs_filter_by_event_type(): void
    {
        AuditLog::log('impersonation.started', null, null, null, $this->admin->id);
        AuditLog::log('user.created', null, null, null, $this->admin->id);
        AuditLog::log('impersonation.stopped', null, null, null, $this->admin->id);

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index', ['event' => 'user.created']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('logs.total', 1)
        );
    }

    public function test_audit_logs_returns_event_types(): void
    {
        AuditLog::log('impersonation.started', null, null, null, $this->admin->id);
        AuditLog::log('user.created', null, null, null, $this->admin->id);

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('eventTypes', fn ($types) => count($types) === 2
                && in_array('impersonation.started', $types->toArray())
                && in_array('user.created', $types->toArray())
            )
        );
    }

    public function test_audit_logs_paginate(): void
    {
        for ($i = 0; $i < 30; $i++) {
            AuditLog::log("event.{$i}", null, null, null, $this->admin->id);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('logs.total', 30)
            ->where('logs.per_page', 25)
            ->where('logs.last_page', 2)
        );
    }

    public function test_audit_logs_empty_state(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('logs.total', 0)
        );
    }

    public function test_audit_logs_include_user_relation(): void
    {
        AuditLog::log('test.event', null, null, null, $this->admin->id);

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('logs.data.0.user.id', $this->admin->id)
            ->where('logs.data.0.user.name', $this->admin->name)
        );
    }
}
