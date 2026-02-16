<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelPlus\Ecommerce\Database\Factories\OrderItemFactory;

final class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'name',
        'sku',
        'quantity',
        'unit_price',
        'total',
        'options',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'product_id' => 'integer',
            'product_variant_id' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'total' => 'integer',
            'options' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Create an order item from a product, snapshotting data.
     *
     * @return array<string, mixed>
     */
    public static function fromProduct(Product $product, int $quantity): array
    {
        return [
            'product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'total' => $product->price * $quantity,
        ];
    }

    /**
     * Create an order item from a product variant, snapshotting data.
     *
     * @return array<string, mixed>
     */
    public static function fromVariant(ProductVariant $variant, int $quantity): array
    {
        return [
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'quantity' => $quantity,
            'unit_price' => $variant->getEffectivePrice(),
            'total' => $variant->getEffectivePrice() * $quantity,
            'options' => $variant->options,
        ];
    }

    protected static function newFactory(): OrderItemFactory
    {
        return OrderItemFactory::new();
    }
}
