<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use LaravelPlus\GlobalSettings\Enums\SettingGroup;
use PHPUnit\Framework\TestCase;

final class SettingGroupTest extends TestCase
{
    /**
     * Test that all cases have lowercase string values.
     */
    public function test_all_cases_have_lowercase_values(): void
    {
        foreach (SettingGroup::cases() as $case) {
            $this->assertSame(mb_strtolower($case->value), $case->value, "Case {$case->name} value should be lowercase");
        }
    }

    /**
     * Test that all cases have non-empty labels.
     */
    public function test_all_cases_have_labels(): void
    {
        foreach (SettingGroup::cases() as $case) {
            $label = $case->label();
            $this->assertNotEmpty($label, "Case {$case->name} should have a non-empty label");
            $this->assertIsString($label);
        }
    }

    /**
     * Test that the expected cases exist.
     */
    public function test_expected_cases_exist(): void
    {
        $expectedCases = ['General', 'Authentication', 'Notifications', 'Security', 'Appearance', 'System'];

        $actualCases = array_map(fn (SettingGroup $case): string => $case->name, SettingGroup::cases());

        $this->assertSame($expectedCases, $actualCases);
    }

    /**
     * Test that tryFrom returns null for invalid values.
     */
    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(SettingGroup::tryFrom('nonexistent'));
        $this->assertNull(SettingGroup::tryFrom(''));
        $this->assertNull(SettingGroup::tryFrom('General'));
    }

    /**
     * Test that tryFrom returns correct cases for valid values.
     */
    public function test_try_from_returns_correct_case(): void
    {
        $this->assertSame(SettingGroup::General, SettingGroup::tryFrom('general'));
        $this->assertSame(SettingGroup::Authentication, SettingGroup::tryFrom('authentication'));
        $this->assertSame(SettingGroup::System, SettingGroup::tryFrom('system'));
    }
}
