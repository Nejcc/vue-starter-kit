<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Google Tag Manager ID
    |--------------------------------------------------------------------------
    |
    | Your GTM container ID (e.g. "GTM-XXXXXX"). When set, the GTM snippet
    | will be injected on pages where the user has accepted analytics cookies.
    |
    */

    'gtm_id' => env('GOOGLE_TAG_MANAGER_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Meta Description
    |--------------------------------------------------------------------------
    |
    | Fallback meta description used when a page does not provide its own.
    |
    */

    'default_meta_description' => env('DEFAULT_META_DESCRIPTION', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Robots
    |--------------------------------------------------------------------------
    |
    | Default robots meta tag value. Use "index, follow" for production
    | and "noindex, nofollow" for staging/development environments.
    |
    */

    'default_robots' => env('DEFAULT_ROBOTS', 'index, follow'),

];
