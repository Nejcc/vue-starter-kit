<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use LaravelPlus\Ecommerce\Database\Factories\AttributeGroupFactory;

final class AttributeGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ecommerce_attribute_groups';

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Attribute, $this>
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class, 'attribute_group_id');
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

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (AttributeGroup $group): void {
            if (empty($group->slug)) {
                $group->slug = self::generateUniqueSlug(Str::slug($group->name));
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

    protected static function newFactory(): AttributeGroupFactory
    {
        return AttributeGroupFactory::new();
    }
}
