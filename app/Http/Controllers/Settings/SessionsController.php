<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class SessionsController extends Controller
{
    /**
     * Show the user's active sessions.
     */
    public function index(Request $request): Response
    {
        $sessions = $this->getSessions($request);

        return Inertia::render('settings/Sessions', [
            'sessions' => $sessions,
            'currentSessionId' => $request->session()->getId(),
        ]);
    }

    /**
     * Destroy a specific session.
     */
    public function destroy(Request $request, string $sessionId): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->input('password'), $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        if ($sessionId === $request->session()->getId()) {
            return redirect()->back()->withErrors([
                'session' => 'You cannot revoke your current session. Use logout instead.',
            ]);
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->delete();

        return redirect()->back()->with('status', 'Session revoked successfully.');
    }

    /**
     * Destroy all sessions except the current one.
     */
    public function destroyAll(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->input('password'), $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return redirect()->back()->with('status', 'All other sessions have been revoked.');
    }

    /**
     * Get all sessions for the authenticated user.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getSessions(Request $request): array
    {
        $currentSessionId = $request->session()->getId();

        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get();

        return $sessions->map(function (object $session) use ($currentSessionId): array {
            $device = $this->parseUserAgent($session->user_agent);

            return [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'is_current' => $session->id === $currentSessionId,
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                'last_active_at' => Carbon::createFromTimestamp($session->last_activity)->toIso8601String(),
                'device' => $device,
            ];
        })->all();
    }

    /**
     * Parse a user agent string to extract browser and platform info.
     *
     * @return array{browser: string, platform: string, is_desktop: bool, is_mobile: bool}
     */
    private function parseUserAgent(?string $userAgent): array
    {
        $ua = $userAgent ?? '';

        $browser = 'Unknown';
        $platform = 'Unknown';
        $isMobile = false;

        // Detect platform (check mobile OSes before desktop to avoid false matches)
        if (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            $platform = 'iOS';
        } elseif (preg_match('/Android/i', $ua)) {
            $platform = 'Android';
        } elseif (preg_match('/Windows/i', $ua)) {
            $platform = 'Windows';
        } elseif (preg_match('/Macintosh/i', $ua)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $ua)) {
            $platform = 'Linux';
        }

        // Detect browser
        if (preg_match('/Edg(e|A)?\/[\d.]+/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/OPR\/[\d.]+/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/Chrome\/[\d.]+/i', $ua) && !preg_match('/Edg/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\/[\d.]+/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\/[\d.]+/i', $ua) && !preg_match('/Chrome/i', $ua)) {
            $browser = 'Safari';
        }

        // Detect mobile
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $ua)) {
            $isMobile = true;
        }

        return [
            'browser' => $browser,
            'platform' => $platform,
            'is_desktop' => !$isMobile,
            'is_mobile' => $isMobile,
        ];
    }
}
