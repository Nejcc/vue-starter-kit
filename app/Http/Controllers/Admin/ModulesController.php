<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

final class ModulesController extends Controller
{
    public function index(): Response
    {
        $modules = [
            [
                'key' => 'globalSettings',
                'name' => 'Global Settings',
                'description' => 'Application-wide key-value settings with role-based access control.',
                'icon' => 'Settings',
                'package' => 'laravelplus/global-settings',
                'installed' => Route::has('admin.settings.index'),
                'adminUrl' => Route::has('admin.settings.index') ? route('admin.settings.index') : null,
            ],
            [
                'key' => 'payments',
                'name' => 'Payment Gateway',
                'description' => 'Payment processing, subscriptions, plans, and transaction management.',
                'icon' => 'CreditCard',
                'package' => 'nejcc/payment-gateway',
                'installed' => Route::has('admin.payments.dashboard'),
                'adminUrl' => Route::has('admin.payments.dashboard') ? route('admin.payments.dashboard') : null,
            ],
            [
                'key' => 'subscribers',
                'name' => 'Subscribers',
                'description' => 'Email subscriber lists, double opt-in flows, and subscriber management.',
                'icon' => 'Mail',
                'package' => 'nejcc/subscribe',
                'installed' => Route::has('admin.subscribers.index'),
                'adminUrl' => Route::has('admin.subscribers.index') ? route('admin.subscribers.index') : null,
            ],
            [
                'key' => 'organizations',
                'name' => 'Organizations',
                'description' => 'Multi-tenant organization management with team memberships.',
                'icon' => 'Building2',
                'package' => 'laravelplus/tenants',
                'installed' => Route::has('admin.organizations.index'),
                'adminUrl' => Route::has('admin.organizations.index') ? route('admin.organizations.index') : null,
            ],
            [
                'key' => 'horizon',
                'name' => 'Horizon',
                'description' => 'Redis queue monitoring dashboard with metrics and job management.',
                'icon' => 'Activity',
                'package' => 'laravel/horizon',
                'installed' => Route::has('horizon.index'),
                'adminUrl' => Route::has('horizon.index') ? '/horizon' : null,
            ],
        ];

        return Inertia::render('admin/Modules/Index', [
            'modules' => $modules,
        ]);
    }
}
