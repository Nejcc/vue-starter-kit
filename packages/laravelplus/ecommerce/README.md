# Ecommerce Package

Product catalog, categories, variants, and shop management for the LaravelPlus starter kit.

## Features

- Product CRUD with status management (active, draft, archived)
- Product variants with custom options (size, color, etc.)
- Category management with self-referential tree hierarchy
- Stock tracking with low-stock and out-of-stock alerts
- Prices stored in cents for precision
- Admin dashboard with stats overview
- Soft deletes on all models

## Installation

The package is already included in the starter kit via path repository. If adding manually:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/laravelplus/ecommerce"
        }
    ],
    "require": {
        "laravelplus/ecommerce": "@dev"
    }
}
```

```bash
composer require laravelplus/ecommerce:@dev
php artisan migrate
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=ecommerce-config
```

Key settings in `config/ecommerce.php`:

| Key | Default | Description |
|-----|---------|-------------|
| `currency.code` | `USD` | Default currency code |
| `currency.symbol` | `$` | Currency symbol |
| `currency.decimals` | `2` | Decimal places |
| `stock.low_stock_threshold` | `5` | Low stock alert threshold |
| `stock.track_stock` | `true` | Enable stock tracking |
| `images.disk` | `public` | Storage disk for images |
| `images.max_images` | `10` | Max images per product |
| `per_page` | `15` | Pagination page size |
| `admin.enabled` | `true` | Enable admin routes |
| `admin.prefix` | `admin/ecommerce` | Admin URL prefix |

## Admin Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/admin/ecommerce` | `admin.ecommerce.dashboard` | Dashboard |
| GET | `/admin/ecommerce/products` | `admin.ecommerce.products.index` | List products |
| GET | `/admin/ecommerce/products/create` | `admin.ecommerce.products.create` | Create product form |
| POST | `/admin/ecommerce/products` | `admin.ecommerce.products.store` | Store product |
| GET | `/admin/ecommerce/products/{product}/edit` | `admin.ecommerce.products.edit` | Edit product form |
| PUT | `/admin/ecommerce/products/{product}` | `admin.ecommerce.products.update` | Update product |
| DELETE | `/admin/ecommerce/products/{product}` | `admin.ecommerce.products.destroy` | Delete product |
| POST | `/admin/ecommerce/products/{product}/variants` | `admin.ecommerce.products.variants.store` | Add variant |
| PUT | `/admin/ecommerce/products/{product}/variants/{variant}` | `admin.ecommerce.products.variants.update` | Update variant |
| DELETE | `/admin/ecommerce/products/{product}/variants/{variant}` | `admin.ecommerce.products.variants.destroy` | Delete variant |
| POST | `/admin/ecommerce/products/{product}/variants/reorder` | `admin.ecommerce.products.variants.reorder` | Reorder variants |
| GET | `/admin/ecommerce/categories` | `admin.ecommerce.categories.index` | List categories |
| GET | `/admin/ecommerce/categories/tree` | `admin.ecommerce.categories.tree` | Category tree view |
| GET | `/admin/ecommerce/categories/create` | `admin.ecommerce.categories.create` | Create category form |
| POST | `/admin/ecommerce/categories` | `admin.ecommerce.categories.store` | Store category |
| GET | `/admin/ecommerce/categories/{category}/edit` | `admin.ecommerce.categories.edit` | Edit category form |
| PUT | `/admin/ecommerce/categories/{category}` | `admin.ecommerce.categories.update` | Update category |
| DELETE | `/admin/ecommerce/categories/{category}` | `admin.ecommerce.categories.destroy` | Delete category |
| POST | `/admin/ecommerce/categories/reorder` | `admin.ecommerce.categories.reorder` | Reorder categories |

## Architecture

### Backend (lives in the package)

```
packages/laravelplus/ecommerce/
├── config/ecommerce.php
├── database/
│   ├── factories/          # ProductFactory, CategoryFactory, ProductVariantFactory
│   └── migrations/         # 4 migration files (categories, products, pivot, variants)
├── routes/admin.php
├── src/
│   ├── EcommerceServiceProvider.php
│   ├── Contracts/          # Repository interfaces
│   ├── Enums/              # ProductStatus, StockStatus
│   ├── Facades/            # Ecommerce facade
│   ├── Http/
│   │   ├── Controllers/Admin/   # DashboardController, ProductController, CategoryController, ProductVariantController
│   │   └── Requests/            # Store/Update form requests
│   ├── Models/             # Product, Category, ProductVariant
│   ├── Repositories/       # ProductRepository, CategoryRepository, ProductVariantRepository
│   └── Services/           # ProductService, CategoryService, ProductVariantService, EcommerceService
└── tests/                  # 183 tests, 385 assertions
```

### Frontend (lives in the package)

Vue pages are located inside the package at `resources/js/pages/` and are auto-discovered by the multi-path page resolver in `app.ts`.

```
packages/laravelplus/ecommerce/
└── resources/js/pages/admin/ecommerce/
    ├── Dashboard.vue
    ├── Products.vue
    ├── Products/
    │   ├── Create.vue
    │   └── Edit.vue        # Includes variant management
    ├── Categories.vue
    └── Categories/
        ├── Create.vue
        ├── Edit.vue
        └── Tree.vue
```

The navigation composable lives in the main app at `resources/js/composables/useEcommerceNav.ts`.

### How package pages work

The starter kit uses a multi-path Inertia page resolver (`resources/js/resolvePackagePages.ts`) that automatically discovers Vue pages from all packages under `packages/laravelplus/*/resources/js/pages/`.

**Auto-discovery:** Any package that places Vue files in `resources/js/pages/` (following the same directory structure as the main app) will have its pages picked up automatically — no Vite or app config changes needed.

**Override mechanism:** To customize a package page in a specific project, create the same file path in the main app's `resources/js/pages/`. App pages always take priority over package pages.

Example:
```
# Package provides this page:
packages/laravelplus/ecommerce/resources/js/pages/admin/ecommerce/Dashboard.vue

# To override, create this file (takes priority):
resources/js/pages/admin/ecommerce/Dashboard.vue
```

## Models

### Product

- Prices stored as integers (cents): `price`, `compare_at_price`, `cost_price`
- BelongsToMany `categories` via pivot table
- HasMany `variants`
- Scopes: `active()`, `featured()`, `published()`, `inStock()`
- Slug-based routing, soft deletes
- JSON fields: `images`, `dimensions`, `metadata`

### Category

- Self-referential tree: `parent()` / `children()` relationships
- BelongsToMany `products`
- Scopes: `active()`, `root()`, `ordered()`
- Tree traversal: `ancestors()`, `descendants()`, `breadcrumb()`
- Soft deletes

### ProductVariant

- BelongsTo `product`
- JSON `options` field for arbitrary key-value pairs (e.g. `{"size": "L", "color": "Red"}`)
- Optional override pricing (falls back to product price)
- Independent stock tracking

## Testing

```bash
# Run package tests
cd packages/laravelplus/ecommerce && vendor/bin/phpunit

# Run from the main app
php artisan test --compact --filter=Ecommerce
```

## Database Tables

All tables are prefixed with `ecommerce_` to avoid conflicts:

- `ecommerce_categories`
- `ecommerce_products`
- `ecommerce_product_category` (pivot)
- `ecommerce_product_variants`
