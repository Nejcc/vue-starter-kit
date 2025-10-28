<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserService extends AbstractService implements UserServiceInterface
{
    /**
     * Create a new user service instance.
     */
    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Create a new user with validation.
     *
     * @param  array<string, mixed>  $data
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
     * @param  array<string, mixed>  $data
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
     */
    public function findById(int $id): ?User
    {
        return $this->getRepository()->findById($id);
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->getRepository()->findByEmail($email);
    }
}
