<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!config('security.headers.enabled', true)) {
            return $response;
        }

        $response->headers->set('X-Frame-Options', config('security.headers.x_frame_options', 'SAMEORIGIN'));
        $response->headers->set('X-Content-Type-Options', config('security.headers.x_content_type_options', 'nosniff'));
        $response->headers->set('Referrer-Policy', config('security.headers.referrer_policy', 'strict-origin-when-cross-origin'));
        $response->headers->set('Permissions-Policy', config('security.headers.permissions_policy', 'camera=(), microphone=(), geolocation=()'));

        $hsts = config('security.headers.strict_transport_security', []);
        if (!empty($hsts['enabled'])) {
            $maxAge = $hsts['max_age'] ?? 31536000;
            $value = "max-age={$maxAge}";
            if (!empty($hsts['include_subdomains'])) {
                $value .= '; includeSubDomains';
            }
            $response->headers->set('Strict-Transport-Security', $value);
        }

        return $response;
    }
}
