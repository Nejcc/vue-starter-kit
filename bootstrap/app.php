<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureCookieConsent;
use App\Http\Middleware\EnsureUserExists;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $webMiddleware = [
            SecurityHeaders::class,
            EnsureCookieConsent::class,
            EnsureUserExists::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ];

        if (class_exists(\LaravelPlus\Localization\Middleware\SetLocale::class)) {
            array_splice($webMiddleware, 3, 0, [\LaravelPlus\Localization\Middleware\SetLocale::class]);
        }

        $middleware->web(append: $webMiddleware);

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
