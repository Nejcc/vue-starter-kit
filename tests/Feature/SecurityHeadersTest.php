<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_include_x_frame_options_header(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_responses_include_x_content_type_options_header(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_responses_include_referrer_policy_header(): void
    {
        $response = $this->get('/');

        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_responses_include_permissions_policy_header(): void
    {
        $response = $this->get('/');

        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_security_headers_can_be_disabled(): void
    {
        config(['security.headers.enabled' => false]);

        $response = $this->get('/');

        $response->assertHeaderMissing('X-Frame-Options');
        $response->assertHeaderMissing('X-Content-Type-Options');
    }

    public function test_hsts_header_not_present_when_disabled(): void
    {
        config(['security.headers.strict_transport_security.enabled' => false]);

        $response = $this->get('/');

        $response->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_header_present_when_enabled(): void
    {
        config([
            'security.headers.strict_transport_security.enabled' => true,
            'security.headers.strict_transport_security.max_age' => 31536000,
            'security.headers.strict_transport_security.include_subdomains' => true,
        ]);

        $response = $this->get('/');

        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_csp_header_not_present_when_disabled(): void
    {
        config(['security.headers.content_security_policy.enabled' => false]);

        $response = $this->get('/');

        $response->assertHeaderMissing('Content-Security-Policy');
        $response->assertHeaderMissing('Content-Security-Policy-Report-Only');
    }

    public function test_csp_header_present_when_enabled(): void
    {
        config(['security.headers.content_security_policy.enabled' => true]);

        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self' 'nonce-", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
    }

    public function test_csp_report_only_mode(): void
    {
        config([
            'security.headers.content_security_policy.enabled' => true,
            'security.headers.content_security_policy.report_only' => true,
        ]);

        $response = $this->get('/');

        $response->assertHeaderMissing('Content-Security-Policy');
        $csp = $response->headers->get('Content-Security-Policy-Report-Only');
        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
    }

    public function test_csp_includes_nonce_for_scripts(): void
    {
        config(['security.headers.content_security_policy.enabled' => true]);

        $response = $this->get('/');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-[A-Za-z0-9+\/=]+'/", $csp);
    }
}
