<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Enums;

enum ProductStatus: string
{
    case Active = 'active';
    case Draft = 'draft';
    case Archived = 'archived';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Draft => 'Draft',
            self::Archived => 'Archived',
        };
    }

    /**
     * Get color for UI display.
     */
    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Draft => 'yellow',
            self::Archived => 'gray',
        };
    }
}
