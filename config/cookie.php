<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cookie Consent Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for cookie consent management
    | and GDPR compliance features.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Master Toggle
    |--------------------------------------------------------------------------
    |
    | Enable or disable the cookie consent banner system entirely.
    |
    */

    'enabled' => env('COOKIE_CONSENT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | GDPR Mode
    |--------------------------------------------------------------------------
    |
    | Enable strict GDPR compliance features including:
    | - Data processing consent during registration
    | - Audit logging of consent changes
    | - IP address tracking for consent
    |
    */

    'gdpr_mode' => env('COOKIE_CONSENT_GDPR_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for storing consent preferences.
    |
    */

    'storage' => [
        'key_prefix' => env('COOKIE_CONSENT_KEY_PREFIX', 'cookie_consent'),
        'lifetime' => env('COOKIE_CONSENT_LIFETIME', 365), // days
        'session_key' => 'cookie_consent_preferences',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cookie Categories
    |--------------------------------------------------------------------------
    |
    | Define the different categories of cookies with their properties.
    | Essential cookies cannot be disabled by users.
    |
    */

    'categories' => [
        'essential' => [
            'name' => 'Essential Cookies',
            'description' => 'These cookies are necessary for the website to function and cannot be switched off in our systems.',
            'required' => true,
            'default_enabled' => true,
            'cookies' => [
                'laravel_session',
                'XSRF-TOKEN',
                'remember_token',
            ],
        ],
        'analytics' => [
            'name' => 'Analytics Cookies',
            'description' => 'These cookies allow us to count visits and traffic sources so we can measure and improve the performance of our site.',
            'required' => false,
            'default_enabled' => false,
            'cookies' => [
                '_ga',
                '_gid',
                '_gat',
                '_gtag',
            ],
        ],
        'marketing' => [
            'name' => 'Marketing Cookies',
            'description' => 'These cookies may be set through our site by our advertising partners to build a profile of your interests.',
            'required' => false,
            'default_enabled' => false,
            'cookies' => [
                '_fbp',
                'fr',
                'tr',
            ],
        ],
        'preferences' => [
            'name' => 'Preference Cookies',
            'description' => 'These cookies enable the website to provide enhanced functionality and personalisation.',
            'required' => false,
            'default_enabled' => false,
            'cookies' => [
                'theme_preference',
                'language_preference',
                'cookie_preferences',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Banner Configuration
    |--------------------------------------------------------------------------
    |
    | Text and styling options for the cookie consent banner.
    |
    */

    'banner' => [
        'title' => 'We use cookies',
        'description' => 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.',
        'buttons' => [
            'accept_all' => 'Accept All',
            'reject_all' => 'Reject All',
            'customize' => 'Customize',
            'save_preferences' => 'Save Preferences',
        ],
        'links' => [
            'privacy_policy' => [
                'text' => 'Privacy Policy',
                'url' => '/privacy-policy',
            ],
            'cookie_policy' => [
                'text' => 'Cookie Policy',
                'url' => '/cookie-policy',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Modal Configuration
    |--------------------------------------------------------------------------
    |
    | Text and options for the detailed cookie preferences modal.
    |
    */

    'modal' => [
        'title' => 'Cookie Preferences',
        'description' => 'Manage your cookie preferences. You can enable or disable different types of cookies below.',
        'buttons' => [
            'save' => 'Save Preferences',
            'cancel' => 'Cancel',
            'accept_all' => 'Accept All',
            'reject_all' => 'Reject All',
        ],
        'sections' => [
            'essential_title' => 'Essential Cookies',
            'essential_description' => 'These cookies are necessary for the website to function and cannot be switched off.',
            'optional_title' => 'Optional Cookies',
            'optional_description' => 'You can choose which optional cookies to allow.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Styling Configuration
    |--------------------------------------------------------------------------
    |
    | Visual styling options for the cookie consent components.
    |
    */

    'styling' => [
        'position' => 'bottom', // bottom, top
        'theme' => 'auto', // light, dark, auto
        'animation' => [
            'duration' => 300, // milliseconds
            'easing' => 'ease-out',
        ],
        'z_index' => 9999,
        'backdrop_blur' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Configuration for logging consent changes for GDPR compliance.
    |
    */

    'audit_logging' => [
        'enabled' => env('COOKIE_CONSENT_AUDIT_LOGGING', true),
        'log_channel' => env('COOKIE_CONSENT_LOG_CHANNEL', 'daily'),
        'log_level' => env('COOKIE_CONSENT_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Processing Consent
    |--------------------------------------------------------------------------
    |
    | Configuration for GDPR data processing consent during registration.
    |
    */

    'data_processing' => [
        'required' => true,
        'text' => 'I agree to the processing of my personal data in accordance with the Privacy Policy.',
        'link_text' => 'Privacy Policy',
        'link_url' => '/privacy-policy',
        'validation_message' => 'You must agree to the processing of your personal data to create an account.',
    ],

];
