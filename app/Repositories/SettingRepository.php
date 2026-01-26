<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\SettingsRepositoryInterface;
use App\Models\Setting;

/**
 * Setting repository implementation.
 *
 * Provides data access methods for Setting models.
 * Extends BaseRepository to inherit base CRUD operations.
 */
final class SettingRepository extends BaseRepository implements SettingsRepositoryInterface
{
    /**
     * Create a new setting repository instance.
     */
    public function __construct()
    {
        parent::__construct(Setting::class);
    }

    /**
     * Get a setting value by key.
     *
     * Automatically decodes JSON strings and returns the appropriate value type.
     *
     * @param  string  $key  The setting key
     * @param  mixed  $default  The default value if not found
     * @return mixed The setting value or default
     *
     * @example
     * $siteName = $repository->get('site_name', 'Default Site');
     * $settings = $repository->get('mail_settings', []);
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = $this->findBy(['key' => $key]);

        if (!$setting) {
            return $default;
        }

        $value = $setting->value;

        // Try to decode JSON if it's a JSON string
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    /**
     * Set a setting value by key.
     *
     * Automatically converts booleans to strings and encodes arrays/objects as JSON.
     * Creates a new setting if it doesn't exist, otherwise updates the existing one.
     *
     * @param  string  $key  The setting key
     * @param  mixed  $value  The value to set
     * @return bool True if successful
     *
     * @example
     * $repository->set('site_name', 'My Site');
     * $repository->set('maintenance_mode', true);
     * $repository->set('mail_settings', ['driver' => 'smtp']);
     */
    public function set(string $key, mixed $value): bool
    {
        // Convert boolean to string for checkbox fields
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        // Encode arrays/objects as JSON
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        $setting = $this->findBy(['key' => $key]);

        if ($setting) {
            return $this->update($setting->id, ['value' => $value]);
        }

        return $this->create(['key' => $key, 'value' => $value]) !== null;
    }

    /**
     * Check if a setting exists.
     *
     * @param  string  $key  The setting key
     * @return bool True if the setting exists
     *
     * @example
     * if ($repository->has('maintenance_mode')) {
     *     // Setting exists
     * }
     */
    public function has(string $key): bool
    {
        return $this->findBy(['key' => $key]) !== null;
    }
}
