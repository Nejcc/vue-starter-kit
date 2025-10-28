<?php

namespace App\Http\Controllers\Settings;

use App\Actions\User\UpdateUserPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * Create a new password controller instance.
     */
    public function __construct(
        private readonly UpdateUserPasswordAction $updateUserPasswordAction
    ) {}

    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/Password');
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $this->updateUserPasswordAction->handle(
            $request->user()->id,
            $request->input('current_password'),
            $request->input('password')
        );

        return back();
    }
}
