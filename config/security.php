<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Configure HTTP security headers added to all web responses.
    |
    */

    'headers' => [
        'enabled' => env('SECURITY_HEADERS_ENABLED', true),
        'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS', 'SAMEORIGIN'),
        'x_content_type_options' => 'nosniff',
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', 'camera=(), microphone=(), geolocation=()'),
        'strict_transport_security' => [
            'enabled' => env('SECURITY_HSTS_ENABLED', false),
            'max_age' => env('SECURITY_HSTS_MAX_AGE', 31536000),
            'include_subdomains' => env('SECURITY_HSTS_SUBDOMAINS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for authentication-related endpoints.
    |
    */

    'rate_limiting' => [
        'login' => env('SECURITY_RATE_LIMIT_LOGIN', 5),
        'two_factor' => env('SECURITY_RATE_LIMIT_2FA', 5),
        'registration' => env('SECURITY_RATE_LIMIT_REGISTRATION', 5),
        'impersonation' => env('SECURITY_RATE_LIMIT_IMPERSONATION', 5),
        'password_update' => env('SECURITY_RATE_LIMIT_PASSWORD', 6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Routes
    |--------------------------------------------------------------------------
    |
    | Control access to development-only routes (quick-login, quick-register).
    | These should only be enabled in local environments.
    |
    */

    'dev_routes' => [
        'enabled' => env('SECURITY_DEV_ROUTES_ENABLED', false),
        'allowed_ips' => env('SECURITY_DEV_ROUTES_IPS', '127.0.0.1,::1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Browser
    |--------------------------------------------------------------------------
    |
    | Configuration for the admin database browser. Sensitive columns
    | will have their values masked in the UI.
    |
    */

    'database_browser' => [
        'enabled' => env('SECURITY_DB_BROWSER_ENABLED', true),
        'masked_columns' => [
            'password',
            'secret',
            'token',
            'api_key',
            'private_key',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'remember_token',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Additional session security settings.
    |
    */

    'session' => [
        'encrypt' => env('SESSION_ENCRYPT', false),
        'secure_cookie' => env('SESSION_SECURE_COOKIE', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Timeout
    |--------------------------------------------------------------------------
    |
    | Number of seconds before a password confirmation expires.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
