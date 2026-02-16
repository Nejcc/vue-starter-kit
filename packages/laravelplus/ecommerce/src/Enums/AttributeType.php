<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Enums;

enum AttributeType: string
{
    case Text = 'text';
    case Number = 'number';
    case Select = 'select';
    case Boolean = 'boolean';
    case Color = 'color';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Number => 'Number',
            self::Select => 'Select',
            self::Boolean => 'Boolean',
            self::Color => 'Color',
        };
    }
}
