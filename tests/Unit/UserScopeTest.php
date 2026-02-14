<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_scope_excludes_suspended_users(): void
    {
        User::factory()->create();
        User::factory()->create(['suspended_at' => now()]);

        $active = User::active()->get();

        $this->assertCount(1, $active);
        $this->assertNull($active->first()->suspended_at);
    }

    public function test_suspended_scope_only_returns_suspended_users(): void
    {
        User::factory()->create();
        $suspended = User::factory()->create([
            'suspended_at' => now(),
            'suspended_reason' => 'Test reason',
        ]);

        $result = User::suspended()->get();

        $this->assertCount(1, $result);
        $this->assertEquals($suspended->id, $result->first()->id);
    }

    public function test_verified_scope_only_returns_verified_users(): void
    {
        User::factory()->create(['email_verified_at' => now()]);
        User::factory()->create(['email_verified_at' => null]);

        $verified = User::verified()->get();

        $this->assertCount(1, $verified);
        $this->assertNotNull($verified->first()->email_verified_at);
    }

    public function test_unverified_scope_only_returns_unverified_users(): void
    {
        User::factory()->create(['email_verified_at' => now()]);
        User::factory()->create(['email_verified_at' => null]);

        $unverified = User::unverified()->get();

        $this->assertCount(1, $unverified);
        $this->assertNull($unverified->first()->email_verified_at);
    }

    public function test_scopes_can_be_chained(): void
    {
        User::factory()->create(['email_verified_at' => now(), 'suspended_at' => null]);
        User::factory()->create(['email_verified_at' => now(), 'suspended_at' => now()]);
        User::factory()->create(['email_verified_at' => null, 'suspended_at' => null]);

        $activeAndVerified = User::active()->verified()->get();

        $this->assertCount(1, $activeAndVerified);
    }

    public function test_active_scope_returns_all_when_no_suspended_users(): void
    {
        User::factory()->count(3)->create();

        $active = User::active()->get();

        $this->assertCount(3, $active);
    }

    public function test_suspended_scope_returns_empty_when_no_suspended_users(): void
    {
        User::factory()->count(3)->create();

        $suspended = User::suspended()->get();

        $this->assertCount(0, $suspended);
    }
}
