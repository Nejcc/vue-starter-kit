<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaravelPlus\Ecommerce\Database\Factories\ProductVariantFactory;
use LaravelPlus\Ecommerce\Enums\StockStatus;

final class ProductVariant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ecommerce_product_variants';

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'compare_at_price',
        'stock_quantity',
        'options',
        'weight',
        'images',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'stock_quantity' => 'integer',
            'options' => 'array',
            'weight' => 'decimal:3',
            'images' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the effective price (variant price or fall back to product price).
     */
    public function getEffectivePrice(): int
    {
        return $this->price ?? $this->product->price;
    }

    /**
     * Get the stock status for this variant.
     */
    public function getStockStatus(): StockStatus
    {
        $threshold = $this->product->low_stock_threshold;

        if ($this->stock_quantity <= 0) {
            return StockStatus::OutOfStock;
        }

        if ($this->stock_quantity <= $threshold) {
            return StockStatus::LowStock;
        }

        return StockStatus::InStock;
    }

    /**
     * Check if the variant is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Format price in dollars (from cents).
     */
    public function formattedPrice(): string
    {
        $decimals = (int) config('ecommerce.currency.decimals', 2);
        $symbol = (string) config('ecommerce.currency.symbol', '$');
        $price = $this->getEffectivePrice();

        return $symbol.number_format($price / (10 ** $decimals), $decimals);
    }

    /**
     * Get a specific option value by key.
     */
    public function getOption(string $key): mixed
    {
        return $this->options[$key] ?? null;
    }

    /**
     * Get a formatted label for this variant (e.g. "Red / Large").
     */
    public function getOptionsLabel(): string
    {
        if (empty($this->options)) {
            return $this->name;
        }

        return implode(' / ', array_values($this->options));
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    protected static function newFactory(): ProductVariantFactory
    {
        return ProductVariantFactory::new();
    }
}
