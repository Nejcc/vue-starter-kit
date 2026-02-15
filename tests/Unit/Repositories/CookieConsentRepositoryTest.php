<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\CookieConsentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class CookieConsentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CookieConsentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CookieConsentRepository();
    }

    public function test_get_authenticated_user_preferences_returns_stored(): void
    {
        $user = User::factory()->create([
            'cookie_consent_preferences' => ['essential' => true, 'analytics' => false],
        ]);

        $prefs = $this->repository->getAuthenticatedUserPreferences($user);

        $this->assertEquals(['essential' => true, 'analytics' => false], $prefs);
    }

    public function test_get_authenticated_user_preferences_returns_empty_when_null(): void
    {
        $user = User::factory()->create([
            'cookie_consent_preferences' => null,
        ]);

        $prefs = $this->repository->getAuthenticatedUserPreferences($user);

        $this->assertEquals([], $prefs);
    }

    public function test_store_authenticated_user_preferences(): void
    {
        $user = User::factory()->create();
        $preferences = ['essential' => true, 'analytics' => true];

        $this->repository->storeAuthenticatedUserPreferences($user, $preferences, '127.0.0.1');

        $user->refresh();
        $this->assertEquals($preferences, $user->cookie_consent_preferences);
        $this->assertNotNull($user->cookie_consent_given_at);
    }

    public function test_get_guest_preferences_from_session(): void
    {
        $request = Request::create('/cookie-consent');
        $session = app('session.store');
        $request->setLaravelSession($session);

        $sessionKey = config('cookie.storage.session_key');
        $session->put($sessionKey, ['essential' => true, 'marketing' => false]);

        $prefs = $this->repository->getGuestPreferences($request);

        $this->assertEquals(['essential' => true, 'marketing' => false], $prefs);
    }

    public function test_get_guest_preferences_returns_empty_when_no_consent(): void
    {
        $request = Request::create('/cookie-consent');
        $request->setLaravelSession(app('session.store'));

        $prefs = $this->repository->getGuestPreferences($request);

        $this->assertEquals([], $prefs);
    }

    public function test_store_guest_preferences_sets_session(): void
    {
        $request = Request::create('/cookie-consent', 'POST');
        $request->setLaravelSession(app('session.store'));

        $preferences = ['essential' => true, 'analytics' => false];
        $this->repository->storeGuestPreferences($request, $preferences);

        $sessionKey = config('cookie.storage.session_key');
        $stored = $request->session()->get($sessionKey);

        $this->assertEquals($preferences, $stored);
    }

    public function test_get_cookie_categories_returns_configured_categories(): void
    {
        $categories = $this->repository->getCookieCategories();

        $this->assertIsArray($categories);
        $this->assertArrayHasKey('essential', $categories);
        $this->assertArrayHasKey('analytics', $categories);
        $this->assertArrayHasKey('marketing', $categories);
        $this->assertArrayHasKey('preferences', $categories);
    }

    public function test_get_cookie_config_returns_expected_keys(): void
    {
        $config = $this->repository->getCookieConfig();

        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('gdpr_mode', $config);
    }
}
