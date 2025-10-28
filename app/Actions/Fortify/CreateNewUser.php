<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
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
