<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\SettingsRepositoryInterface;
use App\Models\Setting;
use App\SettingRole;
use Exception;
use Illuminate\Database\Eloquent\Collection;

/**
 * Settings service implementation.
 *
 * Provides business logic for application settings management.
 * Extends AbstractService to inherit transaction and validation support.
 *
 * @extends AbstractService<Setting>
 */
final class SettingsService extends AbstractService
{
    /**
     * Create a new settings service instance.
     *
     * @param  SettingsRepositoryInterface  $repository  The settings repository instance
     */
    public function __construct(SettingsRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Get a setting value by key.
     *
     * @param  string  $key  The setting key
     * @param  mixed  $default  The default value if not found
     * @return mixed The setting value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->repository->get($key, $default);
    }

    /**
     * Set a setting value by key.
     *
     * @param  string  $key  The setting key
     * @param  mixed  $value  The value to set
     * @return bool True if successful
     */
    public function set(string $key, mixed $value): bool
    {
        return $this->repository->set($key, $value);
    }

    /**
     * Check if a setting exists.
     *
     * @param  string  $key  The setting key
     * @return bool True if the setting exists
     */
    public function has(string $key): bool
    {
        return $this->repository->has($key);
    }

    /**
     * Get all settings.
     *
     * @return Collection<int, Setting>
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get a setting by ID.
     *
     * @param  int  $id  The setting ID
     * @return Setting|null The setting or null if not found
     */
    public function findById(int $id): ?Setting
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new setting.
     *
     * @param  array<string, mixed>  $data  The setting data
     * @return Setting The created setting
     */
    public function create(array $data): Setting
    {
        return $this->transaction(function () use ($data) {
            $validated = $this->validate($data, [
                'key' => ['required', 'string', 'max:255', 'unique:settings,key'],
                'label' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:1000'],
                'field_type' => ['required', 'in:input,checkbox,multioptions'],
                'options' => ['nullable', 'string'],
                'value' => ['nullable'],
                'role' => ['required', 'in:system,user,plugin'],
            ]);

            return $this->repository->create($validated);
        });
    }

    /**
     * Update a setting.
     *
     * @param  int  $id  The setting ID
     * @param  array<string, mixed>  $data  The setting data
     * @return bool True if successful
     */
    public function update(int $id, array $data): bool
    {
        return $this->transaction(function () use ($id, $data) {
            $validated = $this->validate($data, [
                'key' => ['required', 'string', 'max:255', 'unique:settings,key,'.$id],
                'label' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:1000'],
                'field_type' => ['required', 'in:input,checkbox,multioptions'],
                'options' => ['nullable', 'string'],
                'value' => ['nullable'],
                'role' => ['required', 'in:system,user,plugin'],
            ]);

            return $this->repository->update($id, $validated);
        });
    }

    /**
     * Delete a setting.
     *
     * Only non-system settings can be deleted.
     *
     * @param  int  $id  The setting ID
     * @return bool True if successful
     *
     * @throws Exception If attempting to delete a system setting
     */
    public function delete(int $id): bool
    {
        $setting = $this->findById($id);

        if (!$setting) {
            return false;
        }

        if ($setting->role === SettingRole::System) {
            throw new Exception('System settings cannot be deleted.');
        }

        return $this->repository->delete($id);
    }

    /**
     * Search settings by key, label, description, or value.
     *
     * @param  string  $search  The search term
     * @return Collection<int, Setting>
     */
    public function search(string $search): Collection
    {
        return $this->repository->search([
            ['key', 'like', "%{$search}%"],
            ['label', 'like', "%{$search}%"],
            ['description', 'like', "%{$search}%"],
            ['value', 'like', "%{$search}%"],
        ]);
    }

    /**
     * Get settings by role.
     *
     * @param  string  $role  The role (system, user, plugin)
     * @return Collection<int, Setting>
     */
    public function getByRole(string $role): Collection
    {
        return $this->repository->findAllBy(['role' => $role]);
    }

    /**
     * Get multiple setting values at once.
     *
     * @param  array<int, string>  $keys  Array of setting keys
     * @return array<string, mixed> Associative array of key => value pairs
     */
    public function getMultiple(array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }

        return $result;
    }

    /**
     * Set multiple setting values at once.
     *
     * @param  array<string, mixed>  $settings  Associative array of key => value pairs
     * @return bool True if all settings were updated successfully
     */
    public function setMultiple(array $settings): bool
    {
        return $this->transaction(function () use ($settings) {
            foreach ($settings as $key => $value) {
                if (!$this->set($key, $value)) {
                    return false;
                }
            }

            return true;
        });
    }
}
