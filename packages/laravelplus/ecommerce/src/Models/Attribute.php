<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use LaravelPlus\Ecommerce\Database\Factories\AttributeFactory;
use LaravelPlus\Ecommerce\Enums\AttributeType;

final class Attribute extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ecommerce_attributes';

    protected $fillable = [
        'attribute_group_id',
        'name',
        'slug',
        'type',
        'sort_order',
        'is_filterable',
        'is_required',
        'is_active',
        'values',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'attribute_group_id' => 'integer',
            'sort_order' => 'integer',
            'is_filterable' => 'boolean',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'values' => 'array',
            'type' => AttributeType::class,
        ];
    }

    /**
     * @return BelongsTo<AttributeGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'ecommerce_product_attribute_values')
            ->withPivot('value')
            ->withTimestamps();
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
    public function scopeFilterable(Builder $query): Builder
    {
        return $query->where('is_filterable', true);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (Attribute $attribute): void {
            if (empty($attribute->slug)) {
                $attribute->slug = self::generateUniqueSlug(Str::slug($attribute->name));
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

    protected static function newFactory(): AttributeFactory
    {
        return AttributeFactory::new();
    }
}
