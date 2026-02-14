<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class CookieConsentControllerTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // GET PREFERENCES - GUEST
    // ========================================

    /**
     * Test that a guest can get preferences with the correct structure.
     */
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

    /**
     * Test that guest preferences default to no consent.
     */
    public function test_guest_has_no_consent_by_default(): void
    {
        $response = $this->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJson([
            'hasConsent' => false,
            'preferences' => [],
        ]);
    }

    /**
     * Test that get preferences returns all configured cookie categories.
     */
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

    /**
     * Test that get preferences returns enabled and gdpr_mode config values.
     */
    public function test_get_preferences_returns_config_values(): void
    {
        $response = $this->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJson([
            'config' => [
                'enabled' => true,
                'gdpr_mode' => true,
            ],
        ]);
    }

    // ========================================
    // GET PREFERENCES - AUTHENTICATED
    // ========================================

    /**
     * Test that an authenticated user can get preferences.
     */
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

    /**
     * Test that authenticated user without consent shows hasConsent false.
     */
    public function test_authenticated_user_without_consent_shows_no_consent(): void
    {
        $user = User::factory()->create([
            'cookie_consent_given_at' => null,
            'cookie_consent_preferences' => null,
        ]);

        $response = $this->actingAs($user)->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJson([
            'hasConsent' => false,
            'preferences' => [],
        ]);
    }

    /**
     * Test that authenticated user with consent shows hasConsent true and their preferences.
     */
    public function test_authenticated_user_with_consent_shows_has_consent(): void
    {
        $user = User::factory()->create([
            'cookie_consent_given_at' => now(),
            'cookie_consent_preferences' => [
                'essential' => true,
                'analytics' => true,
                'marketing' => false,
                'preferences' => false,
            ],
        ]);

        $response = $this->actingAs($user)->getJson(route('cookie-consent.get'));

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

    // ========================================
    // ACCEPT ALL - GUEST
    // ========================================

    /**
     * Test that a guest can accept all cookies.
     */
    public function test_guest_can_accept_all_cookies(): void
    {
        $response = $this->postJson(route('cookie-consent.accept-all'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'All cookies accepted.',
        ]);
    }

    /**
     * Test that accept all sets every category to true.
     */
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

    /**
     * Test that guest accept all persists preferences in session.
     */
    public function test_guest_accept_all_persists_in_session(): void
    {
        $this->postJson(route('cookie-consent.accept-all'));

        $response = $this->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJson([
            'hasConsent' => true,
            'preferences' => [
                'essential' => true,
                'analytics' => true,
                'marketing' => true,
                'preferences' => true,
            ],
        ]);
    }

    // ========================================
    // ACCEPT ALL - AUTHENTICATED
    // ========================================

    /**
     * Test that an authenticated user can accept all cookies.
     */
    public function test_authenticated_user_can_accept_all_cookies(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cookie-consent.accept-all'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'All cookies accepted.',
        ]);
    }

    /**
     * Test that authenticated user accept all persists in database.
     */
    public function test_authenticated_accept_all_persists_in_database(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('cookie-consent.accept-all'));

        $user->refresh();
        $this->assertTrue($user->hasCookieConsent());
        $this->assertNotNull($user->cookie_consent_given_at);
        $this->assertTrue($user->cookie_consent_preferences['essential']);
        $this->assertTrue($user->cookie_consent_preferences['analytics']);
        $this->assertTrue($user->cookie_consent_preferences['marketing']);
        $this->assertTrue($user->cookie_consent_preferences['preferences']);
    }

    /**
     * Test that authenticated user accept all stores IP address.
     */
    public function test_authenticated_accept_all_stores_ip_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('cookie-consent.accept-all'));

        $user->refresh();
        $this->assertNotNull($user->gdpr_ip_address);
    }

    // ========================================
    // REJECT ALL - GUEST
    // ========================================

    /**
     * Test that a guest can reject all cookies.
     */
    public function test_guest_can_reject_all_cookies(): void
    {
        $response = $this->postJson(route('cookie-consent.reject-all'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Non-essential cookies rejected.',
        ]);
    }

    /**
     * Test that reject all keeps essential enabled and disables the rest.
     */
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

    /**
     * Test that guest reject all persists preferences in session.
     */
    public function test_guest_reject_all_persists_in_session(): void
    {
        $this->postJson(route('cookie-consent.reject-all'));

        $response = $this->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJson([
            'hasConsent' => true,
            'preferences' => [
                'essential' => true,
                'analytics' => false,
                'marketing' => false,
                'preferences' => false,
            ],
        ]);
    }

    // ========================================
    // REJECT ALL - AUTHENTICATED
    // ========================================

    /**
     * Test that an authenticated user can reject all cookies.
     */
    public function test_authenticated_user_can_reject_all_cookies(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('cookie-consent.reject-all'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Non-essential cookies rejected.',
        ]);
    }

    /**
     * Test that authenticated user reject all persists in database with essential true.
     */
    public function test_authenticated_reject_all_persists_in_database(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('cookie-consent.reject-all'));

        $user->refresh();
        $this->assertTrue($user->hasCookieConsent());
        $this->assertNotNull($user->cookie_consent_given_at);
        $this->assertTrue($user->cookie_consent_preferences['essential']);
        $this->assertFalse($user->cookie_consent_preferences['analytics']);
        $this->assertFalse($user->cookie_consent_preferences['marketing']);
        $this->assertFalse($user->cookie_consent_preferences['preferences']);
    }

    /**
     * Test that authenticated user reject all stores IP address.
     */
    public function test_authenticated_reject_all_stores_ip_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('cookie-consent.reject-all'));

        $user->refresh();
        $this->assertNotNull($user->gdpr_ip_address);
    }

    // ========================================
    // UPDATE PREFERENCES - GUEST CUSTOM
    // ========================================

    /**
     * Test that a guest can set custom preferences.
     */
    public function test_guest_can_set_custom_preferences(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => true,
            'marketing' => false,
            'preferences' => true,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Cookie preferences updated successfully.',
            'preferences' => [
                'essential' => true,
                'analytics' => true,
                'marketing' => false,
                'preferences' => true,
            ],
        ]);
    }

    /**
     * Test that guest custom preferences persist in session.
     */
    public function test_guest_custom_preferences_persist_in_session(): void
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

    /**
     * Test that essential cookies are always forced to true via prepareForValidation.
     */
    public function test_essential_cookies_always_forced_true(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => false,
            'analytics' => false,
            'marketing' => false,
            'preferences' => false,
        ]);

        $response->assertOk();
        $response->assertJson([
            'preferences' => [
                'essential' => true,
            ],
        ]);
    }

    /**
     * Test that guest can update preferences multiple times and latest persists.
     */
    public function test_guest_can_update_preferences_multiple_times(): void
    {
        $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => true,
            'marketing' => true,
            'preferences' => true,
        ]);

        $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => false,
            'marketing' => false,
            'preferences' => false,
        ]);

        $response = $this->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJson([
            'preferences' => [
                'essential' => true,
                'analytics' => false,
                'marketing' => false,
                'preferences' => false,
            ],
        ]);
    }

    // ========================================
    // UPDATE PREFERENCES - AUTHENTICATED CUSTOM
    // ========================================

    /**
     * Test that an authenticated user can set custom preferences.
     */
    public function test_authenticated_user_can_set_custom_preferences(): void
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
    }

    /**
     * Test that authenticated user custom preferences persist in database.
     */
    public function test_authenticated_custom_preferences_persist_in_database(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => false,
            'marketing' => true,
            'preferences' => false,
        ]);

        $user->refresh();
        $this->assertTrue($user->hasCookieConsent());
        $this->assertNotNull($user->cookie_consent_given_at);
        $this->assertTrue($user->cookie_consent_preferences['essential']);
        $this->assertFalse($user->cookie_consent_preferences['analytics']);
        $this->assertTrue($user->cookie_consent_preferences['marketing']);
        $this->assertFalse($user->cookie_consent_preferences['preferences']);
    }

    /**
     * Test that authenticated user update stores IP address.
     */
    public function test_authenticated_update_stores_ip_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => true,
            'marketing' => false,
            'preferences' => false,
        ]);

        $user->refresh();
        $this->assertNotNull($user->gdpr_ip_address);
    }

    /**
     * Test that authenticated user can update preferences and consent timestamp changes.
     */
    public function test_authenticated_user_can_update_preferences_overwrites_previous(): void
    {
        $user = User::factory()->create([
            'cookie_consent_given_at' => now()->subDay(),
            'cookie_consent_preferences' => [
                'essential' => true,
                'analytics' => true,
                'marketing' => true,
                'preferences' => true,
            ],
        ]);

        $originalTimestamp = $user->cookie_consent_given_at;

        $this->actingAs($user)->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => false,
            'marketing' => false,
            'preferences' => false,
        ]);

        $user->refresh();
        $this->assertFalse($user->cookie_consent_preferences['analytics']);
        $this->assertFalse($user->cookie_consent_preferences['marketing']);
        $this->assertFalse($user->cookie_consent_preferences['preferences']);
        $this->assertTrue($user->cookie_consent_given_at->isAfter($originalTimestamp));
    }

    // ========================================
    // VALIDATION
    // ========================================

    /**
     * Test that non-boolean values fail validation.
     */
    public function test_update_preferences_validates_boolean_values(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => 'not-a-boolean',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('analytics');
    }

    /**
     * Test that marketing field validates as boolean.
     */
    public function test_update_preferences_validates_marketing_as_boolean(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'marketing' => 'invalid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('marketing');
    }

    /**
     * Test that preferences field validates as boolean.
     */
    public function test_update_preferences_validates_preferences_as_boolean(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'preferences' => 'invalid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('preferences');
    }

    /**
     * Test that integer values (0/1) are accepted as boolean equivalents.
     */
    public function test_update_preferences_accepts_integer_booleans(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => 1,
            'analytics' => 0,
            'marketing' => 1,
            'preferences' => 0,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Test that request with no category fields succeeds (all optional except essential which is merged).
     */
    public function test_update_preferences_with_empty_body_succeeds(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), []);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'preferences' => [
                'essential' => true,
            ],
        ]);
    }

    /**
     * Test that extra unknown fields are ignored.
     */
    public function test_update_preferences_ignores_unknown_fields(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => true,
            'unknown_category' => true,
            'foo' => 'bar',
        ]);

        $response->assertOk();
        $preferences = $response->json('preferences');

        $this->assertArrayNotHasKey('unknown_category', $preferences);
        $this->assertArrayNotHasKey('foo', $preferences);
    }

    /**
     * Test that string "true" and "false" fail validation (must be real booleans).
     */
    public function test_update_preferences_rejects_string_true_false(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => 'true',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('analytics');
    }

    // ========================================
    // AUDIT LOGGING
    // ========================================

    /**
     * Test that accept all logs a consent change.
     */
    public function test_accept_all_logs_consent_change(): void
    {
        Log::shouldReceive('channel')
            ->once()
            ->with(config('cookie.audit_logging.log_channel', 'daily'))
            ->andReturnSelf();
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'Cookie consent - Accept All'
                    && array_key_exists('preferences', $context)
                    && array_key_exists('ip_address', $context));

        $this->postJson(route('cookie-consent.accept-all'));
    }

    /**
     * Test that reject all logs a consent change.
     */
    public function test_reject_all_logs_consent_change(): void
    {
        Log::shouldReceive('channel')
            ->once()
            ->with(config('cookie.audit_logging.log_channel', 'daily'))
            ->andReturnSelf();
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'Cookie consent - Reject All'
                    && array_key_exists('preferences', $context)
                    && array_key_exists('ip_address', $context));

        $this->postJson(route('cookie-consent.reject-all'));
    }

    /**
     * Test that update preferences logs a consent change.
     */
    public function test_update_preferences_logs_consent_change(): void
    {
        Log::shouldReceive('channel')
            ->once()
            ->with(config('cookie.audit_logging.log_channel', 'daily'))
            ->andReturnSelf();
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'Cookie consent updated'
                    && array_key_exists('preferences', $context)
                    && array_key_exists('ip_address', $context));

        $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => true,
        ]);
    }

    /**
     * Test that audit log includes user_id for authenticated users.
     */
    public function test_audit_log_includes_user_id_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        Log::shouldReceive('channel')
            ->once()
            ->andReturnSelf();
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $context['user_id'] === $user->id);

        $this->actingAs($user)->postJson(route('cookie-consent.accept-all'));
    }

    /**
     * Test that audit log has null user_id for guest users.
     */
    public function test_audit_log_has_null_user_id_for_guests(): void
    {
        Log::shouldReceive('channel')
            ->once()
            ->andReturnSelf();
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $context['user_id'] === null);

        $this->postJson(route('cookie-consent.accept-all'));
    }

    // ========================================
    // COOKIE QUEUED FOR GUEST
    // ========================================

    /**
     * Test that accept all queues a cookie for guest users.
     */
    public function test_guest_accept_all_queues_cookie(): void
    {
        $response = $this->postJson(route('cookie-consent.accept-all'));

        $response->assertOk();
        $response->assertCookie('cookie_consent_guest');
    }

    /**
     * Test that reject all queues a cookie for guest users.
     */
    public function test_guest_reject_all_queues_cookie(): void
    {
        $response = $this->postJson(route('cookie-consent.reject-all'));

        $response->assertOk();
        $response->assertCookie('cookie_consent_guest');
    }

    /**
     * Test that update preferences queues a cookie for guest users.
     */
    public function test_guest_update_preferences_queues_cookie(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => false,
        ]);

        $response->assertOk();
        $response->assertCookie('cookie_consent_guest');
    }

    // ========================================
    // EDGE CASES
    // ========================================

    /**
     * Test that only providing some categories still succeeds.
     */
    public function test_update_preferences_with_partial_categories(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'analytics' => true,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'preferences' => [
                'essential' => true,
                'analytics' => true,
            ],
        ]);
    }

    /**
     * Test that accept all after reject all updates preferences correctly.
     */
    public function test_accept_all_overrides_previous_reject_all(): void
    {
        $this->postJson(route('cookie-consent.reject-all'));
        $this->postJson(route('cookie-consent.accept-all'));

        $response = $this->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJson([
            'hasConsent' => true,
            'preferences' => [
                'essential' => true,
                'analytics' => true,
                'marketing' => true,
                'preferences' => true,
            ],
        ]);
    }

    /**
     * Test that reject all after accept all updates preferences correctly.
     */
    public function test_reject_all_overrides_previous_accept_all(): void
    {
        $this->postJson(route('cookie-consent.accept-all'));
        $this->postJson(route('cookie-consent.reject-all'));

        $response = $this->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertJson([
            'hasConsent' => true,
            'preferences' => [
                'essential' => true,
                'analytics' => false,
                'marketing' => false,
                'preferences' => false,
            ],
        ]);
    }

    /**
     * Test that authenticated accept then reject updates database correctly.
     */
    public function test_authenticated_accept_then_reject_updates_database(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('cookie-consent.accept-all'));
        $this->actingAs($user)->postJson(route('cookie-consent.reject-all'));

        $user->refresh();
        $this->assertTrue($user->cookie_consent_preferences['essential']);
        $this->assertFalse($user->cookie_consent_preferences['analytics']);
        $this->assertFalse($user->cookie_consent_preferences['marketing']);
        $this->assertFalse($user->cookie_consent_preferences['preferences']);
    }

    /**
     * Test that array values for boolean fields fail validation.
     */
    public function test_update_preferences_rejects_array_values(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => [1, 2, 3],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('analytics');
    }

    /**
     * Test that null values for boolean fields fail validation.
     */
    public function test_update_preferences_rejects_null_values(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
            'analytics' => null,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('analytics');
    }

    /**
     * Test that response is always JSON.
     */
    public function test_get_preferences_returns_json_content_type(): void
    {
        $response = $this->getJson(route('cookie-consent.get'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    }

    /**
     * Test that accept all returns JSON content type.
     */
    public function test_accept_all_returns_json_content_type(): void
    {
        $response = $this->postJson(route('cookie-consent.accept-all'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    }

    /**
     * Test that reject all returns JSON content type.
     */
    public function test_reject_all_returns_json_content_type(): void
    {
        $response = $this->postJson(route('cookie-consent.reject-all'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    }

    /**
     * Test that update preferences returns JSON content type.
     */
    public function test_update_preferences_returns_json_content_type(): void
    {
        $response = $this->postJson(route('cookie-consent.update'), [
            'essential' => true,
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    }
}
