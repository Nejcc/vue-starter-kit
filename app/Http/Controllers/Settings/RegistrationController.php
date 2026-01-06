<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Contracts\Repositories\SettingsRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\RegistrationSettingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Registration settings controller.
 *
 * Handles displaying and updating registration settings.
 */
final class RegistrationController extends Controller
{
    /**
     * Create a new registration controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the registration settings page.
     *
     * @param  Request  $request  The incoming request
     * @return Response The Inertia response with registration settings page data
     */
    public function edit(Request $request): Response
    {
        $settingsRepository = app(SettingsRepositoryInterface::class);
        $registrationEnabled = (bool) $settingsRepository->get('registration_enabled', false);

        return Inertia::render('settings/Registration', [
            'registrationEnabled' => $registrationEnabled,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the registration setting.
     *
     * @param  RegistrationSettingRequest  $request  The validated request
     * @return RedirectResponse Redirect to registration settings page
     */
    public function update(RegistrationSettingRequest $request): RedirectResponse
    {
        $settingsRepository = app(SettingsRepositoryInterface::class);
        $settingsRepository->set('registration_enabled', $request->boolean('registration_enabled'));

        return to_route('registration.edit')->with('status', 'Registration setting updated successfully.');
    }
}
