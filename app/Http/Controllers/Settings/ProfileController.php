<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\User\DeleteUserAction;
use App\Actions\User\UpdateUserProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Profile controller for user profile management.
 *
 * Handles displaying and updating user profile information, as well as
 * account deletion. Uses actions to encapsulate business logic.
 */
final class ProfileController extends Controller
{
    /**
     * Create a new profile controller instance.
     *
     * @param  UpdateUserProfileAction  $updateUserProfileAction  Action for updating user profile
     * @param  DeleteUserAction  $deleteUserAction  Action for deleting user account
     */
    public function __construct(
        private readonly UpdateUserProfileAction $updateUserProfileAction,
        private readonly DeleteUserAction $deleteUserAction
    ) {}

    /**
     * Show the user's profile settings page.
     *
     * @param  Request  $request  The incoming request
     * @return Response The Inertia response with profile page data
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
     *
     * Validates and updates the authenticated user's profile information
     * using the UpdateUserProfileAction.
     *
     * @param  ProfileUpdateRequest  $request  The validated request
     * @return RedirectResponse Redirect to profile edit page
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
     *
     * Validates the password and deletes the authenticated user's account
     * using the DeleteUserAction. Invalidates the session after deletion.
     *
     * @param  ProfileDeleteRequest  $request  The validated request
     * @return RedirectResponse Redirect to home page
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $this->deleteUserAction->handle(
            $request->user()->id,
            $request->input('password'),
            $request
        );

        return redirect('/');
    }
}
