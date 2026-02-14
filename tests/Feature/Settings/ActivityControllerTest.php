<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_activity_page(): void
    {
        $response = $this->get(route('activity.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_activity_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('activity.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/Activity')
            ->has('logs'));
    }

    public function test_activity_page_shows_user_audit_logs(): void
    {
        $user = User::factory()->create();

        AuditLog::log('auth.login', $user, null, null, $user->id);
        AuditLog::log('user.profile_updated', $user, ['name' => 'Old'], ['name' => 'New'], $user->id);

        $response = $this->actingAs($user)->get(route('activity.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('settings/Activity')
            ->has('logs.data', 2));
    }

    public function test_activity_page_does_not_show_other_users_logs(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        AuditLog::log('auth.login', $user, null, null, $user->id);
        AuditLog::log('auth.login', $otherUser, null, null, $otherUser->id);

        $response = $this->actingAs($user)->get(route('activity.index'));

        $response->assertInertia(fn ($page) => $page->component('settings/Activity')
            ->has('logs.data', 1));
    }

    public function test_activity_logs_are_ordered_newest_first(): void
    {
        $user = User::factory()->create();

        $oldLog = AuditLog::log('auth.login', $user, null, null, $user->id);
        $oldLog->forceFill(['created_at' => '2026-01-01 10:00:00'])->save();

        $newLog = AuditLog::log('auth.logout', $user, null, null, $user->id);
        $newLog->forceFill(['created_at' => '2026-01-02 10:00:00'])->save();

        $response = $this->actingAs($user)->get(route('activity.index'));

        $response->assertInertia(function ($page) use ($newLog, $oldLog): void {
            $page->component('settings/Activity');
            $logs = $page->toArray()['props']['logs']['data'];

            $this->assertEquals($newLog->id, $logs[0]['id']);
            $this->assertEquals($oldLog->id, $logs[1]['id']);
        });
    }

    public function test_activity_log_entries_have_expected_fields(): void
    {
        $user = User::factory()->create();

        AuditLog::log('auth.login', $user, null, null, $user->id);

        $response = $this->actingAs($user)->get(route('activity.index'));

        $response->assertInertia(fn ($page) => $page->component('settings/Activity')
            ->has('logs.data.0', fn ($log) => $log
                ->has('id')
                ->has('event')
                ->has('description')
                ->has('ip_address')
                ->has('user_agent')
                ->has('created_at')
                ->has('created_at_human')));
    }

    public function test_activity_log_descriptions_are_human_readable(): void
    {
        $user = User::factory()->create();

        AuditLog::log('auth.login', $user, null, null, $user->id);

        $response = $this->actingAs($user)->get(route('activity.index'));

        $response->assertInertia(function ($page): void {
            $page->component('settings/Activity');
            $logs = $page->toArray()['props']['logs']['data'];

            $this->assertEquals('Signed in', $logs[0]['description']);
        });
    }

    public function test_activity_page_is_paginated(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 25; $i++) {
            AuditLog::log('auth.login', $user, null, null, $user->id);
        }

        $response = $this->actingAs($user)->get(route('activity.index'));

        $response->assertInertia(function ($page): void {
            $page->component('settings/Activity');
            $logs = $page->toArray()['props']['logs'];

            $this->assertCount(20, $logs['data']);
            $this->assertEquals(2, $logs['last_page']);
            $this->assertNotNull($logs['next_page_url']);
        });
    }

    public function test_activity_page_second_page(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 25; $i++) {
            AuditLog::log('auth.login', $user, null, null, $user->id);
        }

        $response = $this->actingAs($user)->get(route('activity.index', ['page' => 2]));

        $response->assertInertia(function ($page): void {
            $page->component('settings/Activity');
            $logs = $page->toArray()['props']['logs'];

            $this->assertCount(5, $logs['data']);
            $this->assertEquals(2, $logs['current_page']);
        });
    }

    public function test_empty_activity_log(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('activity.index'));

        $response->assertInertia(fn ($page) => $page->component('settings/Activity')
            ->has('logs.data', 0));
    }
}
