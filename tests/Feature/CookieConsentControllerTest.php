<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CookieConsentControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── Get Preferences ─────────────────────────────────────────────

    public function test_guest_can_get_preferences(): void
    {
        $response = $this->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJsonStructure([
            'preferences',
            'hasConsent',
            'categories',
            'config' => ['enabled', 'gdpr_mode'],
        ]);
        $response->assertJson([
            'hasConsent' => false,
            'preferences' => [],
        ]);
    }

    public function test_authenticated_user_can_get_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJsonStructure([
            'preferences',
            'hasConsent',
            'categories',
            'config',
        ]);
    }

    public function test_get_preferences_returns_categories_from_config(): void
    {
        $response = $this->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJsonStructure([
            'categories' => [
                'essential',
                'analytics',
                'marketing',
                'preferences',
            ],
        ]);
    }

    public function test_authenticated_user_with_consent_shows_has_consent(): void
    {
        $user = User::factory()->create([
            'cookie_consent_given_at' => now(),
            'cookie_consent_preferences' => ['essential' => true, 'analytics' => true],
        ]);

        $response = $this->actingAs($user)->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJson([
            'hasConsent' => true,
            'preferences' => ['essential' => true, 'analytics' => true],
        ]);
    }

    // ─── Update Preferences ──────────────────────────────────────────

    public function test_guest_can_update_preferences(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => false,
            'marketing' => false,
            'preferences' => true,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Cookie preferences updated successfully.',
        ]);
    }

    public function test_authenticated_user_can_update_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => true,
            'marketing' => false,
            'preferences' => false,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'preferences' => [
                'essential' => true,
                'analytics' => true,
                'marketing' => false,
                'preferences' => false,
            ],
        ]);

        $user->refresh();
        $this->assertTrue($user->hasCookieConsent());
    }

    public function test_essential_cookies_always_forced_true(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => false,
            'analytics' => false,
            'marketing' => false,
            'preferences' => false,
        ]);

        $response->assertOk();
        // Essential is forced to true via prepareForValidation
        $response->assertJson([
            'preferences' => [
                'essential' => true,
            ],
        ]);
    }

    public function test_update_preferences_validates_boolean_values(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => 'not-a-boolean',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('analytics');
    }

    // ─── Accept All ──────────────────────────────────────────────────

    public function test_guest_can_accept_all_cookies(): void
    {
        $response = $this->postJson(route('cookie-consent.accept-all'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'All cookies accepted.',
        ]);

        $preferences = $response->json('preferences');
        foreach ($preferences as $value) {
            $this->assertTrue($value);
        }
    }

    public function test_authenticated_user_can_accept_all_cookies(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cookie-consent.accept-all'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'All cookies accepted.',
        ]);

        $user->refresh();
        $this->assertTrue($user->hasCookieConsent());
    }

    public function test_accept_all_enables_all_categories(): void
    {
        $response = $this->postJson(route('cookie-consent.accept-all'));

        $response->assertOk();
        $preferences = $response->json('preferences');

        $this->assertTrue($preferences['essential']);
        $this->assertTrue($preferences['analytics']);
        $this->assertTrue($preferences['marketing']);
        $this->assertTrue($preferences['preferences']);
    }

    // ─── Reject All ──────────────────────────────────────────────────

    public function test_guest_can_reject_all_cookies(): void
    {
        $response = $this->postJson(route('cookie-consent.reject-all'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Non-essential cookies rejected.',
        ]);
    }

    public function test_authenticated_user_can_reject_all_cookies(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cookie-consent.reject-all'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $user->refresh();
        $this->assertTrue($user->hasCookieConsent());
    }

    public function test_reject_all_keeps_essential_enabled(): void
    {
        $response = $this->postJson(route('cookie-consent.reject-all'));

        $response->assertOk();
        $preferences = $response->json('preferences');

        $this->assertTrue($preferences['essential']);
        $this->assertFalse($preferences['analytics']);
        $this->assertFalse($preferences['marketing']);
        $this->assertFalse($preferences['preferences']);
    }

    // ─── Guest Session Persistence ───────────────────────────────────

    public function test_guest_preferences_persist_in_session(): void
    {
        $this->postJson(route('cookie-consent.accept-all'));

        // Subsequent get should show consent
        $response = $this->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJson([
            'hasConsent' => true,
        ]);
    }

    public function test_guest_update_then_get_returns_updated_preferences(): void
    {
        $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => true,
            'marketing' => false,
            'preferences' => false,
        ]);

        $response = $this->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJson([
            'hasConsent' => true,
            'preferences' => [
                'essential' => true,
                'analytics' => true,
                'marketing' => false,
                'preferences' => false,
            ],
        ]);
    }
}
