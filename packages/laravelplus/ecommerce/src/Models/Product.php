<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use LaravelPlus\Ecommerce\Database\Factories\ProductFactory;
use LaravelPlus\Ecommerce\Enums\ProductStatus;
use LaravelPlus\Ecommerce\Enums\StockStatus;

final class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ecommerce_products';

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'price',
        'compare_at_price',
        'cost_price',
        'currency',
        'status',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
        'is_featured',
        'is_digital',
        'has_variants',
        'weight',
        'dimensions',
        'images',
        'metadata',
        'published_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'cost_price' => 'integer',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_digital' => 'boolean',
            'has_variants' => 'boolean',
            'weight' => 'decimal:3',
            'dimensions' => 'array',
            'images' => 'array',
            'metadata' => 'array',
            'status' => ProductStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'ecommerce_product_category')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Attribute, $this>
     */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'ecommerce_product_attribute_values')
            ->withPivot('value')
            ->withTimestamps();
    }

    /**
     * @return MorphToMany<Tag, $this>
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'ecommerce_taggables');
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    /**
     * Get the stock status for this product.
     */
    public function getStockStatus(): StockStatus
    {
        if ($this->has_variants) {
            $totalStock = $this->variants()->where('is_active', true)->sum('stock_quantity');

            if ($totalStock <= 0) {
                return StockStatus::OutOfStock;
            }

            if ($totalStock <= $this->low_stock_threshold) {
                return StockStatus::LowStock;
            }

            return StockStatus::InStock;
        }

        if ($this->stock_quantity <= 0) {
            return StockStatus::OutOfStock;
        }

        if ($this->stock_quantity <= $this->low_stock_threshold) {
            return StockStatus::LowStock;
        }

        return StockStatus::InStock;
    }

    /**
     * Check if the product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->getStockStatus() !== StockStatus::OutOfStock;
    }

    /**
     * Check if the product is on sale (has compare_at_price).
     */
    public function isOnSale(): bool
    {
        return $this->compare_at_price !== null && $this->compare_at_price > $this->price;
    }

    /**
     * Check if the product is published.
     */
    public function isPublished(): bool
    {
        return $this->status === ProductStatus::Active
            && $this->is_active
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    /**
     * Format price in dollars (from cents).
     */
    public function formattedPrice(): string
    {
        $decimals = (int) config('ecommerce.currency.decimals', 2);
        $symbol = (string) config('ecommerce.currency.symbol', '$');

        return $symbol.number_format($this->price / (10 ** $decimals), $decimals);
    }

    /**
     * Format compare at price in dollars (from cents).
     */
    public function formattedCompareAtPrice(): ?string
    {
        if ($this->compare_at_price === null) {
            return null;
        }

        $decimals = (int) config('ecommerce.currency.decimals', 2);
        $symbol = (string) config('ecommerce.currency.symbol', '$');

        return $symbol.number_format($this->compare_at_price / (10 ** $decimals), $decimals);
    }

    /**
     * Get the effective price (lowest variant price or product price).
     */
    public function getEffectivePrice(): int
    {
        if ($this->has_variants) {
            $minVariantPrice = $this->variants()->where('is_active', true)->min('price');

            return $minVariantPrice !== null ? (int) $minVariantPrice : $this->price;
        }

        return $this->price;
    }

    /**
     * Get the total stock across all variants or product stock.
     */
    public function getTotalStock(): int
    {
        if ($this->has_variants) {
            return (int) $this->variants()->where('is_active', true)->sum('stock_quantity');
        }

        return $this->stock_quantity;
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
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Active)
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeWithTag(Builder $query, int $tagId): Builder
    {
        return $query->whereHas('tags', function (Builder $q) use ($tagId): void {
            $q->where('ecommerce_tags.id', $tagId);
        });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('categories', function (Builder $q) use ($categoryId): void {
            $q->where('ecommerce_categories.id', $categoryId);
        });
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (Product $product): void {
            if (empty($product->slug)) {
                $product->slug = self::generateUniqueSlug(Str::slug($product->name));
            }
        });
    }

    /**
     * Generate a unique slug, appending a counter if necessary.
     */
    private static function generateUniqueSlug(string $slug): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while (self::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
