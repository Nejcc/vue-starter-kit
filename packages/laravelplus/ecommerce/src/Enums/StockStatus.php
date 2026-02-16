<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Enums;

enum StockStatus: string
{
    case InStock = 'in_stock';
    case OutOfStock = 'out_of_stock';
    case LowStock = 'low_stock';
    case BackOrder = 'back_order';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::InStock => 'In Stock',
            self::OutOfStock => 'Out of Stock',
            self::LowStock => 'Low Stock',
            self::BackOrder => 'Back Order',
        };
    }

    /**
     * Get color for UI display.
     */
    public function color(): string
    {
        return match ($this) {
            self::InStock => 'green',
            self::OutOfStock => 'red',
            self::LowStock => 'yellow',
            self::BackOrder => 'blue',
        };
    }
}
