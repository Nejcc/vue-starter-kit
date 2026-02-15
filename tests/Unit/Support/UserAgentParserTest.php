<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\UserAgentParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserAgentParserTest extends TestCase
{
    #[Test]
    public function it_parses_null_user_agent(): void
    {
        $result = UserAgentParser::parse(null);

        $this->assertSame('Unknown', $result['browser']);
        $this->assertSame('Unknown', $result['platform']);
        $this->assertTrue($result['is_desktop']);
        $this->assertFalse($result['is_mobile']);
    }

    #[Test]
    public function it_parses_empty_user_agent(): void
    {
        $result = UserAgentParser::parse('');

        $this->assertSame('Unknown', $result['browser']);
        $this->assertSame('Unknown', $result['platform']);
    }

    #[Test]
    #[DataProvider('userAgentProvider')]
    public function it_parses_user_agents(string $ua, string $expectedBrowser, string $expectedPlatform, bool $expectedMobile): void
    {
        $result = UserAgentParser::parse($ua);

        $this->assertSame($expectedBrowser, $result['browser']);
        $this->assertSame($expectedPlatform, $result['platform']);
        $this->assertSame($expectedMobile, $result['is_mobile']);
        $this->assertSame(!$expectedMobile, $result['is_desktop']);
    }

    /** @return array<string, array{string, string, string, bool}> */
    public static function userAgentProvider(): array
    {
        return [
            'Chrome on Windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Chrome', 'Windows', false,
            ],
            'Firefox on Linux' => [
                'Mozilla/5.0 (X11; Linux x86_64; rv:121.0) Gecko/20100101 Firefox/121.0',
                'Firefox', 'Linux', false,
            ],
            'Safari on macOS' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_2) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
                'Safari', 'macOS', false,
            ],
            'Edge on Windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
                'Edge', 'Windows', false,
            ],
            'Opera on Windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 OPR/106.0.0.0',
                'Opera', 'Windows', false,
            ],
            'Chrome on Android' => [
                'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.6099.144 Mobile Safari/537.36',
                'Chrome', 'Android', true,
            ],
            'Safari on iOS' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
                'Safari', 'iOS', true,
            ],
            'Chrome on iPad' => [
                'Mozilla/5.0 (iPad; CPU OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1',
                'Safari', 'iOS', true,
            ],
        ];
    }
}
