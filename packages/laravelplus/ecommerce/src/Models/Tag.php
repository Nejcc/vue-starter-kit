<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use LaravelPlus\Ecommerce\Database\Factories\TagFactory;

final class Tag extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_tags';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'sort_order',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return MorphToMany<Product, $this>
     */
    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'taggable', 'ecommerce_taggables');
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
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (Tag $tag): void {
            if (empty($tag->slug)) {
                $tag->slug = self::generateUniqueSlug(Str::slug($tag->name));
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

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }
}
