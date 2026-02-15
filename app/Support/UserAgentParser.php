<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Parses user agent strings to extract browser and platform information.
 */
final class UserAgentParser
{
    /**
     * Parse a user agent string to extract browser and platform info.
     *
     * @return array{browser: string, platform: string, is_desktop: bool, is_mobile: bool}
     */
    public static function parse(?string $userAgent): array
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
