<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case OnHold = 'on_hold';
    case Failed = 'failed';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
            self::OnHold => 'On Hold',
            self::Failed => 'Failed',
        };
    }

    /**
     * Get color for UI display.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Confirmed => 'blue',
            self::Processing => 'indigo',
            self::Shipped => 'cyan',
            self::Delivered => 'teal',
            self::Completed => 'green',
            self::Cancelled => 'gray',
            self::Refunded => 'orange',
            self::OnHold => 'amber',
            self::Failed => 'red',
        };
    }

    /**
     * Check if this is a final (terminal) status.
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled, self::Refunded, self::Failed], true);
    }

    /**
     * Check if this status can transition to the given status.
     */
    public function canTransitionTo(self $status): bool
    {
        if ($this->isFinal()) {
            return false;
        }

        $transitions = match ($this) {
            self::Pending => [self::Confirmed, self::Processing, self::Cancelled, self::OnHold, self::Failed],
            self::Confirmed => [self::Processing, self::Cancelled, self::OnHold],
            self::Processing => [self::Shipped, self::Cancelled, self::OnHold],
            self::Shipped => [self::Delivered, self::OnHold],
            self::Delivered => [self::Completed, self::Refunded],
            self::OnHold => [self::Pending, self::Processing, self::Cancelled],
            default => [],
        };

        return in_array($status, $transitions, true);
    }
}
