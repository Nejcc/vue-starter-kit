# Ecommerce Development Skill

Activate when working with the `laravelplus/ecommerce` package — managing products, categories, product variants, using the Ecommerce facade, building admin pages under `admin/ecommerce`, or extending ecommerce functionality.

## Package Overview

The ecommerce package provides a product catalog with categories, product variants, and admin CRUD. All prices are stored in **cents** (integer). Tables are prefixed `ecommerce_`.

## Key Classes

- **Models**: `Product`, `Category`, `ProductVariant` — in `LaravelPlus\Ecommerce\Models`
- **Services**: `ProductService`, `CategoryService`, `ProductVariantService`, `EcommerceService`
- **Repositories**: `ProductRepository`, `CategoryRepository`, `ProductVariantRepository`
- **Enums**: `ProductStatus` (Active, Draft, Archived), `StockStatus` (InStock, OutOfStock, LowStock, BackOrder)
- **Facade**: `Ecommerce` — delegates to `EcommerceService`

## Architecture

```
Controllers → Services → Repositories → Models
```

- Repositories handle data access (queries, CRUD)
- Services handle business logic (transactions, stock management, publishing)
- Controllers handle HTTP (validation via FormRequests, Inertia responses)

## Routes

All admin routes are prefixed with `admin/ecommerce` and named `admin.ecommerce.*`:
- Products: `admin.ecommerce.products.{index,create,store,edit,update,destroy}`
- Variants: `admin.ecommerce.products.variants.{store,update,destroy,reorder}`
- Categories: `admin.ecommerce.categories.{index,tree,create,store,edit,update,destroy,reorder}`

## Product Variants

Products can have variants (size, color, etc.) when `has_variants` is `true`. Each variant has:
- Its own SKU, price (nullable — falls back to product price), stock quantity
- JSON `options` field (e.g., `{"color": "Red", "size": "L"}`)
- Sort order for display ordering

## Price Handling

All prices are in cents. Use `formattedPrice()` on models or `Ecommerce::formatPrice(2999)` → `$29.99`.

## Config

Published via `ecommerce-config` tag. Key settings:
- `ecommerce.currency.code` / `symbol` / `decimals`
- `ecommerce.stock.low_stock_threshold` / `track_stock`
- `ecommerce.admin.enabled` / `prefix` / `middleware`
