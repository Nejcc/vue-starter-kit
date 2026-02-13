<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Support\AdminNavigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminNavigationSharingTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_navigation_is_shared_via_inertia(): void
    {
        $nav = app(AdminNavigation::class);
        $nav->register('test-module', 'Test Module', 'Star', [
            ['title' => 'Home', 'href' => '/admin/test', 'icon' => 'Home'],
        ], 10);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('moduleNavigation'));
    }

    public function test_module_navigation_contains_registered_groups(): void
    {
        $nav = app(AdminNavigation::class);
        $nav->register('test-module', 'Test Module', 'Star', [
            ['title' => 'Home', 'href' => '/admin/test', 'icon' => 'Home'],
        ], 99);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $groups = $nav->groups();
        $lastGroup = end($groups);

        $response->assertInertia(fn ($page) => $page->has('moduleNavigation'));

        $this->assertSame('Test Module', $lastGroup['title']);
        $this->assertSame('Star', $lastGroup['icon']);
        $this->assertCount(1, $lastGroup['items']);
        $this->assertSame('Home', $lastGroup['items'][0]['title']);
    }

    public function test_admin_navigation_is_singleton(): void
    {
        $a = app(AdminNavigation::class);
        $b = app(AdminNavigation::class);

        $this->assertSame($a, $b);
    }
}
