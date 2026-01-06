<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class CookieConsentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that cookie consent preferences can be retrieved for guest users.
     */
    public function test_guest_user_can_get_cookie_preferences(): void
    {
        $response = $this->get('/cookie-consent');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'preferences',
            'hasConsent',
            'categories',
            'config',
        ]);
    }

    /**
     * Test that cookie consent preferences can be retrieved for authenticated users.
     */
    public function test_authenticated_user_can_get_cookie_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/cookie-consent');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'preferences',
            'hasConsent',
            'categories',
            'config',
        ]);
    }

    /**
     * Test that guest users can update cookie preferences.
     */
    public function test_guest_user_can_update_cookie_preferences(): void
    {
        $preferences = [
            'essential' => true,
            'analytics' => true,
            'marketing' => false,
            'preferences' => true,
        ];

        $response = $this->post('/cookie-consent', $preferences);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'preferences' => $preferences,
        ]);

        // Check that preferences are stored in session
        $this->assertTrue(session()->has(config('cookie.storage.session_key')));
        $this->assertEquals($preferences, session(config('cookie.storage.session_key')));
    }

    /**
     * Test that authenticated users can update cookie preferences.
     */
    public function test_authenticated_user_can_update_cookie_preferences(): void
    {
        $user = User::factory()->create();
        $preferences = [
            'essential' => true,
            'analytics' => true,
            'marketing' => false,
            'preferences' => true,
        ];

        $response = $this->actingAs($user)->post('/cookie-consent', $preferences);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'preferences' => $preferences,
        ]);

        // Check that preferences are stored in database
        $user->refresh();
        $this->assertEquals($preferences, $user->cookie_consent_preferences);
        $this->assertNotNull($user->cookie_consent_given_at);
    }

    /**
     * Test that essential cookies are always enabled.
     */
    public function test_essential_cookies_are_always_enabled(): void
    {
        $preferences = [
            'essential' => false, // Try to disable essential cookies
            'analytics' => true,
            'marketing' => false,
            'preferences' => true,
        ];

        $response = $this->post('/cookie-consent', $preferences);

        $response->assertStatus(200);

        // Essential cookies should be forced to true
        $responseData = $response->json();
        $this->assertTrue($responseData['preferences']['essential']);
    }

    /**
     * Test accept all cookies functionality.
     */
    public function test_accept_all_cookies(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/cookie-consent/accept-all');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $user->refresh();
        $this->assertTrue($user->cookie_consent_preferences['essential']);
        $this->assertTrue($user->cookie_consent_preferences['analytics']);
        $this->assertTrue($user->cookie_consent_preferences['marketing']);
        $this->assertTrue($user->cookie_consent_preferences['preferences']);
    }

    /**
     * Test reject all cookies functionality.
     */
    public function test_reject_all_cookies(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/cookie-consent/reject-all');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $user->refresh();
        $this->assertTrue($user->cookie_consent_preferences['essential']); // Essential should remain true
        $this->assertFalse($user->cookie_consent_preferences['analytics']);
        $this->assertFalse($user->cookie_consent_preferences['marketing']);
        $this->assertFalse($user->cookie_consent_preferences['preferences']);
    }

    /**
     * Test that registration requires data processing consent when GDPR mode is enabled.
     */
    public function test_registration_requires_data_processing_consent(): void
    {
        $userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password',
            'password_confirmation' => 'password',
            // Missing data_processing_consent
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors(['data_processing_consent']);
    }

    /**
     * Test that registration succeeds with data processing consent.
     */
    public function test_registration_succeeds_with_data_processing_consent(): void
    {
        $userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password',
            'password_confirmation' => 'password',
            'data_processing_consent' => true,
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->data_processing_consent);
        $this->assertNotNull($user->data_processing_consent_given_at);
        $this->assertNotNull($user->gdpr_ip_address);
    }

    /**
     * Test that User model helper methods work correctly.
     */
    public function test_user_model_helper_methods(): void
    {
        $user = User::factory()->create([
            'cookie_consent_preferences' => [
                'essential' => true,
                'analytics' => true,
                'marketing' => false,
                'preferences' => true,
            ],
            'cookie_consent_given_at' => now(),
            'data_processing_consent' => true,
            'data_processing_consent_given_at' => now(),
        ]);

        $this->assertTrue($user->hasCookieConsent());
        $this->assertTrue($user->hasDataProcessingConsent());
        $this->assertTrue($user->hasCookieConsentForCategory('analytics'));
        $this->assertFalse($user->hasCookieConsentForCategory('marketing'));
    }

    /**
     * Test that cookie consent middleware shares data with Inertia.
     */
    public function test_cookie_consent_middleware_shares_data(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('cookieConsent')
            ->where('cookieConsent.hasConsent', false)
            ->has('cookieConsent.preferences')
            ->has('cookieConsent.categories')
            ->has('cookieConsent.config')
        );
    }

    /**
     * Test that authenticated user with consent shares correct data.
     */
    public function test_authenticated_user_with_consent_shares_correct_data(): void
    {
        $user = User::factory()->create([
            'cookie_consent_preferences' => [
                'essential' => true,
                'analytics' => true,
                'marketing' => false,
                'preferences' => true,
            ],
            'cookie_consent_given_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('cookieConsent')
            ->where('cookieConsent.hasConsent', true)
            ->where('cookieConsent.preferences.analytics', true)
            ->where('cookieConsent.preferences.marketing', false)
        );
    }

    /**
     * Test validation rules for cookie preferences.
     */
    public function test_cookie_preferences_validation(): void
    {
        $invalidData = [
            'essential' => 'not-a-boolean',
            'analytics' => 'invalid',
        ];

        $response = $this->postJson('/cookie-consent', $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['analytics']);
    }

    /**
     * Test that IP address is stored when updating preferences.
     */
    public function test_ip_address_is_stored_when_updating_preferences(): void
    {
        $user = User::factory()->create();
        $preferences = [
            'essential' => true,
            'analytics' => true,
            'marketing' => false,
            'preferences' => true,
        ];

        $response = $this->actingAs($user)->post('/cookie-consent', $preferences);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertNotNull($user->gdpr_ip_address);
    }
}
