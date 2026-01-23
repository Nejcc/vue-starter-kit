<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\RoleNames;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingStoreRequest;
use App\Http\Requests\Admin\SettingsUpdateRequest;
use App\Http\Requests\Admin\SettingUpdateRequest;
use App\Models\Setting;
use App\Services\SettingsService;
use App\SettingRole;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin settings controller.
 *
 * Handles displaying and updating all application settings.
 */
final class SettingsController extends Controller
{
    /**
     * Create a new admin settings controller instance.
     */
    public function __construct(
        protected SettingsService $settingsService
    ) {
        $this->middleware('auth');
    }

    /**
     * Check if user has admin or super-admin role.
     */
    private function authorizeAdmin(): void
    {
        $user = auth()->user();

        if (!$user || (!$user->hasRole(RoleNames::SUPER_ADMIN) && !$user->hasRole(RoleNames::ADMIN))) {
            abort(403, 'Unauthorized. Admin access required.');
        }
    }

    /**
     * Show the admin settings page.
     *
     * @param  Request  $request  The incoming request
     * @return Response The Inertia response with settings page data
     */
    public function index(Request $request): Response
    {
        $this->authorizeAdmin();

        $settings = $this->settingsService->all();

        // Search functionality
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->get('search');
            $settings = $settings->filter(fn ($setting) => mb_stripos($setting->key, $search) !== false
                    || mb_stripos($setting->label ?? '', $search) !== false
                    || mb_stripos($setting->description ?? '', $search) !== false
                    || mb_stripos($setting->value ?? '', $search) !== false);
        }

        return Inertia::render('admin/Settings', [
            'settings' => $settings->map(fn ($setting) => [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $setting->value,
                'field_type' => $setting->field_type ?? 'input',
                'options' => $setting->options,
                'label' => $setting->label ?? $setting->key,
                'description' => $setting->description,
                'role' => $setting->role?->value ?? SettingRole::User->value,
            ])->values(),
            'status' => $request->session()->get('status'),
            'filters' => [
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    /**
     * Show the form for creating a new setting.
     *
     * @return Response The Inertia response with create form
     */
    public function create(): Response
    {
        $this->authorizeAdmin();

        return Inertia::render('admin/Settings/Create');
    }

    /**
     * Store a newly created setting.
     *
     * @param  SettingStoreRequest  $request  The validated request
     * @return RedirectResponse Redirect to admin settings page
     */
    public function store(SettingStoreRequest $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validated();

        // Handle checkbox values
        if ($data['field_type'] === 'checkbox' && isset($data['value'])) {
            $data['value'] = filter_var($data['value'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }

        // Set default role to 'user' if not provided
        if (!isset($data['role'])) {
            $data['role'] = SettingRole::User->value;
        }

        $this->settingsService->create($data);

        return redirect()->route('admin.settings.index')->with('status', 'Setting created successfully.');
    }

    /**
     * Show the form for editing a setting.
     *
     * @param  Setting  $setting  The setting to edit
     * @return Response The Inertia response with edit form
     */
    public function edit(Setting $setting): Response
    {
        $this->authorizeAdmin();

        return Inertia::render('admin/Settings/Edit', [
            'setting' => [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $setting->value,
                'field_type' => $setting->field_type ?? 'input',
                'options' => $setting->options,
                'label' => $setting->label,
                'description' => $setting->description,
                'role' => $setting->role?->value ?? SettingRole::User->value,
            ],
        ]);
    }

    /**
     * Update a specific setting.
     *
     * @param  SettingUpdateRequest  $request  The validated request
     * @param  Setting  $setting  The setting to update
     * @return RedirectResponse Redirect to admin settings page
     */
    public function update(SettingUpdateRequest $request, Setting $setting): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validated();

        // Prevent changing role of system settings
        if ($setting->role === SettingRole::System && isset($validated['role']) && $validated['role'] !== SettingRole::System->value) {
            unset($validated['role']);
        }

        // Handle checkbox values
        if ($validated['field_type'] === 'checkbox' && isset($validated['value'])) {
            $validated['value'] = filter_var($validated['value'], FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }

        $this->settingsService->update($setting->id, $validated);

        return redirect()->route('admin.settings.index')->with('status', 'Setting updated successfully.');
    }

    /**
     * Update multiple settings at once (bulk update).
     *
     * @param  SettingsUpdateRequest  $request  The validated request
     * @return RedirectResponse Redirect to admin settings page
     */
    public function bulkUpdate(SettingsUpdateRequest $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $settings = $request->validated()['settings'];

        foreach ($settings as $key => $value) {
            // Handle checkbox values - if it's a boolean or '1'/'0', convert properly
            if (is_bool($value) || $value === '1' || $value === '0' || $value === 'true' || $value === 'false') {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
            $this->settingsService->set($key, $value);
        }

        return redirect()->route('admin.settings.index')->with('status', 'Settings updated successfully.');
    }

    /**
     * Remove the specified setting.
     *
     * @param  Setting  $setting  The setting to delete
     * @return RedirectResponse Redirect to admin settings page
     */
    public function destroy(Setting $setting): RedirectResponse
    {
        $this->authorizeAdmin();

        try {
            $this->settingsService->delete($setting->id);

            return redirect()->route('admin.settings.index')->with('status', 'Setting deleted successfully.');
        } catch (Exception $e) {
            return redirect()->route('admin.settings.index')
                ->with('error', $e->getMessage());
        }
    }
}
