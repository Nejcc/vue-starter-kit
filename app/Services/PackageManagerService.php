<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Route;
use LaravelPlus\GlobalSettings\Models\Setting;

final class PackageManagerService
{
    /**
     * Registry of all managed packages.
     *
     * @var array<string, array{name: string, description: string, icon: string, package: string, configKey: string, settingsUrl: string|null, adminUrl: string|null, adminRouteName: string, required: bool}>
     */
    public const PACKAGES = [
        'globalSettings' => [
            'name' => 'Global Settings',
            'description' => 'Application-wide key-value settings with role-based access control.',
            'icon' => 'Settings',
            'package' => 'laravelplus/global-settings',
            'configKey' => 'global-settings.admin.enabled',
            'settingsUrl' => '/admin/settings',
            'adminUrl' => '/admin/settings',
            'adminRouteName' => 'admin.settings.index',
            'required' => true,
        ],
        'payments' => [
            'name' => 'Payment Gateway',
            'description' => 'Payment processing, subscriptions, plans, and transaction management.',
            'icon' => 'CreditCard',
            'package' => 'laravelplus/payment-gateway',
            'configKey' => 'payment-gateway.admin.enabled',
            'settingsUrl' => '/admin/payments',
            'adminUrl' => '/admin/payments',
            'adminRouteName' => 'admin.payments.dashboard',
            'required' => false,
        ],
        'subscribers' => [
            'name' => 'Subscribers',
            'description' => 'Email subscriber lists, double opt-in flows, and subscriber management.',
            'icon' => 'Mail',
            'package' => 'laravelplus/subscribe',
            'configKey' => 'subscribe.admin.enabled',
            'settingsUrl' => '/admin/subscribers',
            'adminUrl' => '/admin/subscribers',
            'adminRouteName' => 'admin.subscribers.index',
            'required' => false,
        ],
        'organizations' => [
            'name' => 'Organizations',
            'description' => 'Multi-tenant organization management with team memberships.',
            'icon' => 'Building2',
            'package' => 'laravelplus/tenants',
            'configKey' => 'tenants.admin.enabled',
            'settingsUrl' => '/admin/organizations/settings',
            'adminUrl' => '/admin/organizations',
            'adminRouteName' => 'admin.organizations.index',
            'required' => false,
        ],
        'localizations' => [
            'name' => 'Localization',
            'description' => 'Multi-language support with translation management and locale detection.',
            'icon' => 'Languages',
            'package' => 'laravelplus/localization',
            'configKey' => 'localization.admin.enabled',
            'settingsUrl' => '/admin/localizations/languages',
            'adminUrl' => '/admin/localizations/languages',
            'adminRouteName' => 'admin.localizations.languages.index',
            'required' => false,
        ],
    ];

    /**
     * Get all packages with their current status.
     *
     * @return array<int, array{key: string, name: string, description: string, icon: string, package: string, enabled: bool, installed: bool, settingsUrl: string|null, adminUrl: string|null, required: bool}>
     */
    public function getAll(): array
    {
        $packages = [];

        foreach (self::PACKAGES as $key => $meta) {
            $installed = Route::has($meta['adminRouteName']);

            $packages[] = [
                'key' => $key,
                'name' => $meta['name'],
                'description' => $meta['description'],
                'icon' => $meta['icon'],
                'package' => $meta['package'],
                'enabled' => $this->isEnabled($key),
                'installed' => $installed,
                'settingsUrl' => $installed ? $meta['settingsUrl'] : null,
                'adminUrl' => $installed ? $meta['adminUrl'] : null,
                'required' => $meta['required'],
            ];
        }

        return $packages;
    }

    /**
     * Check if a specific package is enabled.
     */
    public function isEnabled(string $key): bool
    {
        if (!isset(self::PACKAGES[$key])) {
            return false;
        }

        $meta = self::PACKAGES[$key];

        // Required packages are always enabled
        if ($meta['required']) {
            return true;
        }

        // Check database setting first
        if (class_exists(Setting::class)) {
            $dbValue = Setting::get("package.{$key}.enabled");

            if ($dbValue !== null) {
                return in_array($dbValue, ['1', 'true', true, 1], true);
            }
        }

        // Fall back to config
        return (bool) config($meta['configKey'], true);
    }

    /**
     * Set the enabled state for a package.
     */
    public function setEnabled(string $key, bool $enabled): void
    {
        if (!isset(self::PACKAGES[$key])) {
            return;
        }

        // Prevent disabling required packages
        if (self::PACKAGES[$key]['required'] && !$enabled) {
            return;
        }

        Setting::set("package.{$key}.enabled", $enabled ? '1' : '0');
    }

    /**
     * Validate that a package key exists in the registry.
     */
    public function exists(string $key): bool
    {
        return isset(self::PACKAGES[$key]);
    }

    /**
     * Check if a package is marked as required.
     */
    public function isRequired(string $key): bool
    {
        return self::PACKAGES[$key]['required'] ?? false;
    }
}
