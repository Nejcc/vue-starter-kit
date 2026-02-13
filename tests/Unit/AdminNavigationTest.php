<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\AdminNavigation;
use PHPUnit\Framework\TestCase;

final class AdminNavigationTest extends TestCase
{
    public function test_register_adds_group(): void
    {
        $nav = new AdminNavigation();
        $nav->register('payments', 'Payments', 'CreditCard', [
            ['title' => 'Dashboard', 'href' => '/admin/payments', 'icon' => 'LayoutDashboard'],
        ]);

        $groups = $nav->groups();

        $this->assertCount(1, $groups);
        $this->assertSame('Payments', $groups[0]['title']);
        $this->assertSame('CreditCard', $groups[0]['icon']);
        $this->assertCount(1, $groups[0]['items']);
        $this->assertSame('Dashboard', $groups[0]['items'][0]['title']);
    }

    public function test_groups_sorted_by_priority(): void
    {
        $nav = new AdminNavigation();
        $nav->register('subscribers', 'Subscribers', 'Mail', [], 20);
        $nav->register('payments', 'Payments', 'CreditCard', [], 10);
        $nav->register('analytics', 'Analytics', 'BarChart', [], 30);

        $groups = $nav->groups();

        $this->assertSame('Payments', $groups[0]['title']);
        $this->assertSame('Subscribers', $groups[1]['title']);
        $this->assertSame('Analytics', $groups[2]['title']);
    }

    public function test_duplicate_key_overrides(): void
    {
        $nav = new AdminNavigation();
        $nav->register('payments', 'Payments V1', 'CreditCard', [], 10);
        $nav->register('payments', 'Payments V2', 'Wallet', [], 5);

        $groups = $nav->groups();

        $this->assertCount(1, $groups);
        $this->assertSame('Payments V2', $groups[0]['title']);
        $this->assertSame('Wallet', $groups[0]['icon']);
    }

    public function test_groups_returns_empty_array_when_nothing_registered(): void
    {
        $nav = new AdminNavigation();

        $this->assertSame([], $nav->groups());
    }

    public function test_priority_field_is_not_included_in_output(): void
    {
        $nav = new AdminNavigation();
        $nav->register('payments', 'Payments', 'CreditCard', [], 10);

        $groups = $nav->groups();

        $this->assertArrayNotHasKey('priority', $groups[0]);
    }
}
