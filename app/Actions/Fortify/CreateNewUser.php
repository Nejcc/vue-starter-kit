<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use LaravelPlus\GlobalSettings\Contracts\SettingsRepositoryInterface;

final class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        // Check if registration is enabled
        try {
            $settingsRepository = app(SettingsRepositoryInterface::class);
            $registrationEnabled = (bool) $settingsRepository->get('registration_enabled', false);

            if (!$registrationEnabled) {
                throw ValidationException::withMessages([
                    'email' => ['Registration is currently disabled. Please contact an administrator.'],
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // If settings table doesn't exist, default to disabled
            throw ValidationException::withMessages([
                'email' => ['Registration is currently disabled. Please contact an administrator.'],
            ]);
        }

        $rules = [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ];

        // Add GDPR data processing consent validation if enabled
        if (config('cookie.gdpr_mode', true) && config('cookie.data_processing.required', true)) {
            $rules['data_processing_consent'] = ['required', 'accepted'];
        }

        Validator::make($input, $rules, [
            'data_processing_consent.required' => config('cookie.data_processing.validation_message', 'You must agree to the processing of your personal data to create an account.'),
            'data_processing_consent.accepted' => config('cookie.data_processing.validation_message', 'You must agree to the processing of your personal data to create an account.'),
        ])->validate();

        $userData = [
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ];

        // Add GDPR consent data if provided
        if (isset($input['data_processing_consent'])) {
            $userData['data_processing_consent'] = (bool) $input['data_processing_consent'];
            $userData['data_processing_consent_given_at'] = now();
            $userData['gdpr_ip_address'] = request()->ip();
        }

        return User::create($userData);
    }
}
