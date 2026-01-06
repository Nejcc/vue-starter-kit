<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Contracts\RepositoryInterface;
use App\Models\Setting;

/**
 * @extends RepositoryInterface<Setting>
 */
interface SettingsRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a setting value by key.
     *
     * @param  string  $key  The setting key
     * @param  mixed  $default  The default value if not found
     * @return mixed The setting value or default
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a setting value by key.
     *
     * @param  string  $key  The setting key
     * @param  mixed  $value  The value to set
     * @return bool True if successful
     */
    public function set(string $key, mixed $value): bool;

    /**
     * Check if a setting exists.
     *
     * @param  string  $key  The setting key
     * @return bool True if the setting exists
     */
    public function has(string $key): bool;
}
