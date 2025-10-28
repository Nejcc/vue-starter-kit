<?php

namespace App\Http\Controllers\Settings;

use App\Actions\User\DeleteUserAction;
use App\Actions\User\UpdateUserProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Create a new profile controller instance.
     */
    public function __construct(
        private readonly UpdateUserProfileAction $updateUserProfileAction,
        private readonly DeleteUserAction $deleteUserAction
    ) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->updateUserProfileAction->handle(
            $request->user()->id,
            $request->validated()
        );

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $this->deleteUserAction->handle(
            $request->user()->id,
            $request->input('password'),
            $request
        );

        return redirect('/');
    }
}
