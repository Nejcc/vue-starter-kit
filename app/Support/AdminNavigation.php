<?php

declare(strict_types=1);

namespace App\Support;

final class AdminNavigation
{
    /**
     * @var array<string, array{title: string, icon: string, items: array<int, array{title: string, href: string, icon: string}>, priority: int}>
     */
    private array $groups = [];

    /**
     * Register a navigation group.
     *
     * @param  array<int, array{title: string, href: string, icon: string}>  $items
     */
    public function register(string $key, string $title, string $icon, array $items, int $priority = 50): void
    {
        $this->groups[$key] = [
            'title' => $title,
            'icon' => $icon,
            'items' => $items,
            'priority' => $priority,
        ];
    }

    /**
     * Get all registered groups sorted by priority.
     *
     * @return array<int, array{title: string, icon: string, items: array<int, array{title: string, href: string, icon: string}>}>
     */
    public function groups(): array
    {
        $sorted = $this->groups;
        uasort($sorted, fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);

        return array_values(array_map(
            fn (array $group): array => [
                'title' => $group['title'],
                'icon' => $group['icon'],
                'items' => $group['items'],
            ],
            $sorted,
        ));
    }
}
