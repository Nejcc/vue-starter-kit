<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

final class TwoFactorFullFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Skip test if two-factor authentication feature is not enabled.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }
    }

    /**
     * Enable 2FA for the given user via the API endpoint and return the raw TOTP secret.
     *
     * @return array{secret: string, user: User}
     */
    private function enableTwoFactorForUser(User $user): array
    {
        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson(route('two-factor.enable'));

        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);

        // The model's encrypted cast decrypts once, giving us Fortify's encrypted value.
        // Fortify's decrypt gives us the raw secret.
        $rawSecret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);

        return ['secret' => $rawSecret, 'user' => $user];
    }

    /**
     * Enable and confirm 2FA for the given user, returning the raw secret and recovery codes.
     *
     * @return array{secret: string, recoveryCodes: array<int, string>, user: User}
     */
    private function enableAndConfirmTwoFactorForUser(User $user): array
    {
        $result = $this->enableTwoFactorForUser($user);
        $rawSecret = $result['secret'];

        $code = (new Google2FA())->getCurrentOtp($rawSecret);

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson(route('two-factor.confirm'), ['code' => $code]);

        $user->refresh();

        $this->assertNotNull($user->two_factor_confirmed_at);

        $recoveryCodes = $user->recoveryCodes();

        return [
            'secret' => $rawSecret,
            'recoveryCodes' => $recoveryCodes,
            'user' => $user,
        ];
    }

    /**
     * Set up a user with confirmed 2FA using forceFill (bypasses TOTP code caching).
     *
     * @return array{secret: string, recoveryCodes: array<string>, user: User}
     */
    private function createUserWithConfirmedTwoFactor(): array
    {
        $user = User::factory()->create();

        $rawSecret = (new Google2FA())->generateSecretKey();
        $recoveryCodes = [
            'ABCDE-12345',
            'FGHIJ-67890',
            'KLMNO-11111',
            'PQRST-22222',
            'UVWXY-33333',
            'ZABCD-44444',
            'EFGHI-55555',
            'JKLMN-66666',
        ];

        $user->forceFill([
            'two_factor_secret' => encrypt($rawSecret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return [
            'secret' => $rawSecret,
            'recoveryCodes' => $recoveryCodes,
            'user' => $user,
        ];
    }

    public function test_authenticated_user_can_enable_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson(route('two-factor.enable'));

        $response->assertOk();

        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_user_can_get_qr_code_after_enabling_two_factor(): void
    {
        $user = User::factory()->create();

        $this->enableTwoFactorForUser($user);

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->getJson(route('two-factor.qr-code'));

        $response->assertOk();
        $response->assertJsonStructure(['svg']);
    }

    public function test_user_can_get_secret_key_after_enabling_two_factor(): void
    {
        $user = User::factory()->create();

        $result = $this->enableTwoFactorForUser($user);

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->getJson(route('two-factor.secret-key'));

        $response->assertOk();
        $response->assertJsonStructure(['secretKey']);
        $response->assertJsonFragment(['secretKey' => $result['secret']]);
    }

    public function test_user_can_get_recovery_codes_after_enabling_two_factor(): void
    {
        $user = User::factory()->create();

        $this->enableTwoFactorForUser($user);

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->getJson(route('two-factor.recovery-codes'));

        $response->assertOk();

        $codes = $response->json();

        $this->assertIsArray($codes);
        $this->assertCount(8, $codes);
    }

    public function test_user_can_confirm_two_factor_with_valid_totp_code(): void
    {
        $user = User::factory()->create();

        $result = $this->enableTwoFactorForUser($user);
        $rawSecret = $result['secret'];

        $code = (new Google2FA())->getCurrentOtp($rawSecret);

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson(route('two-factor.confirm'), ['code' => $code]);

        $response->assertOk();

        $user->refresh();

        $this->assertNotNull($user->two_factor_confirmed_at);
    }

    public function test_user_cannot_confirm_two_factor_with_invalid_code(): void
    {
        $user = User::factory()->create();

        $this->enableTwoFactorForUser($user);

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson(route('two-factor.confirm'), ['code' => '000000']);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrorFor('code');

        $user->refresh();

        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_login_with_two_factor_redirects_to_challenge_then_authenticates_with_totp(): void
    {
        $result = $this->createUserWithConfirmedTwoFactor();
        $user = $result['user'];
        $rawSecret = $result['secret'];

        // Step 1: Login with credentials
        $loginResponse = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect(route('two-factor.login'));
        $this->assertGuest();

        // Step 2: Submit 2FA code
        $code = (new Google2FA())->getCurrentOtp($rawSecret);

        $challengeResponse = $this->post(route('two-factor.login.store'), [
            'code' => $code,
        ]);

        $challengeResponse->assertRedirect(config('fortify.home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_two_factor_using_recovery_code(): void
    {
        $result = $this->createUserWithConfirmedTwoFactor();
        $user = $result['user'];
        $recoveryCodes = $result['recoveryCodes'];

        // Login with credentials
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();

        // Submit a recovery code
        $recoveryCode = $recoveryCodes[0];

        $challengeResponse = $this->post(route('two-factor.login.store'), [
            'recovery_code' => $recoveryCode,
        ]);

        $challengeResponse->assertRedirect(config('fortify.home'));
        $this->assertAuthenticatedAs($user);

        // The used recovery code should be replaced
        $user->refresh();
        $updatedCodes = $user->recoveryCodes();

        $this->assertNotContains($recoveryCode, $updatedCodes);
        $this->assertCount(8, $updatedCodes);
    }

    public function test_login_with_two_factor_fails_with_invalid_code(): void
    {
        $result = $this->createUserWithConfirmedTwoFactor();
        $user = $result['user'];

        // Login with credentials
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Submit invalid code
        $challengeResponse = $this->post(route('two-factor.login.store'), [
            'code' => '000000',
        ]);

        $challengeResponse->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_login_with_invalid_recovery_code_fails(): void
    {
        $result = $this->createUserWithConfirmedTwoFactor();
        $user = $result['user'];

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $challengeResponse = $this->post(route('two-factor.login.store'), [
            'recovery_code' => 'invalid-recovery-code',
        ]);

        $challengeResponse->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_user_can_regenerate_recovery_codes(): void
    {
        $user = User::factory()->create();

        $result = $this->enableAndConfirmTwoFactorForUser($user);
        $originalCodes = $result['recoveryCodes'];

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson(route('two-factor.regenerate-recovery-codes'));

        $response->assertOk();

        $user->refresh();
        $newCodes = $user->recoveryCodes();

        $this->assertCount(8, $newCodes);
        $this->assertNotEquals($originalCodes, $newCodes);
    }

    public function test_user_can_disable_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $this->enableAndConfirmTwoFactorForUser($user);

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->deleteJson(route('two-factor.disable'));

        $response->assertOk();

        $user->refresh();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_guest_cannot_access_two_factor_enable_endpoint(): void
    {
        $response = $this->postJson(route('two-factor.enable'));

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_access_two_factor_qr_code_endpoint(): void
    {
        $response = $this->getJson(route('two-factor.qr-code'));

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_access_two_factor_secret_key_endpoint(): void
    {
        $response = $this->getJson(route('two-factor.secret-key'));

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_access_two_factor_recovery_codes_endpoint(): void
    {
        $response = $this->getJson(route('two-factor.recovery-codes'));

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_disable_two_factor(): void
    {
        $response = $this->deleteJson(route('two-factor.disable'));

        $response->assertUnauthorized();
    }

    public function test_cannot_enable_two_factor_without_password_confirmation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('two-factor.enable'));

        $response->assertRedirect(route('password.confirm'));
    }

    public function test_cannot_disable_two_factor_without_password_confirmation(): void
    {
        $user = User::factory()->create();

        // Set up 2FA directly via forceFill so no password confirmation session lingers
        $rawSecret = (new Google2FA())->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => encrypt($rawSecret),
            'two_factor_recovery_codes' => encrypt(json_encode(['code-1'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($user)
            ->delete(route('two-factor.disable'));

        $response->assertRedirect(route('password.confirm'));
    }

    public function test_enabling_two_factor_again_does_not_overwrite_existing_secret(): void
    {
        $user = User::factory()->create();

        $result = $this->enableTwoFactorForUser($user);
        $firstSecret = $result['secret'];

        // Try enabling again without force
        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson(route('two-factor.enable'));

        $user->refresh();

        $currentSecret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);

        $this->assertEquals($firstSecret, $currentSecret);
    }

    public function test_enabling_two_factor_with_force_generates_new_secret(): void
    {
        $user = User::factory()->create();

        $result = $this->enableTwoFactorForUser($user);
        $firstSecret = $result['secret'];

        // Enable again with force
        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->postJson(route('two-factor.enable'), ['force' => true]);

        $user->refresh();

        $currentSecret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);

        $this->assertNotEquals($firstSecret, $currentSecret);
    }
}
