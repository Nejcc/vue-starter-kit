<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use LaravelPlus\Ecommerce\Database\Factories\CategoryFactory;

final class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ecommerce_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
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
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'parent_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'ecommerce_product_category')
            ->withTimestamps();
    }

    /**
     * Get all ancestors (parent chain up to root).
     *
     * @return Collection<int, self>
     */
    public function getAncestors(): Collection
    {
        $ancestors = new Collection;
        $category = $this->parent;

        while ($category) {
            $ancestors->prepend($category);
            $category = $category->parent;
        }

        return $ancestors;
    }

    /**
     * Get all descendants (recursive children).
     *
     * @return Collection<int, self>
     */
    public function getDescendants(): Collection
    {
        $descendants = new Collection;

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }

        return $descendants;
    }

    /**
     * Get breadcrumb trail including self.
     *
     * @return Collection<int, self>
     */
    public function getBreadcrumb(): Collection
    {
        $breadcrumb = $this->getAncestors();
        $breadcrumb->push($this);

        return $breadcrumb;
    }

    /**
     * Check if this category is a root (no parent).
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Check if this category is a leaf (no children).
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
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
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
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

        self::creating(function (Category $category): void {
            if (empty($category->slug)) {
                $category->slug = self::generateUniqueSlug(Str::slug($category->name));
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

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
