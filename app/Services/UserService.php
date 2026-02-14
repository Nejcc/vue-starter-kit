<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\AuditEvent;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Exceptions\UserException;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * User service implementation.
 *
 * Provides business logic for user operations including creation, profile updates,
 * password changes, and account deletion. All operations use transactions and validation.
 */
final class UserService extends AbstractService implements UserServiceInterface
{
    /**
     * Create a new user service instance.
     *
     * @param  UserRepositoryInterface  $repository  The user repository instance
     */
    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Create a new user with validation.
     *
     * Validates user data, hashes the password, and creates a new user within
     * a database transaction. Throws ValidationException if validation fails.
     *
     * @param  array<string, mixed>  $data  The user data (name, email, password)
     * @return User The newly created user instance
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails
     *
     * @example
     * $user = $service->create([
     *     'name' => 'John Doe',
     *     'email' => 'john@example.com',
     *     'password' => 'secure_password',
     * ]);
     */
    public function create(array $data): User
    {
        $validated = $this->validate($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
        ]);

        return $this->transaction(fn () => $this->getRepository()->createUser([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]));
    }

    /**
     * Update user profile information.
     *
     * Validates and updates user profile data. If the email address changes,
     * the email verification status is reset. All operations are performed
     * within a database transaction.
     *
     * @param  int  $userId  The user ID
     * @param  array<string, mixed>  $data  The profile data (name, email)
     * @return User The updated user instance
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If user is not found
     * @throws \Illuminate\Validation\ValidationException If validation fails
     *
     * @example
     * $user = $service->updateProfile(1, [
     *     'name' => 'Updated Name',
     *     'email' => 'newemail@example.com',
     * ]);
     */
    public function updateProfile(int $userId, array $data): User
    {
        $validated = $this->validate($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$userId],
        ]);

        $user = $this->getRepository()->findById($userId);

        if (!$user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found.');
        }

        $originalEmail = $user->email;

        return $this->transaction(function () use ($user, $validated, $originalEmail) {
            $oldValues = ['name' => $user->name, 'email' => $user->email];

            $this->getRepository()->updateUser($user->id, $validated);

            if ($originalEmail !== $validated['email']) {
                $user->email_verified_at = null;
                $user->save();
                $user->sendEmailVerificationNotification();
            }

            $user->refresh();

            AuditLog::log(AuditEvent::USER_PROFILE_UPDATED, $user, $oldValues, [
                'name' => $user->name,
                'email' => $user->email,
            ]);

            return $user;
        });
    }

    /**
     * Update user password.
     *
     * Validates the current password, validates the new password strength,
     * and updates the password within a database transaction. The current
     * password must be correct for the update to proceed.
     *
     * @param  int  $userId  The user ID
     * @param  string  $currentPassword  The current password for verification
     * @param  string  $newPassword  The new password
     * @return bool True if the password was updated successfully
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If user is not found
     * @throws \Illuminate\Validation\ValidationException If current password is incorrect or new password is invalid
     *
     * @example
     * $success = $service->updatePassword(1, 'old_password', 'new_secure_password');
     */
    public function updatePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->getRepository()->findById($userId);

        if (!$user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found.');
        }

        if (!Hash::check($currentPassword, $user->password)) {
            $validator = validator([], []);
            $validator->errors()->add('current_password', 'The current password is incorrect.');
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $validated = $this->validate([
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ], [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        return $this->transaction(function () use ($user, $validated): bool {
            $result = $this->getRepository()->updatePassword($user->id, Hash::make($validated['password']));

            AuditLog::log(AuditEvent::USER_PASSWORD_CHANGED, $user);

            return $result;
        });
    }

    /**
     * Delete user account.
     *
     * Verifies the password, logs out the user if they're currently authenticated,
     * and deletes the user account within a database transaction.
     *
     * @param  int  $userId  The user ID
     * @param  string  $password  The user's password for verification
     * @return bool True if the account was deleted successfully
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If user is not found
     * @throws \Illuminate\Validation\ValidationException If password is incorrect
     *
     * @example
     * $success = $service->delete(1, 'user_password');
     */
    public function delete(int $userId, string $password): bool
    {
        $user = $this->getRepository()->findById($userId);

        if (!$user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found.');
        }

        if (!Hash::check($password, $user->password)) {
            $validator = validator([], []);
            $validator->errors()->add('password', 'The password is incorrect.');
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $this->transaction(function () use ($user) {
            AuditLog::log(AuditEvent::USER_ACCOUNT_DELETED, $user, [
                'name' => $user->name,
                'email' => $user->email,
            ]);

            if (Auth::id() === $user->id) {
                Auth::logout();
            }

            return $this->getRepository()->deleteUser($user->id);
        });
    }

    /**
     * Find user by ID.
     *
     * @param  int  $id  The user ID
     * @return User|null The user instance or null if not found
     *
     * @example
     * $user = $service->findById(1);
     */
    public function findById(int $id): ?User
    {
        return $this->getRepository()->findById($id);
    }

    /**
     * Find user by email.
     *
     * @param  string  $email  The email address
     * @return User|null The user instance or null if not found
     *
     * @example
     * $user = $service->findByEmail('user@example.com');
     */
    public function findByEmail(string $email): ?User
    {
        return $this->getRepository()->findByEmail($email);
    }

    /**
     * Search users by name or email.
     *
     * @param  string  $search  The search query
     * @param  int  $limit  Maximum number of results
     * @return Collection<int, User> Collection of matching users
     *
     * @example
     * $users = $service->search('john');
     */
    public function search(string $search, int $limit = 50): Collection
    {
        return $this->getRepository()->search($search, $limit);
    }

    /**
     * Get all users for impersonation (excluding current user).
     *
     * @param  int  $excludeUserId  The user ID to exclude
     * @return Collection<int, User> Collection of users
     *
     * @example
     * $users = $service->getAllForImpersonation(1);
     */
    public function getAllForImpersonation(int $excludeUserId): Collection
    {
        return $this->getRepository()->getAllForImpersonation($excludeUserId);
    }

    public function getAdminPaginated(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->getRepository()->searchPaginated($search, $perPage);
    }

    /**
     * Create a user from admin panel with role assignment.
     *
     * @param  array<string, mixed>  $data
     */
    public function adminCreate(array $data): User
    {
        return $this->transaction(function () use ($data): User {
            $user = $this->getRepository()->createUser([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            if (!empty($data['roles']) && is_array($data['roles'])) {
                $user->assignRole($data['roles']);
            }

            AuditLog::log(AuditEvent::USER_CREATED, $user, null, [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
            ]);

            return $user;
        });
    }

    /**
     * Update a user from admin panel with role sync.
     *
     * @param  array<string, mixed>  $data
     */
    public function adminUpdate(int $userId, array $data): User
    {
        $user = $this->getRepository()->findById($userId);

        if (!$user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found.');
        }

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->toArray(),
        ];

        return $this->transaction(function () use ($user, $data, $oldValues): User {
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $this->getRepository()->updateUser($user->id, $updateData);

            if (array_key_exists('roles', $data)) {
                $user->syncRoles($data['roles'] ?? []);
            }

            $user->refresh();

            AuditLog::log(AuditEvent::USER_UPDATED, $user, $oldValues, [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
            ]);

            return $user;
        });
    }

    /**
     * Delete a user from admin panel.
     */
    public function adminDelete(int $userId): bool
    {
        $user = $this->getRepository()->findById($userId);

        if (!$user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found.');
        }

        if ($user->id === Auth::id()) {
            throw UserException::cannotDeleteOwnAccount();
        }

        AuditLog::log(AuditEvent::USER_DELETED, $user, [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return $this->getRepository()->deleteUser($user->id);
    }

    public function getTotalCount(): int
    {
        return $this->getRepository()->countAll();
    }

    public function getVerifiedCount(): int
    {
        return $this->getRepository()->countVerified();
    }

    public function getRecentUsers(int $limit = 5): Collection
    {
        return $this->getRepository()->getRecent($limit);
    }

    /**
     * Get user permissions data for the dedicated permissions page.
     *
     * @return array<string, mixed>
     */
    public function getPermissionsData(User $user): array
    {
        $user->load(['permissions', 'roles.permissions']);

        $rolePermissions = $user->roles
            ->flatMap(fn ($role) => $role->permissions->pluck('name'))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return [
            'id' => $user->id,
            'slug' => $user->slug,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->toArray(),
            'direct_permissions' => $user->getDirectPermissions()->pluck('name')->toArray(),
            'role_permissions' => $rolePermissions,
        ];
    }

    /**
     * Sync direct permissions on a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function syncPermissions(User $user, array $data): User
    {
        $oldPermissions = $user->getDirectPermissions()->pluck('name')->toArray();

        return $this->transaction(function () use ($user, $data, $oldPermissions): User {
            $user->syncPermissions($data['permissions'] ?? []);
            $user->refresh();

            AuditLog::log(AuditEvent::USER_PERMISSIONS_SYNCED, $user, [
                'direct_permissions' => $oldPermissions,
            ], [
                'direct_permissions' => $user->getDirectPermissions()->pluck('name')->toArray(),
            ]);

            return $user;
        });
    }

    /**
     * Get all users with roles for CSV export.
     *
     * @return Collection<int, User>
     */
    public function getAllForExport(): Collection
    {
        return User::query()
            ->with('roles')
            ->orderBy('id')
            ->get();
    }

    /**
     * Suspend a user account.
     */
    public function suspend(int $userId, ?string $reason = null): User
    {
        $user = $this->getRepository()->findById($userId);

        if (!$user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found.');
        }

        if ($user->id === Auth::id()) {
            throw UserException::cannotSuspendOwnAccount();
        }

        return $this->transaction(function () use ($user, $reason): User {
            $user->update([
                'suspended_at' => now(),
                'suspended_reason' => $reason,
            ]);

            AuditLog::log(AuditEvent::USER_SUSPENDED, $user, null, [
                'reason' => $reason,
            ]);

            $user->refresh();

            return $user;
        });
    }

    /**
     * Unsuspend a user account.
     */
    public function unsuspend(int $userId): User
    {
        $user = $this->getRepository()->findById($userId);

        if (!$user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found.');
        }

        return $this->transaction(function () use ($user): User {
            $previousReason = $user->suspended_reason;

            $user->update([
                'suspended_at' => null,
                'suspended_reason' => null,
            ]);

            AuditLog::log(AuditEvent::USER_UNSUSPENDED, $user, [
                'suspended_reason' => $previousReason,
            ]);

            $user->refresh();

            return $user;
        });
    }
}
