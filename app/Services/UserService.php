<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * User service implementation.
 *
 * Provides business logic for user operations including creation, profile updates,
 * password changes, and account deletion. All operations use transactions and validation.
 */
class UserService extends AbstractService implements UserServiceInterface
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

        return $this->transaction(function () use ($validated) {
            return $this->getRepository()->createUser([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
        });
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

        if (! $user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found.');
        }

        return $this->transaction(function () use ($user, $validated) {
            $this->getRepository()->updateUser($user->id, $validated);

            // Reset email verification if email changed
            if ($user->email !== $validated['email']) {
                $this->getRepository()->updateUser($user->id, [
                    'email_verified_at' => null,
                ]);
            }

            // Refresh the user to get updated data
            $user->refresh();

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

        if (! $user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found.');
        }

        if (! Hash::check($currentPassword, $user->password)) {
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

        return $this->transaction(function () use ($user, $validated) {
            return $this->getRepository()->updatePassword($user->id, Hash::make($validated['password']));
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

        if (! $user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('User not found.');
        }

        if (! Hash::check($password, $user->password)) {
            $validator = validator([], []);
            $validator->errors()->add('password', 'The password is incorrect.');
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $this->transaction(function () use ($user) {
            // Logout the user if they're currently authenticated
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
}
