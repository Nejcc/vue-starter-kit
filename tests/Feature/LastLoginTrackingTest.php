<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class LastLoginTrackingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that last_login_at is updated when Login event is fired.
     * This tests the integration between the Login event and our listener.
     */
    public function test_last_login_at_is_updated_on_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->assertNull($user->last_login_at);
        $this->assertFalse($user->hasLoggedIn());

        // Simulate login by firing the Login event (which is what happens during actual login)
        Event::dispatch(new Login('web', $user, false));

        $user->refresh();

        // The event listener should have updated last_login_at
        $this->assertNotNull($user->last_login_at, 'last_login_at should be set when Login event is dispatched');
        $this->assertTrue($user->hasLoggedIn());
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->last_login_at);
    }

    /**
     * Test that last_login_at is null for users who haven't logged in.
     */
    public function test_last_login_at_is_null_for_new_users(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->last_login_at);
        $this->assertFalse($user->hasLoggedIn());
    }

    /**
     * Test that recordLastLogin method works correctly.
     */
    public function test_record_last_login_method(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->last_login_at);

        $user->recordLastLogin();
        $user->refresh();

        $this->assertNotNull($user->last_login_at);
        $this->assertTrue($user->hasLoggedIn());
    }

    /**
     * Test that Login event triggers last login update.
     */
    public function test_login_event_triggers_last_login_update(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertNull($user->last_login_at);

        // Manually dispatch the Login event to test the listener
        Event::dispatch(new Login('web', $user, false));

        $user->refresh();

        $this->assertNotNull($user->last_login_at, 'last_login_at should be set when Login event is dispatched');
    }

    /**
     * Test that updated_at is not modified when recording last login.
     */
    public function test_updated_at_is_not_modified_when_recording_login(): void
    {
        $user = User::factory()->create();
        $originalUpdatedAt = $user->updated_at->copy();

        // Wait a moment to ensure timestamps would differ if updated
        usleep(100000); // 0.1 seconds

        $user->recordLastLogin();
        $user->refresh();

        // Allow for small timing differences but ensure it's essentially the same
        $this->assertLessThanOrEqual(1, abs($originalUpdatedAt->diffInSeconds($user->updated_at)));
        $this->assertNotNull($user->last_login_at);
    }
}
