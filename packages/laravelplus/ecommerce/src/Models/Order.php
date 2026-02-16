<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use LaravelPlus\Ecommerce\Database\Factories\OrderFactory;
use LaravelPlus\Ecommerce\Enums\OrderStatus;

final class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ecommerce_orders';

    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'tax',
        'discount',
        'shipping_cost',
        'total',
        'currency',
        'shipping_address',
        'billing_address',
        'notes',
        'metadata',
        'placed_at',
        'completed_at',
        'cancelled_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'subtotal' => 'integer',
            'tax' => 'integer',
            'discount' => 'integer',
            'shipping_cost' => 'integer',
            'total' => 'integer',
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'metadata' => 'array',
            'status' => OrderStatus::class,
            'placed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * @return BelongsTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'), 'user_id');
    }

    /**
     * Get transactions (from payment-gateway package if available).
     */
    public function transactions(): ?MorphMany
    {
        if (class_exists(\LaravelPlus\PaymentGateway\Models\Transaction::class)) {
            return $this->morphMany(\LaravelPlus\PaymentGateway\Models\Transaction::class, 'payable');
        }

        return null;
    }

    /**
     * Format total in dollars (from cents).
     */
    public function formattedTotal(): string
    {
        $decimals = (int) config('ecommerce.currency.decimals', 2);
        $symbol = (string) config('ecommerce.currency.symbol', '$');

        return $symbol.number_format($this->total / (10 ** $decimals), $decimals);
    }

    /**
     * Check if the order has been paid.
     */
    public function isPaid(): bool
    {
        if ($this->transactions() === null) {
            return false;
        }

        return $this->transactions()->where('status', 'completed')->exists();
    }

    /**
     * Check if the order can transition to the given status.
     */
    public function canTransitionTo(OrderStatus $status): bool
    {
        return $this->status->canTransitionTo($status);
    }

    /**
     * Generate a unique order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = (string) config('ecommerce.orders.number_prefix', 'ORD');
        $year = date('Y');

        $lastOrder = self::withTrashed()
            ->where('order_number', 'like', "{$prefix}-{$year}-%")
            ->orderByRaw('CAST(SUBSTR(order_number, -4) AS INTEGER) DESC')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $nextNumber);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeWithStatus(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Completed);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (Order $order): void {
            if (empty($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }

            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
