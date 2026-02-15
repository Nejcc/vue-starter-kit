<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\CookieConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class CookieConsentServiceTest extends TestCase
{
    use RefreshDatabase;

    private CookieConsentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CookieConsentService::class);
    }

    public function test_get_preferences_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'cookie_consent_preferences' => ['essential' => true, 'analytics' => false],
            'cookie_consent_given_at' => now(),
        ]);
        $this->actingAs($user);

        $request = Request::create('/cookie-consent');
        $request->setLaravelSession(app('session.store'));

        $result = $this->service->getPreferences($request);

        $this->assertArrayHasKey('preferences', $result);
        $this->assertArrayHasKey('hasConsent', $result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertArrayHasKey('config', $result);
        $this->assertTrue($result['hasConsent']);
        $this->assertTrue($result['preferences']['essential']);
    }

    public function test_get_preferences_for_guest_without_consent(): void
    {
        $request = Request::create('/cookie-consent');
        $request->setLaravelSession(app('session.store'));

        $result = $this->service->getPreferences($request);

        $this->assertFalse($result['hasConsent']);
        $this->assertEmpty($result['preferences']);
    }

    public function test_update_preferences_stores_correctly_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = Request::create('/cookie-consent', 'POST');
        $request->setLaravelSession(app('session.store'));

        $preferences = ['essential' => true, 'analytics' => true, 'marketing' => false];
        $result = $this->service->updatePreferences($request, $preferences);

        $this->assertEquals($preferences, $result);

        $user->refresh();
        $this->assertEquals($preferences, $user->cookie_consent_preferences);
    }

    public function test_accept_all_sets_all_categories_true(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = Request::create('/cookie-consent', 'POST');
        $request->setLaravelSession(app('session.store'));

        $result = $this->service->acceptAll($request);

        foreach ($result as $value) {
            $this->assertTrue($value);
        }
    }

    public function test_reject_all_keeps_essential_true(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = Request::create('/cookie-consent', 'POST');
        $request->setLaravelSession(app('session.store'));

        $result = $this->service->rejectAll($request);

        $this->assertTrue($result['essential']);

        $nonEssential = array_filter($result, fn ($v, $k) => $k !== 'essential', ARRAY_FILTER_USE_BOTH);
        foreach ($nonEssential as $value) {
            $this->assertFalse($value);
        }
    }

    public function test_update_preferences_stores_for_guest(): void
    {
        $request = Request::create('/cookie-consent', 'POST');
        $request->setLaravelSession(app('session.store'));

        $preferences = ['essential' => true, 'analytics' => false];
        $this->service->updatePreferences($request, $preferences);

        $sessionKey = config('cookie.storage.session_key');
        $stored = $request->session()->get($sessionKey);

        $this->assertEquals($preferences, $stored);
    }

    public function test_get_preferences_returns_categories_and_config(): void
    {
        $request = Request::create('/cookie-consent');
        $request->setLaravelSession(app('session.store'));

        $result = $this->service->getPreferences($request);

        $this->assertNotEmpty($result['categories']);
        $this->assertArrayHasKey('essential', $result['categories']);
        $this->assertArrayHasKey('enabled', $result['config']);
        $this->assertArrayHasKey('gdpr_mode', $result['config']);
    }
}
