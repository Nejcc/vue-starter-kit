# CODEBASE.md

> **Generic Laravel Starter Kit** - A production-ready, reusable codebase template for building Laravel applications with Vue 3 and Inertia.js.

## Table of Contents

1. [Application Overview](#application-overview)
2. [Architecture Patterns](#architecture-patterns)
3. [Directory Structure](#directory-structure)
4. [Environment Setup](#environment-setup)
5. [Key Technologies & Versions](#key-technologies--versions)
6. [Database Schema & Migrations](#database-schema--migrations)
7. [Authentication & Authorization](#authentication--authorization)
8. [Frontend Architecture](#frontend-architecture)
9. [Common Patterns & Examples](#common-patterns--examples)
10. [Data Flow Examples](#data-flow-examples)
11. [Performance Considerations](#performance-considerations)
12. [Security Best Practices](#security-best-practices)
13. [Troubleshooting](#troubleshooting)
14. [Critical Code Standards & Conventions](#critical-code-standards--conventions)
15. [Comprehensive File Inventory](#comprehensive-file-inventory)
16. [Development Workflow](#development-workflow)
17. [Tracking Changes](#tracking-changes)
18. [Key Features Summary](#key-features-summary)
19. [Quick Reference](#quick-reference)

---

## Application Overview

This is a **generic Laravel starter kit** designed to be cloned and customized for any Laravel project. It provides a solid foundation with:

- **Laravel 12** with PHP 8.4+, PHP 8.5+ support
- **Vue 3** with Composition API and TypeScript
- **Inertia.js 2** for seamless SPA experience + prefetch
- **Tailwind CSS 4** for utility-first styling
- **shadcn-vue** component library with Reka UI
- **Laravel Fortify** for authentication (login, register, 2FA, password reset)
- **Spatie Laravel Permission** for roles and permissions
- **Repository, Service, and Action patterns** for clean architecture
- **User impersonation** for admin testing
- **Cookie consent management** with GDPR compliance
- **Database management UI** for viewing tables and data (with sensitive column masking)
- **Last login tracking** for security auditing
- **Audit logging** with polymorphic trail for user actions
- **Notification system** with mark-as-read, filtering, and pagination
- **Theme/appearance management** (light/dark mode)
- **Security headers middleware** (X-Frame-Options, CSP, HSTS, Referrer-Policy, Permissions-Policy)
- **Payment gateway integration** (Stripe, PayPal, Crypto, Bank Transfer, COD) via `nejcc/payment-gateway` package
- **Subscriber management** via `nejcc/subscribe` package
- **Global settings** via `laravelplus/global-settings` package

### Key Philosophy

- **Strict Typing**: All PHP files use `declare(strict_types=1);` and explicit return types
- **Clean Architecture**: Clear separation of concerns with Repository, Service, and Action patterns
- **Reusable Patterns**: Consistent patterns that can be applied across different projects
- **Code Quality**: Laravel Pint for PHP formatting, Prettier for frontend

### Quick Start Guide

**For New Developers**:

1. **Clone and Setup**:
   ```bash
   git clone <repository-url> your-project
   cd your-project
   composer run setup
   ```

2. **Start Development**:
   ```bash
   composer run dev
   ```
   This starts: Laravel server, queue worker, log viewer, and Vite dev server

3. **Access Application**:
   - Frontend: `http://localhost:8000`
   - Default user: Check `database/seeders/UserSeeder.php`

4. **Key Files to Understand First**:
   - `app/Repositories/AbstractRepository.php` - Repository pattern
   - `app/Services/AbstractService.php` - Service pattern
   - `app/Http/Controllers/DashboardController.php` - Simple controller example
   - `resources/js/pages/Dashboard.vue` - Simple page example

5. **Read This Documentation**:
   - Start with [Architecture Patterns](#architecture-patterns)
   - Review [Common Patterns & Examples](#common-patterns--examples)
   - Check [Development Workflow](#development-workflow) when adding features

---

## Architecture Patterns

This starter kit follows a clean architecture pattern with clear separation of concerns:

### Repository Pattern

**Location**: `app/Repositories/`

- **AbstractRepository**: Base repository with caching, CRUD operations, and query building
- **BaseRepository**: Extends AbstractRepository (if needed for additional base functionality)
- **RepositoryInterface**: Contract defining repository methods
- Repositories handle all database interactions and provide a clean abstraction layer
- Built-in caching with automatic invalidation on write operations

**Flow**: Controllers → Services → Repositories → Models

### Service Pattern

**Location**: `app/Services/`

- **AbstractService**: Base service with transaction support and validation helpers
- Services contain business logic and orchestrate repository operations
- Services can be easily tested and swapped without affecting controllers
- All operations use database transactions for data integrity

### Action Pattern

**Location**: `app/Actions/`

- Actions encapsulate single, focused business operations
- Actions are reusable and can be composed to build complex workflows
- Used for user management, authentication, and other domain operations
- Implements `ActionInterface` for consistency

### Layer Flow

```
HTTP Request
    ↓
Controller (handles HTTP, delegates to Service)
    ↓
Service (business logic, validation, transactions)
    ↓
Repository (data access, caching)
    ↓
Model (Eloquent ORM)
    ↓
Database
```

Actions can be used at any layer when a single-purpose operation is needed.

### Architecture Diagram

```mermaid
graph TB
    subgraph Frontend["Frontend (Vue 3 + Inertia)"]
        Pages[Inertia Pages]
        Components[Vue Components]
        Composables[Composables]
    end
    
    subgraph Backend["Backend (Laravel 12)"]
        Controllers[Controllers]
        Services[Services]
        Repositories[Repositories]
        Models[Models]
        Actions[Actions]
    end
    
    subgraph Data["Data Layer"]
        Database[(Database)]
        Cache[(Cache)]
    end
    
    Pages -->|HTTP Requests| Controllers
    Controllers -->|Business Logic| Services
    Services -->|Data Access| Repositories
    Repositories -->|ORM| Models
    Models -->|Queries| Database
    Repositories -->|Cache| Cache
    Services -->|Single Operations| Actions
    Actions -->|Can Use| Services
    Actions -->|Can Use| Repositories
```

### Dependency Injection Flow

```mermaid
graph LR
    ServiceProvider[Service Provider] -->|Binds| Interface[Repository Interface]
    Interface -->|Resolves To| Implementation[Repository Implementation]
    Service -->|Injects| Interface
    Controller -->|Injects| Service
    Action -->|Injects| Service
```

**Example**:
- `RepositoryServiceProvider` binds `UserRepositoryInterface` → `UserRepository`
- `UserService` constructor receives `UserRepositoryInterface` (auto-resolved)
- `Controller` receives `UserService` (auto-resolved)

---

## Directory Structure

### Backend Structure

```
app/
├── Actions/              # Single-purpose business operations
│   ├── Fortify/         # Authentication actions
│   └── User/            # User management actions
├── Constants/           # Application constants (e.g., RoleNames)
├── Contracts/           # Interfaces (Repository, Service, Action)
│   ├── Actions/
│   ├── Repositories/
│   └── Services/
├── Facades/             # Application-specific facades
├── Http/
│   ├── Controllers/    # HTTP controllers
│   │   ├── Admin/      # Admin panel controllers
│   │   └── Settings/   # User settings controllers
│   ├── Middleware/     # Custom middleware
│   └── Requests/        # Form request validation
│       ├── Admin/
│       └── Settings/
├── Listeners/           # Event listeners
├── Models/              # Eloquent models (User, AuditLog, Role, Permission)
├── Providers/           # Service providers
├── Repositories/        # Data access layer
├── Services/            # Business logic layer (User, Notification, Role, Permission, Impersonation)
├── SettingRole.php     # Setting role enum
└── Traits/              # Reusable traits
```

### Frontend Structure

```
resources/js/
├── actions/             # Wayfinder-generated route actions
├── components/          # Reusable Vue components (shadcn-vue)
├── composables/         # Vue composition functions
├── layouts/             # Inertia layout components
│   ├── admin/
│   ├── app/
│   ├── auth/
│   └── settings/
├── pages/               # Inertia page components
│   ├── admin/          # Admin panel (users, roles, permissions, settings, database, payments, subscribers, audit logs)
│   ├── auth/           # Authentication pages
│   ├── notifications/  # Notification center
│   └── settings/       # User settings
├── routes/              # Wayfinder-generated routes
├── types/               # TypeScript type definitions
├── app.ts              # Main application entry point
└── ssr.ts              # SSR entry point
```

### Test Structure

```
tests/
├── Feature/             # Feature tests (HTTP endpoints)
└── Unit/                # Unit tests (individual classes)
    ├── Actions/
    ├── Repositories/
    └── Services/
```

---

## Environment Setup

### Prerequisites

- **PHP 8.4+** or **PHP 8.5+** with extensions:
  - BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML
- **Composer** (latest version)
- **Node.js 18+** and npm
- **SQLite** (default) or MySQL/PostgreSQL
- **Git**

### Initial Setup

1. **Clone Repository**:
   ```bash
   git clone <repository-url> your-project-name
   cd your-project-name
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Environment Configuration**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**:
   ```bash
   # For SQLite (default)
   touch database/database.sqlite

   # For MySQL/PostgreSQL, update .env:
   # DB_CONNECTION=mysql
   # DB_HOST=127.0.0.1
   # DB_PORT=3306
   # DB_DATABASE=your_database
   # DB_USERNAME=your_username
   # DB_PASSWORD=your_password
   ```

5. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

6. **Build Frontend Assets**:
   ```bash
   npm run build
   ```

### Development Environment

**Start All Services**:
```bash
composer run dev
```

This command runs concurrently:
- Laravel development server (`php artisan serve`)
- Queue worker (`php artisan queue:listen`)
- Log viewer (`php artisan pail`)
- Vite dev server (`npm run dev`)

**Access Points**:
- Application: `http://localhost:8000`
- Logs: View in terminal (Pail)
- Hot Reload: Automatic with Vite

### Production Environment

1. **Optimize**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   composer install --optimize-autoloader --no-dev
   npm run build
   ```

2. **Environment Variables**:
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Configure production database
   - Set up queue workers
   - Configure cache driver (Redis recommended)

### Environment Variables Reference

**Key Variables**:
```env
APP_NAME="Your App Name"
APP_ENV=local
APP_DEBUG=true                     # Must be false in production
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

MAIL_MAILER=log
QUEUE_CONNECTION=sync

CACHE_DRIVER=file
SESSION_DRIVER=file

# Security
SECURITY_HEADERS_ENABLED=true      # HTTP security headers
SECURITY_HSTS_ENABLED=false        # Enable in production with HTTPS
SECURITY_DEV_ROUTES_ENABLED=false  # Quick-login/register dev routes
SESSION_ENCRYPT=false              # Enable in production
```

---

## Key Technologies & Versions

### Backend

- **Laravel 12** - PHP framework
- **PHP 8.4+** - Programming language (strict types required)
- **Laravel Fortify** - Headless authentication backend
- **Spatie Laravel Permission** - Roles and permissions management (core dependency)
- **Laravel Wayfinder** - Type-safe route generation
- **Inertia.js Laravel** - Server-side adapter

### Frontend

- **Vue 3** - Progressive JavaScript framework
- **TypeScript** - Type-safe JavaScript
- **Inertia.js Vue3** - Client-side adapter
- **Tailwind CSS 4** - Utility-first CSS framework
- **shadcn-vue** - Component library
- **Reka UI** - Headless UI components
- **Lucide Vue** - Icon library
- **VueUse** - Vue composition utilities

### Local Packages

- **`nejcc/payment-gateway`** (`packages/nejcc/payment-gateway`) - Multi-driver payment/billing (Stripe, PayPal, Crypto, Bank Transfer, COD), provides `Billable` trait
- **`nejcc/subscribe`** (`packages/nejcc/subscribe`) - Subscriber/mailing list management
- **`laravelplus/global-settings`** (`packages/laravelplus/global-settings`) - Dynamic key-value application settings with role-based access
- **`laravelplus/installer`** (`packages/laravelplus/installer`) - Application installer/setup wizard

### Development Tools

- **Vite** - Build tool and dev server
- **ESLint** - Code linting
- **Prettier** - Code formatting
- **TypeScript ESLint** - TypeScript linting
- **Laravel Pint** - PHP code style fixer (required)
- **Laravel Pail** - Real-time log viewer
- **Laravel Horizon** - Queue monitoring dashboard
- **Laravel Debugbar** - Development debugging toolbar
- **PHPUnit** - Testing framework
- **Nightwatch.js** - E2E browser testing

---

## Database Schema & Migrations

### Database Tables

#### Users Table (`users`)

**Columns**:
- `id` - Primary key
- `name` - User's full name
- `email` - Email address (unique)
- `email_verified_at` - Email verification timestamp
- `password` - Hashed password
- `remember_token` - "Remember me" token
- `two_factor_secret` - 2FA secret key (encrypted)
- `two_factor_recovery_codes` - 2FA recovery codes (encrypted)
- `two_factor_confirmed_at` - 2FA activation timestamp
- `cookie_consent_preferences` - JSON column for consent categories
- `cookie_consent_given_at` - Cookie consent timestamp
- `data_processing_consent` - Boolean for data processing consent
- `data_processing_consent_given_at` - Data processing consent timestamp
- `gdpr_ip_address` - IP address for GDPR compliance (audit trail)
- `last_login_at` - Last login timestamp
- `created_at` - Record creation timestamp
- `updated_at` - Record update timestamp

#### Settings Table (`settings`)

**Columns**:
- `id` - Primary key
- `key` - Setting key (unique)
- `value` - Setting value
- `field_type` - Type of field (input, checkbox, multioptions)
- `options` - JSON column for multi-select options
- `label` - Display label for UI
- `description` - Setting description
- `role` - Setting role (system, user, plugin)
- `created_at` - Record creation timestamp
- `updated_at` - Record update timestamp

#### Spatie Permission Tables

**`roles`**:
- `id` - Primary key
- `name` - Role name (unique)
- `guard_name` - Guard name (default: web)
- `created_at`, `updated_at` - Timestamps

**`permissions`**:
- `id` - Primary key
- `name` - Permission name (unique)
- `guard_name` - Guard name (default: web)
- `group_name` - Group for organizing permissions (e.g., "users", "roles")
- `created_at`, `updated_at` - Timestamps

**`role_has_permissions`** (pivot):
- `permission_id` - Foreign key to permissions
- `role_id` - Foreign key to roles

**`model_has_roles`** (polymorphic pivot):
- `role_id` - Foreign key to roles
- `model_type` - Model class name
- `model_id` - Model ID

**`model_has_permissions`** (polymorphic pivot):
- `permission_id` - Foreign key to permissions
- `model_type` - Model class name
- `model_id` - Model ID

#### Laravel Core Tables

**`password_reset_tokens`**:
- `email` - Email address (primary key)
- `token` - Reset token
- `created_at` - Token creation timestamp

**`sessions`**:
- `id` - Session ID (primary key)
- `user_id` - Foreign key to users (nullable)
- `ip_address` - Client IP
- `user_agent` - Client user agent
- `payload` - Session data
- `last_activity` - Last activity timestamp

**`cache`**:
- `key` - Cache key (primary key)
- `value` - Cached value
- `expiration` - Expiration timestamp

**`jobs`**:
- Queue jobs table for background processing

### Migrations (11 total)

1. **`create_users_table.php`** - Basic user schema
2. **`create_cache_table.php`** - Cache storage
3. **`create_jobs_table.php`** - Queue jobs
4. **`add_two_factor_columns_to_users_table.php`** - 2FA support
5. **`add_cookie_consent_columns_to_users_table.php`** - Cookie consent & GDPR
6. **`add_last_login_at_to_users_table.php`** - Login tracking
7. **`create_permission_tables.php`** - Spatie Permission tables
8. **`add_group_name_to_permissions_table.php`** - Permission grouping
9. **`create_settings_table.php`** - Application settings
10. **`add_role_to_settings_table.php`** - Setting roles (system/user/plugin)
11. **`create_audit_logs_table.php`** - Polymorphic audit trail

### Database Seeders (5 seeders)

**`DatabaseSeeder.php`**:
- Main seeder that orchestrates all other seeders
- Calls seeders in correct order to maintain referential integrity

**`PermissionSeeder.php`**:
- Creates base permissions for the application
- Permissions created:
  - `view users` - View user list
  - `create users` - Create new users
  - `edit users` - Modify existing users
  - `delete users` - Delete users
  - `impersonate` - Impersonate other users

**`RoleSeeder.php`**:
- Creates three system roles:
  - `super-admin` - Full access (automatically has all permissions via Gate::before)
  - `admin` - Administrative role (assigned specific permissions)
  - `user` - Standard user role (limited permissions)
- Assigns appropriate permissions to each role

**`SettingsSeeder.php`**:
- Creates default application settings
- Example settings (customize as needed):
  - Site configuration
  - Feature flags
  - System preferences

**`UserSeeder.php`**:
- Creates default users for testing and development
- Creates users with different roles for testing
- Default users can be customized per project needs

### Default Roles & Permissions

**Roles**:
- `super-admin`: Has ALL permissions automatically (via `Gate::before()` in AuthServiceProvider)
- `admin`: Administrative access with specific permissions
- `user`: Standard user with limited access

**Permissions** (grouped by resource):
- **Users Group**:
  - `view users`
  - `create users`
  - `edit users`
  - `delete users`
  - `impersonate`

**Note**: More permissions and groups can be added via the admin panel or additional seeders.

---

## Authentication & Authorization

### Laravel Fortify

Fortify provides headless authentication with the following features:

- User registration (can be enabled/disabled via settings)
- Login/logout
- Password reset via email
- Email verification
- Two-factor authentication (2FA) with QR codes and recovery codes
- Password confirmation

**Configuration**: `app/Providers/FortifyServiceProvider.php`

### Spatie Laravel Permission

**Core dependency** for roles and permissions management:

- **Role-based access control (RBAC)**: Users have roles (super-admin, admin, user)
- **Permission-based authorization**: Fine-grained permissions
- **Default Roles**:
  - `super-admin`: Has all permissions automatically (via Gate::before)
  - `admin`: Administrative role
  - `user`: Standard user role

**Configuration**: `app/Providers/AuthServiceProvider.php`

The super-admin role is special - it automatically has all permissions via `Gate::before()` and should not be deleted or renamed.

### Cookie Consent & GDPR

The application includes comprehensive GDPR-compliant cookie consent management:

**Features**:
- Cookie consent banner for both guests and authenticated users
- Category-based consent (essential, analytics, marketing, preferences)
- Accept all / Reject all functionality
- Granular category preferences
- Data processing consent tracking
- IP address logging for audit trail
- Timestamp tracking for consent given
- Cookie consent preferences stored in both database and session
- Privacy policy and cookie policy pages

**Implementation**:
- `EnsureCookieConsent` middleware enforces consent
- User model tracks: `cookie_consent_preferences` (JSON), `cookie_consent_given_at`, `gdpr_ip_address`
- `CookieConsentController` handles preference updates
- Methods on User model: `hasCookieConsent()`, `hasDataProcessingConsent()`, `hasCookieConsentForCategory()`, `updateCookieConsent()`, `updateDataProcessingConsent()`

### User Impersonation

Allows administrators to impersonate other users for testing and support purposes:

**Features**:
- Super-admin and admin roles can impersonate users
- Cannot impersonate yourself
- Active impersonation banner displayed prominently
- Session-based impersonation tracking
- Easy "stop impersonating" functionality
- Protected by role middleware

**Implementation**:
- `ImpersonateController` handles impersonation logic
- `HandleInertiaRequests` middleware shares impersonation status
- Frontend components: `ImpersonateButton`, `ImpersonateModal`, `ImpersonationBanner`
- Routes: `/impersonate` (index), `/impersonate` (store), `/impersonate` (destroy)

### Database Management

Admin database browser for inspecting database structure and data:

**Supported Databases**:
- SQLite
- MySQL
- MariaDB
- PostgreSQL
- SQL Server

**Features**:
- List all database connections configured in the application
- Browse tables and views for each connection
- View table structure (columns, data types, nullability, defaults)
- Browse table data with pagination
- View indexes and foreign key constraints
- Row count for each table
- Support for multiple database connections

**Implementation**:
- `Admin\DatabaseController` handles all database operations using direct DB queries and schema introspection
- Sensitive columns masked via `config('security.database_browser.masked_columns')`
- Audit logging of database views via `AuditLog::log()`
- Frontend pages: `Database/Index.vue`, `Database/Show.vue` with sub-views (Structure, Data, Indexes, Actions)
- Routes under `/admin/database` prefix

### Last Login Tracking

Tracks when users last logged in for security auditing:

**Features**:
- Automatically records login timestamp
- Does not update `updated_at` timestamp
- Stored in `last_login_at` column
- `hasLoggedIn()` helper method

**Implementation**:
- `TracksLastLogin` trait applied to User model
- `recordLastLogin()` method called during authentication
- Listener registered for `Login` event in Fortify

### Development Features

#### Quick Login & Register (Local Development Only)

Provides quick authentication during local development without entering credentials:

**Features**:
- Available only when `APP_ENV=local` AND `SECURITY_DEV_ROUTES_ENABLED=true`
- Route: `POST /quick-login/{userId}` - Login as any user by ID
- Route: `POST /quick-register/{role}` - Create and login as user with role (super-admin, admin, user)
- Bypasses password and 2FA requirements

**Security**:
- Requires both `SECURITY_DEV_ROUTES_ENABLED=true` config and `local` environment
- IP-restricted to allowed IPs (default: `127.0.0.1,::1`, configurable via `SECURITY_DEV_ROUTES_IPS`)
- Disabled by default — must be explicitly enabled in `.env`

---

## Frontend Architecture

### Inertia.js Pages

**Location**: `resources/js/pages/`

Pages are organized by feature:
- `auth/` - Authentication pages (login, register, password reset, etc.)
- `admin/` - Admin panel pages
- `settings/` - User settings pages
- Root level - Public pages (Welcome, Dashboard, etc.)

### Vue Components

**Location**: `resources/js/components/`

Reusable Vue components built with shadcn-vue and Reka UI. Components follow the shadcn-vue pattern.

#### Custom Application Components

**App Shell & Layout**:
- `AppShell.vue` - Main application shell wrapper
- `AppHeader.vue` - Application header with navigation and user menu
- `AppSidebar.vue` - Collapsible sidebar navigation
- `AppContent.vue` - Main content area wrapper
- `AppLogo.vue` / `AppLogoIcon.vue` - Application branding components
- `AppSidebarHeader.vue` - Sidebar header component
- `AdminSidebar.vue` - Admin panel specific sidebar

**Navigation**:
- `NavMain.vue` - Primary navigation menu
- `NavFooter.vue` - Footer navigation links
- `NavUser.vue` - User-specific navigation menu
- `Breadcrumbs.vue` - Breadcrumb trail navigation
- `TextLink.vue` - Styled text links

**User Components**:
- `UserCard.vue` - User profile card display
- `UserInfo.vue` - User information display
- `UserMenuContent.vue` - User dropdown menu content

**Authentication & Security**:
- `TwoFactorRecoveryCodes.vue` - Display 2FA recovery codes
- `TwoFactorSetupModal.vue` - 2FA setup wizard with QR code
- `DeleteUser.vue` - User account deletion confirmation

**Impersonation**:
- `ImpersonateButton.vue` - Button to trigger impersonation
- `ImpersonateModal.vue` - Modal for selecting user to impersonate
- `ImpersonationBanner.vue` - Banner displayed during active impersonation

**Cookie Consent**:
- `CookieConsentBanner.vue` - GDPR cookie consent banner
- `AppearanceTabs.vue` - Theme selection tabs (light/dark/system)

**UI Utilities**:
- `AlertError.vue` - Error alert component
- `Icon.vue` - Icon wrapper component
- `Heading.vue` / `HeadingSmall.vue` - Typography components
- `InputError.vue` - Form error message display
- `PlaceholderPattern.vue` - Empty state placeholder

**Public Pages**:
- `PublicHeader.vue` - Public-facing header
- `PublicFooter.vue` - Public-facing footer

#### shadcn-vue UI Components (136+ components)

Pre-built, accessible UI components from shadcn-vue library:

**Layout & Structure**:
- Card (Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter, CardAction)
- Sheet (drawer/mobile menu)
- Separator
- Collapsible

**Navigation**:
- Breadcrumb (full suite with ellipsis, separators)
- Navigation Menu (multi-level navigation)
- Dropdown Menu (context menus with shortcuts)
- Tabs

**Forms & Input**:
- Button
- Input
- Label
- Checkbox
- Input OTP (one-time password input)
- Form components

**Feedback**:
- Alert (Alert, AlertTitle, AlertDescription)
- Badge
- Avatar (Avatar, AvatarImage, AvatarFallback)

**Overlays**:
- Dialog (modals with scroll support)
- Sheet (side drawer)

**Data Display**:
- Table components (full table suite)

All components are customizable with Tailwind CSS and support dark mode.

### Layouts

**Location**: `resources/js/layouts/`

- `AppLayout.vue` - Main application layout
- `PublicLayout.vue` - Public-facing pages layout
- `AuthLayout.vue` - Authentication pages layout
- `admin/AdminLayout.vue` - Admin panel layout
- `app/AppHeaderLayout.vue` - App header layout
- `app/AppSidebarLayout.vue` - App sidebar layout
- `auth/` - Various auth page layouts
- `settings/Layout.vue` - Settings pages layout

### Composables

**Location**: `resources/js/composables/`

Reusable Vue composition functions:
- `useAppearance.ts` - Theme/appearance management
- `useCookieConsent.ts` - Cookie consent handling
- `useInitials.ts` - User initials generation
- `useTwoFactorAuth.ts` - 2FA functionality

### TypeScript Types

**Location**: `resources/js/types/`

Type definitions for pages, models, and forms to ensure type safety across the frontend.

### Wayfinder

Laravel Wayfinder generates TypeScript functions and types for Laravel routes, providing type-safe route generation on the frontend.

#### Wayfinder Usage Examples

**Importing Routes**:
```typescript
// Import controller methods (tree-shakable)
import { show, store, update } from '@/actions/App/Http/Controllers/PostController'

// Import named routes
import { show as postShow } from '@/routes/post'
```

**Using Routes**:
```typescript
// Get route object with URL and method
show(1) // { url: "/posts/1", method: "get" }

// Get just the URL
show.url(1) // "/posts/1"

// Use specific HTTP methods
show.get(1) // { url: "/posts/1", method: "get" }
show.head(1) // { url: "/posts/1", method: "head" }

// With query parameters
show(1, { query: { page: 1 } }) // "/posts/1?page=1"

// Merge with current query
show(1, { mergeQuery: { page: 2, sort: null } })
```

**With Inertia Form Component**:
```vue
<template>
  <Form v-bind="store.form()">
    <input name="title" />
    <button type="submit">Create</button>
  </Form>
</template>

<script setup lang="ts">
import { Form } from '@inertiajs/vue3'
import { store } from '@/actions/App/Http/Controllers/PostController'
</script>
```

**Generate Wayfinder Types**:
```bash
php artisan wayfinder:generate
```

---

## Common Patterns & Examples

### Repository Pattern Example

**Creating a New Repository**:

```php
<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\PostRepositoryInterface;
use App\Models\Post;

final class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Post::class);
    }

    public function findBySlug(string $slug): ?Post
    {
        return $this->query()
            ->where('slug', $slug)
            ->first();
    }

    public function getPublished(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->query()
            ->where('published', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
```

**Register in Service Provider**:
```php
// app/Providers/RepositoryServiceProvider.php
public array $bindings = [
    PostRepositoryInterface::class => PostRepository::class,
    // ... other bindings
];
```

### Service Pattern Example

**Creating a New Service**:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\PostRepositoryInterface;
use App\Contracts\Services\PostServiceInterface;
use App\Models\Post;

final class PostService extends AbstractService implements PostServiceInterface
{
    public function __construct(PostRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Post
    {
        $validated = $this->validate($data, [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'slug' => ['required', 'string', 'unique:posts,slug'],
        ]);

        return $this->transaction(fn () => $this->getRepository()->create($validated));
    }

    public function publish(int $postId): bool
    {
        return $this->transaction(function () use ($postId) {
            $post = $this->getRepository()->findOrFail($postId);
            return $this->getRepository()->update($post->id, [
                'published' => true,
                'published_at' => now(),
            ]);
        });
    }
}
```

### Action Pattern Example

**Creating a New Action**:

```php
<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Contracts\Actions\ActionInterface;
use App\Contracts\Services\PostServiceInterface;
use App\Models\Post;

final class PublishPostAction implements ActionInterface
{
    public function __construct(
        private readonly PostServiceInterface $postService
    ) {}

    public function handle(mixed ...$parameters): bool
    {
        $postId = $parameters[0] ?? null;

        if (!is_int($postId)) {
            throw new \InvalidArgumentException('Post ID must be an integer');
        }

        return $this->postService->publish($postId);
    }
}
```

### Form Request Example

**Creating a Form Request**:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\AbstractFormRequest;

final class StorePostRequest extends AbstractFormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'slug' => ['required', 'string', 'unique:posts,slug'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The post title is required.',
            'content.required' => 'The post content is required.',
        ];
    }
}
```

### Controller Example

**Creating a Controller**:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\PostServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class PostsController extends Controller
{
    public function __construct(
        private readonly PostServiceInterface $postService
    ) {}

    public function index(): Response
    {
        $posts = $this->postService->getAll();

        return Inertia::render('admin/Posts/Index', [
            'posts' => $posts,
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = $this->postService->create($request->validated());

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }
}
```

### Frontend Page Example

**Creating an Inertia Page**:

```vue
<template>
  <AppLayout title="Posts">
    <div>
      <h1>Posts</h1>
      <Form v-bind="store.form()" @submit="submit">
        <input v-model="form.title" name="title" />
        <textarea v-model="form.content" name="content" />
        <button type="submit" :disabled="form.processing">
          Create Post
        </button>
      </Form>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Form } from '@inertiajs/vue3'
import { store } from '@/actions/App/Http/Controllers/Admin/PostsController'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps<{
  posts: Array<{
    id: number
    title: string
    content: string
  }>
}>()

const form = store.form()

const submit = () => {
  form.post(store.url())
}
</script>
```

### Testing Example

**Feature Test**:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('admin.posts.store'), [
                'title' => 'Test Post',
                'content' => 'Test content',
                'slug' => 'test-post',
            ]);

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'user_id' => $user->id,
        ]);
    }
}
```

**Unit Test**:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Repositories\PostRepository;
use App\Services\PostService;
use App\Models\Post;
use Tests\TestCase;
use Mockery;

final class PostServiceTest extends TestCase
{
    public function test_create_post(): void
    {
        $repository = Mockery::mock(PostRepository::class);
        $repository->shouldReceive('create')
            ->once()
            ->andReturn(new Post(['title' => 'Test']));

        $service = new PostService($repository);
        $post = $service->create(['title' => 'Test']);

        $this->assertInstanceOf(Post::class, $post);
    }
}
```

---

## Data Flow Examples

### Example: Creating a User

1. **Frontend** (`resources/js/pages/admin/Users/Create.vue`)
   - User fills out form
   - Submits via Inertia form

2. **Route** (`routes/web.php`)
   - Routes to `Admin\UsersController@store`

3. **Controller** (`app/Http/Controllers/Admin/UsersController.php`)
   - Receives request
   - Validates via Form Request (`StoreUserRequest`)
   - Calls `UserService::create()`

4. **Service** (`app/Services/UserService.php`)
   - Validates data
   - Starts database transaction
   - Calls `UserRepository::createUser()`

5. **Repository** (`app/Repositories/UserRepository.php`)
   - Extends `BaseRepository`
   - Calls `AbstractRepository::create()`
   - Clears cache automatically

6. **Model** (`app/Models/User.php`)
   - Eloquent creates record
   - Returns User instance

7. **Response**
   - Service returns User
   - Controller returns Inertia response
   - Frontend receives updated data

### Example: Authentication Flow

1. User submits login form
2. Fortify handles authentication (`app/Providers/FortifyServiceProvider.php`)
3. On success, user is authenticated
4. `HandleInertiaRequests` middleware shares user data with frontend
5. Frontend receives user, roles, and permissions in shared props

### Data Flow Diagram

```mermaid
sequenceDiagram
    participant User
    participant Frontend
    participant Controller
    participant Service
    participant Repository
    participant Model
    participant Database

    User->>Frontend: Submit Form
    Frontend->>Controller: HTTP Request (Inertia)
    Controller->>Service: Call Service Method
    Service->>Service: Validate Data
    Service->>Service: Start Transaction
    Service->>Repository: Call Repository Method
    Repository->>Repository: Check Cache
    alt Cache Hit
        Repository-->>Service: Return Cached Data
    else Cache Miss
        Repository->>Model: Query Model
        Model->>Database: Execute Query
        Database-->>Model: Return Data
        Model-->>Repository: Return Model
        Repository->>Repository: Cache Result
        Repository-->>Service: Return Data
    end
    Service->>Service: Commit Transaction
    Service-->>Controller: Return Result
    Controller-->>Frontend: Inertia Response
    Frontend-->>User: Update UI
```

---

## Critical Code Standards & Conventions

### PHP 8.4+ Strict Types

**REQUIRED**: All PHP files MUST declare strict types at the top:

```php
<?php

declare(strict_types=1);

namespace App\Example;

// ... rest of file
```

### Explicit Return Types

**REQUIRED**: ALL methods and functions MUST have explicit return type declarations:

```php
// ✅ Good
public function findById(int $id): ?User
{
    // ...
}

// ❌ Bad
public function findById($id)
{
    // ...
}
```

### Constructor Property Promotion

Use PHP 8 constructor property promotion where applicable:

```php
// ✅ Good
public function __construct(
    private readonly UserServiceInterface $userService
) {}

// ❌ Bad
private UserServiceInterface $userService;

public function __construct(UserServiceInterface $userService)
{
    $this->userService = $userService;
}
```

### Laravel Pint

**REQUIRED**: Code MUST be formatted with Pint before committing:

```bash
# Format only changed files
vendor/bin/pint --dirty

# Format all files
vendor/bin/pint
```

### Repository Pattern

- **Always** use repositories for data access
- **Never** use direct model queries in controllers
- Controllers should call Services, not Repositories directly
- Services use Repositories for data access

### Service Pattern

- Business logic goes in Services, not Controllers
- Services orchestrate Repository operations
- Services handle validation and transactions

### Form Request Validation

- **Always** use Form Request classes for validation
- **Never** use inline validation in controllers
- Form Requests extend `AbstractFormRequest`

### Testing Requirements

- All changes must have corresponding tests
- Feature tests for HTTP endpoints
- Unit tests for individual classes
- Run tests before committing: `php artisan test`

### Code Formatting

- **PHP**: Laravel Pint (`vendor/bin/pint --dirty`)
- **Frontend**: Prettier (`npm run format`)

---

## Comprehensive File Inventory

This section documents every important file in the codebase, organized by category. Use this as a reference when implementing new features.

> **Note on Dates**: To track when files were created or modified, use `git log --follow <file>` or check file modification dates. When adding new features, update this documentation.

### 9.1 Core Architecture Files

#### Abstract Classes & Interfaces

**`app/Contracts/RepositoryInterface.php`**
- Base repository interface defining CRUD operations
- All repositories must implement this interface
- Provides type-safe contract for data access layer

**`app/Repositories/AbstractRepository.php`**
- Base repository implementation with caching
- Provides: `find()`, `findOrFail()`, `create()`, `update()`, `delete()`, `all()`, `paginate()`
- Automatic cache invalidation on write operations
- Cache TTL: 3600 seconds (1 hour) by default

**`app/Repositories/BaseRepository.php`**
- Extends AbstractRepository (if additional base functionality is needed)
- Check this file for any base repository extensions

**`app/Contracts/Actions/ActionInterface.php`**
- Base action contract for single-purpose operations
- All actions must implement `handle()` method

**`app/Services/AbstractService.php`**
- Base service implementation
- Provides: `transaction()`, `validate()`, `getRepository()`
- All services extend this class

**`app/Http/Requests/AbstractFormRequest.php`**
- Base form request class
- Provides common validation structure
- All form requests extend this class

#### Service Providers

**`app/Providers/AppServiceProvider.php`**
- Main application service provider
- Register application services here

**`app/Providers/AuthServiceProvider.php`**
- Authentication and authorization configuration
- **Critical**: Grants super-admin role all permissions via `Gate::before()`
- Policy mappings

**`app/Providers/FortifyServiceProvider.php`**
- Laravel Fortify configuration
- Configures authentication views (Inertia pages)
- Configures authentication actions
- Configures rate limiting
- Checks registration enabled setting

**`app/Providers/RepositoryServiceProvider.php`**
- Repository interface bindings
- Registers repository implementations in service container
- Add new repository bindings here

#### Bootstrap Files

**`bootstrap/app.php`**
- Laravel 12 application bootstrap
- Registers middleware, exceptions, and routing
- No `app/Http/Kernel.php` in Laravel 12

**`bootstrap/providers.php`**
- Application-specific service providers registration

### 9.2 Repository Layer Files

**`app/Contracts/Repositories/UserRepositoryInterface.php`**
- User repository interface
- Extends `RepositoryInterface`
- Defines user-specific methods: `findByEmail()`, `findById()`, `createUser()`, `updateUser()`, `deleteUser()`, `updatePassword()`, `search()`, `getAllForImpersonation()`

**`app/Repositories/UserRepository.php`**
- User repository implementation
- Extends `BaseRepository`
- Implements `UserRepositoryInterface`
- Provides user-specific data access methods

**`app/Contracts/Repositories/SettingsRepositoryInterface.php`**
- Settings repository interface
- Defines methods: `get()`, `set()`, `has()`

**`app/Repositories/SettingRepository.php`**
- Settings repository implementation
- Extends `BaseRepository`
- Implements `SettingsRepositoryInterface`
- Handles JSON encoding/decoding for complex values
- Converts booleans to strings for database storage

### 9.3 Service Layer Files

**`app/Contracts/Services/UserServiceInterface.php`**
- User service interface
- Defines business logic methods: `create()`, `updateProfile()`, `updatePassword()`, `delete()`, `findById()`, `findByEmail()`

**`app/Services/UserService.php`**
- User service implementation
- Extends `AbstractService`
- Implements `UserServiceInterface`
- Handles user creation, profile updates, password changes, account deletion
- Uses transactions for data integrity
- Validates data before operations

**`app/Services/ImpersonationService.php`**
- Impersonation service implementation
- Handles starting/stopping user impersonation sessions
- Session-based impersonation state management

**`app/Services/NotificationService.php`**
- Notification service implementation
- Handles notification retrieval, pagination, mark-as-read, and filtering

**`app/Services/RoleService.php`**
- Role service implementation
- CRUD operations for Spatie Permission roles
- Permission assignment to roles

**`app/Services/PermissionService.php`**
- Permission service implementation
- CRUD operations for Spatie Permission permissions
- Permission grouping management

### 9.4 Action Classes

#### Fortify Actions

**`app/Actions/Fortify/CreateNewUser.php`**
- Handles user registration via Fortify
- Creates new user account
- Used by Fortify during registration

**`app/Actions/Fortify/PasswordValidationRules.php`**
- Defines password validation rules
- Used across the application for consistent password requirements

**`app/Actions/Fortify/ResetUserPassword.php`**
- Handles password reset via Fortify
- Updates user password during password reset flow

#### User Actions

**`app/Actions/User/CreateUserAction.php`**
- Action for creating a user
- Uses `UserService` to create user
- Implements `ActionInterface`

**`app/Actions/User/DeleteUserAction.php`**
- Action for deleting a user
- Uses `UserService` to delete user

**`app/Actions/User/UpdateUserPasswordAction.php`**
- Action for updating user password
- Uses `UserService` to update password

**`app/Actions/User/UpdateUserProfileAction.php`**
- Action for updating user profile
- Uses `UserService` to update profile

### 9.5 Controllers

#### Base Controller

**`app/Http/Controllers/Controller.php`**
- Base controller class
- All controllers extend this

#### Public Controllers

**`app/Http/Controllers/DashboardController.php`**
- Displays authenticated user's dashboard
- Requires authentication and email verification
- Renders `Dashboard` Inertia page

**`app/Http/Controllers/AboutController.php`**
- Handles about page
- Renders `About` Inertia page

**`app/Http/Controllers/CookieConsentController.php`**
- Handles cookie consent preferences for GDPR compliance
- Methods:
  - `getPreferences()` - Retrieve current consent preferences
  - `updatePreferences()` - Update category-based consent
  - `acceptAll()` - Accept all cookie categories
  - `rejectAll()` - Reject all non-essential cookies
- Logs IP address and timestamp for audit trail
- Supports both authenticated users and guests

**`app/Http/Controllers/ImpersonateController.php`**
- Handles user impersonation for admin testing and support
- Methods:
  - `index()` - List all users available for impersonation
  - `store($userId)` - Start impersonating a user
  - `destroy()` - Stop current impersonation session
- Protected by 'role:super-admin,admin' middleware
- Prevents self-impersonation
- Uses `UserService` for user lookup
- Stores impersonation state in session

#### Admin Controllers

**`app/Http/Controllers/Admin/AdminController.php`**
- Admin panel dashboard/home

**`app/Http/Controllers/Admin/UsersController.php`**
- User management in admin panel
- Methods: `index()`, `create()`, `store()`

**`app/Http/Controllers/Admin/RolesController.php`**
- Role management in admin panel
- Methods: `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`

**`app/Http/Controllers/Admin/PermissionsController.php`**
- Permission management in admin panel
- Methods: `index()`, `create()`, `store()`, `edit()`, `update()`

**`app/Http/Controllers/Admin/SettingsController.php`**
- Application settings management
- Methods: `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`, `bulkUpdate()`

**`app/Http/Controllers/Admin/DatabaseController.php`**
- Database browser/explorer for all configured connections
- Methods:
  - `index()` - List all database connections and their tables
  - `show($connection, $table)` - Show table details with multiple views
- Features:
  - Supports SQLite, MySQL, MariaDB, PostgreSQL, SQL Server
  - View table structure (columns, types, constraints)
  - Browse table data with pagination
  - View indexes and foreign keys
  - Display row counts for all tables
  - Sensitive column masking (passwords, tokens, secrets) via `config('security.database_browser.masked_columns')`
  - Audit logging of database views via `AuditLog::log()`

**`app/Http/Controllers/Admin/AuditLogsController.php`**
- Audit log viewer for admin panel
- Methods: `index()` - List audit logs with pagination
- Displays polymorphic audit trail entries

**`app/Http/Controllers/NotificationsController.php`**
- Notification center for authenticated users
- Methods: `index()`, `markAsRead()`, `markAllAsRead()`
- Pagination and filtering support

#### Settings Controllers

**`app/Http/Controllers/Settings/ProfileController.php`**
- User profile settings
- Methods: `edit()`, `update()`, `destroy()` (delete account)

**`app/Http/Controllers/Settings/PasswordController.php`**
- User password change
- Methods: `edit()`, `update()`

**`app/Http/Controllers/Settings/TwoFactorAuthenticationController.php`**
- 2FA management
- Methods: `show()` (enable/disable 2FA)

**`app/Http/Controllers/Settings/RegistrationController.php`**
- Registration settings (enable/disable registration)

### 9.6 Form Requests

#### Base Form Request

**`app/Http/Requests/AbstractFormRequest.php`**
- Base form request class
- Provides common validation structure
- All form requests extend this

#### Admin Form Requests

**`app/Http/Requests/Admin/StoreUserRequest.php`**
- Validates user creation in admin panel

**`app/Http/Requests/Admin/StoreRoleRequest.php`**
- Validates role creation

**`app/Http/Requests/Admin/UpdateRoleRequest.php`**
- Validates role updates

**`app/Http/Requests/Admin/StorePermissionRequest.php`**
- Validates permission creation

**`app/Http/Requests/Admin/UpdatePermissionRequest.php`**
- Validates permission updates

**`app/Http/Requests/Admin/SettingStoreRequest.php`**
- Validates setting creation

**`app/Http/Requests/Admin/SettingsUpdateRequest.php`**
- Validates setting updates

#### Settings Form Requests

**`app/Http/Requests/Settings/ProfileUpdateRequest.php`**
- Validates profile updates

**`app/Http/Requests/Settings/PasswordUpdateRequest.php`**
- Validates password changes

**`app/Http/Requests/Settings/TwoFactorAuthenticationRequest.php`**
- Validates 2FA operations

**`app/Http/Requests/Settings/RegistrationSettingRequest.php`**
- Validates registration setting changes

#### Other Form Requests

**`app/Http/Requests/UpdateCookieConsentRequest.php`**
- Validates cookie consent preferences

### 9.7 Models

**`app/Models/User.php`**
- User Eloquent model with comprehensive authentication and authorization features
- **Traits used**:
  - `HasFactory` - Factory support for testing
  - `HasRoles` - Spatie Permission role-based access control
  - `Notifiable` - Laravel notification system
  - `TracksLastLogin` - Custom trait for login tracking
  - `TwoFactorAuthenticatable` - Laravel Fortify 2FA support
- **Fillable attributes**: name, email, password, cookie_consent_preferences, cookie_consent_given_at, data_processing_consent, data_processing_consent_given_at, gdpr_ip_address, last_login_at
- **Hidden attributes**: password, remember_token, two_factor_recovery_codes, two_factor_secret
- **Casts**: email_verified_at (datetime), password (hashed), cookie_consent_preferences (array)
- **Accessor Methods**:
  - `getFullNameAttribute()` - Returns full name (currently same as name)
  - `getInitialsAttribute()` - Generates user initials from name
  - `hasVerifiedEmail()` - Check if email is verified
  - `getDisplayNameAttribute()` - Display name for UI
- **Cookie Consent Methods**:
  - `hasCookieConsent()` - Check if user has given cookie consent
  - `hasDataProcessingConsent()` - Check if user has given data processing consent
  - `hasCookieConsentForCategory($category)` - Check consent for specific category
  - `updateCookieConsent($preferences)` - Update cookie consent preferences
  - `updateDataProcessingConsent($consent)` - Update data processing consent
- **Last Login Methods** (via TracksLastLogin trait):
  - `recordLastLogin()` - Record last login timestamp
  - `hasLoggedIn()` - Check if user has ever logged in

**`app/Models/Setting.php`**
- Setting Eloquent model for dynamic application configuration
- Stores key-value pairs with metadata for UI rendering
- **Fillable attributes**: key, value, field_type, options, label, description, role
- **Unique constraint**: key column is unique
- **Casts**:
  - `role` to `SettingRole` enum (System, User, Plugin)
  - `options` to array (for multi-select fields)
- **Field types supported**:
  - `input` - Text input field
  - `checkbox` - Boolean checkbox
  - `multioptions` - Multi-select dropdown
- **Static helper methods**:
  - `get($key, $default = null)` - Retrieve setting value by key
  - `set($key, $value)` - Update or create setting value
- **Use cases**: Feature flags, system configuration, user preferences, plugin settings

**`app/Models/AuditLog.php`**
- Polymorphic audit log model for tracking user actions
- Stores event type, old/new values, IP address, user agent
- Polymorphic `auditable` relationship for linking to any model
- Static `log()` method for convenient logging
- Used by impersonation, database browser, and other sensitive operations

#### Traits

**`app/Traits/TracksLastLogin.php`**
- Trait for tracking last login timestamp
- Methods: `recordLastLogin()`, `hasLoggedIn()`
- Used by User model

#### Enums

**`app/SettingRole.php`**
- Enum for setting roles (determines who can view/edit settings)
- Used by Setting model

#### Constants

**`app/Constants/RoleNames.php`**
- Application role name constants
- Constants: `SUPER_ADMIN`, `ADMIN`, `USER`
- Used throughout application for role checks

#### Facades

**`app/Facades/User.php`**
- User facade for convenient static access to user operations
- Provides static interface to user-related functionality

### 9.8 Middleware

**`app/Http/Middleware/HandleInertiaRequests.php`**
- Inertia.js middleware
- Shares data with all Inertia pages
- Shares: app name, quote, auth user (with roles/permissions), impersonation status, sidebar state

**`app/Http/Middleware/EnsureCookieConsent.php`**
- Ensures cookie consent is given
- Handles cookie consent requirements
- Shares consent status with Inertia pages

**`app/Http/Middleware/HandleAppearance.php`**
- Handles theme/appearance preferences from cookies
- Manages light/dark mode
- Persists theme selection across sessions

**`app/Http/Middleware/EnsureUserHasRole.php`**
- Role-based access control middleware
- Verifies user has required role(s) to access route
- Registered as 'role' middleware alias in bootstrap/app.php
- Usage: `Route::middleware('role:admin')` or `Route::middleware('role:super-admin,admin')`
- Returns 403 Forbidden if user lacks required role

**`app/Http/Middleware/EnsureUserExists.php`**
- Ensures the authenticated user record still exists in the database
- Prevents stale sessions from accessing protected routes

**`app/Http/Middleware/SecurityHeaders.php`**
- Adds HTTP security headers to all web responses
- Configurable via `config('security.headers')`
- Headers: X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy
- Optional HSTS (Strict-Transport-Security) when enabled for production
- Registered in `bootstrap/app.php` web middleware stack

### 9.9 Frontend Files

#### Entry Points

**`resources/js/app.ts`**
- Main application entry point
- Initializes Inertia.js app
- Sets up Vue 3 with Inertia plugin
- Initializes theme on page load

**`resources/js/ssr.ts`**
- Server-side rendering entry point
- Used for SSR builds

#### Pages

**Public Pages**:
- `resources/js/pages/Welcome.vue` - Landing page
- `resources/js/pages/Dashboard.vue` - User dashboard
- `resources/js/pages/PrivacyPolicy.vue` - Privacy policy page
- `resources/js/pages/CookiePolicy.vue` - Cookie policy page

**Authentication Pages** (`resources/js/pages/auth/`):
- `Login.vue` - Login page
- `Register.vue` - Registration page
- `ForgotPassword.vue` - Password reset request
- `ResetPassword.vue` - Password reset form
- `VerifyEmail.vue` - Email verification
- `TwoFactorChallenge.vue` - 2FA challenge
- `ConfirmPassword.vue` - Password confirmation

**Settings Pages** (`resources/js/pages/settings/`):
- `Profile.vue` - Profile settings
- `Password.vue` - Password change
- `Appearance.vue` - Theme/appearance settings
- `TwoFactor.vue` - 2FA management
- `CookiePreferences.vue` - Cookie consent preferences

**Admin Pages** (`resources/js/pages/admin/`):
- `Dashboard.vue` - Admin dashboard
- `Users/Index.vue` - User list with search
- `Users/Create.vue` - Create user with role assignment
- `Roles/Index.vue` - Role list with search
- `Roles/Create.vue` - Create role with permission assignment
- `Roles/Edit.vue` - Edit role with permission assignment
- `Permissions/Index.vue` - Permission list with search/grouping
- `Permissions/Create.vue` - Create permission with group
- `Permissions/Edit.vue` - Edit permission with group
- `Settings.vue` - Settings list with search
- `Settings/Create.vue` - Create setting (key-value pairs)
- `Settings/Edit.vue` - Edit setting
- `Database/Index.vue` - Database connections and tables list
- `Database/Show.vue` - Table/view details (main wrapper)
- `Database/show/Structure.vue` - Table structure view (columns, types, nullability)
- `Database/show/Data.vue` - Table data browser with pagination
- `Database/show/Indexes.vue` - Table indexes and foreign keys
- `Database/show/Actions.vue` - Table actions (future functionality)
- `Databases/Index.vue` - Database connections list (alternate view)
- `AuditLogs/Index.vue` - Audit log viewer with pagination

**Admin Payment Pages** (`resources/js/pages/admin/payments/`):
- `Dashboard.vue` - Payment overview dashboard
- `plans/Index.vue` - Payment plans list
- `plans/Create.vue` - Create payment plan
- `plans/Edit.vue` - Edit payment plan
- `invoices/Index.vue` - Invoices list with pagination
- `invoices/Show.vue` - Invoice details
- `customers/Index.vue` - Customers list with pagination
- `customers/Show.vue` - Customer details
- `subscriptions/Index.vue` - Subscriptions list with pagination
- `subscriptions/Show.vue` - Subscription details
- `transactions/Index.vue` - Transactions list with pagination
- `transactions/Show.vue` - Transaction details

**Admin Subscriber Pages** (`resources/js/pages/admin/subscribers/`):
- `Dashboard.vue` - Subscriber overview dashboard
- `Index.vue` - Subscribers list
- `Show.vue` - Subscriber details
- `lists/Index.vue` - Mailing lists
- `lists/Show.vue` - Mailing list details

**Notification Pages** (`resources/js/pages/notifications/`):
- Notification center with mark-as-read, filtering, and pagination

**Other Pages**:
- `resources/js/pages/About.vue` - About page

#### Layouts

**`resources/js/layouts/AppLayout.vue`**
- Main application layout
- Used for authenticated pages

**`resources/js/layouts/PublicLayout.vue`**
- Public-facing pages layout
- Includes public header and footer

**`resources/js/layouts/AuthLayout.vue`**
- Authentication pages layout
- Various auth layouts in `auth/` subdirectory

**`resources/js/layouts/admin/AdminLayout.vue`**
- Admin panel layout

**`resources/js/layouts/app/AppHeaderLayout.vue`**
- App header layout component

**`resources/js/layouts/app/AppSidebarLayout.vue`**
- App sidebar layout component

**`resources/js/layouts/settings/Layout.vue`**
- Settings pages layout

#### Composables

**`resources/js/composables/useAppearance.ts`**
- Theme/appearance management
- Handles light/dark mode switching
- Persists preferences

**`resources/js/composables/useCookieConsent.ts`**
- Cookie consent handling
- Manages cookie preferences
- Communicates with backend

**`resources/js/composables/useInitials.ts`**
- User initials generation
- Creates initials from user name

**`resources/js/composables/useTwoFactorAuth.ts`**
- 2FA functionality
- Handles 2FA setup and verification

**`resources/js/composables/useCurrentUrl.ts`**
- Returns the current page URL from Inertia
- Useful for active link detection and navigation state

**`resources/js/composables/useErrorHandler.ts`**
- Centralized error handling for frontend operations
- Provides consistent error display and logging

**`resources/js/composables/useToast.ts`**
- Toast notification composable
- Provides success/error/info toast messages

#### Components

**Location**: `resources/js/components/`

Contains reusable Vue components built with shadcn-vue and Reka UI. Components follow shadcn-vue patterns and are organized by feature/type.

#### Types

**Location**: `resources/js/types/`

TypeScript type definitions for:
- Page props
- Models
- Forms
- API responses

### 9.10 Configuration & Routes

#### Route Files

**`routes/web.php`** (94 routes total)
- Main web routes file with all public and authenticated routes
- **Public Routes**:
  - `/` - Welcome page
  - `/about` - About page
  - `/privacy-policy` - Privacy policy
  - `/cookie-policy` - Cookie policy
- **Quick Login** (local development only):
  - `/quick-login` - Development helper for quick authentication
- **Authentication Routes**:
  - Handled by Laravel Fortify (login, register, password reset, email verification, 2FA)
- **Dashboard**:
  - `/dashboard` - User dashboard (auth + verified middleware)
- **Cookie Consent**:
  - `GET /cookie-consent/preferences` - Get consent preferences
  - `POST /cookie-consent/preferences` - Update preferences
  - `POST /cookie-consent/accept-all` - Accept all cookies
  - `POST /cookie-consent/reject-all` - Reject non-essential cookies
- **Settings Routes**:
  - Loaded from `routes/settings.php`
- **Impersonation** (admin/super-admin only):
  - `GET /impersonate` - List users for impersonation
  - `POST /impersonate` - Start impersonating user
  - `DELETE /impersonate` - Stop impersonation
- **Admin Routes** (prefix: `/admin`):
  - `/admin` - Admin dashboard
  - `/admin/settings/*` - Settings CRUD + bulk update
  - `/admin/users/*` - User management
  - `/admin/roles/*` - Role CRUD
  - `/admin/permissions/*` - Permission CRUD
  - `/admin/database` - Database browser
  - `/admin/database/{connection}/{table}` - Table details
  - `/admin/audit-logs` - Audit log viewer
- **Notification Routes**:
  - Notification center with mark-as-read and filtering
- **Payment Routes** (via payment-gateway package, admin middleware):
  - Payment dashboard, plans CRUD, invoices, customers, subscriptions, transactions
- **Subscriber Routes** (via subscribe package):
  - Subscriber dashboard, subscriber list, mailing lists

**`routes/settings.php`** (6 routes)
- User settings routes (prefix: `/settings`, auth + verified middleware)
- **Profile**:
  - `GET /settings/profile` - Edit profile
  - `PATCH /settings/profile` - Update profile
  - `DELETE /settings/profile` - Delete account
- **Password**:
  - `GET /settings/password` - Change password page
  - `PUT /settings/password` - Update password
- **Appearance**:
  - `GET /settings/appearance` - Theme selection
- **Two-Factor Authentication**:
  - `GET /settings/two-factor` - 2FA management
- **Cookie Preferences**:
  - `GET /settings/cookie-preferences` - Cookie preference page

**`routes/console.php`**
- Console/Artisan command routes
- `inspire` command - Display inspiring quote

#### Configuration Files

**`config/fortify.php`**
- Laravel Fortify configuration
- Authentication features enabled/disabled

**`config/inertia.php`**
- Inertia.js configuration
- SSR settings, shared data

**`config/permission.php`**
- Spatie Laravel Permission configuration

**`config/security.php`**
- Centralized security configuration
- Sections: headers, rate_limiting, dev_routes, database_browser, session, password_timeout
- All values configurable via environment variables

**`config/cookie.php`**
- Cookie consent configuration
- Category definitions (essential, analytics, marketing, preferences)

**`config/horizon.php`**
- Laravel Horizon queue monitoring configuration
- Queue worker and supervisor settings

**`vite.config.ts`**
- Vite build configuration
- Plugins: Laravel, Tailwind, Wayfinder, Vue
- Code splitting configuration

**`tsconfig.json`**
- TypeScript configuration
- Strict type checking

**`pint.json`**
- Laravel Pint configuration
- PHP code style rules

**`eslint.config.js`**
- ESLint configuration
- Vue 3 + TypeScript rules

### 9.11 Testing Files

#### Test Structure

**`tests/TestCase.php`**
- Base test case class
- All tests extend this

#### Feature Tests (27 test files)

**Location**: `tests/Feature/`

Feature tests for HTTP endpoints and complete user flows:

**Authentication Tests** (`tests/Feature/Auth/`):
- `AuthenticationTest.php` - Login flow, authentication attempts, logout
- `RegistrationTest.php` - User registration process
- `PasswordResetTest.php` - Password reset email and update flow
- `EmailVerificationTest.php` - Email verification process
- `VerificationNotificationTest.php` - Resending verification emails
- `PasswordConfirmationTest.php` - Password confirmation for sensitive actions
- `TwoFactorChallengeTest.php` - 2FA authentication challenges

**User Feature Tests**:
- `DashboardTest.php` - Dashboard access and authentication requirements
- `LastLoginTrackingTest.php` - Last login timestamp tracking
- `ImpersonateTest.php` - User impersonation functionality
- `CookieConsentTest.php` - Cookie consent management
- `CookieConsentControllerTest.php` - Cookie consent controller endpoints
- `NotificationsControllerTest.php` - Notification endpoints
- `AboutControllerTest.php` - About page

**Settings Tests** (`tests/Feature/Settings/`):
- `ProfileUpdateTest.php` - Profile information updates
- `PasswordUpdateTest.php` - Password change functionality
- `TwoFactorAuthenticationTest.php` - 2FA enable/disable/recovery codes
- `AppearanceTest.php` - Appearance/theme settings

**Admin Tests** (`tests/Feature/Admin/`):
- `AdminDashboardTest.php` - Admin dashboard access
- `UsersControllerTest.php` - Admin user management
- `RolesControllerTest.php` - Admin role CRUD
- `PermissionsControllerTest.php` - Admin permission CRUD
- `SettingsControllerTest.php` - Admin settings management
- `DatabaseControllerTest.php` - Database browser
- `AuditLogsControllerTest.php` - Audit log viewer

**Integration Tests**:
- `RepositoryServiceIntegrationTest.php` - Repository and Service integration
- `ExampleTest.php` - Example feature test

#### Unit Tests (15 test files)

**Location**: `tests/Unit/`

**Action Tests** (`tests/Unit/Actions/User/`):
- `CreateUserActionTest.php` - User creation action
- `UpdateUserProfileActionTest.php` - Profile update action
- `UpdateUserPasswordActionTest.php` - Password update action
- `DeleteUserActionTest.php` - User deletion action

**Repository Tests** (`tests/Unit/Repositories/`):
- `AbstractRepositoryTest.php` - Base repository functionality (caching, CRUD)
- `UserRepositoryTest.php` - User repository specific methods

**Service Tests** (`tests/Unit/Services/`):
- `AbstractServiceTest.php` - Base service functionality (transactions, validation)
- `UserServiceTest.php` - User service business logic
- `NotificationServiceTest.php` - Notification service
- `ImpersonationServiceTest.php` - Impersonation service
- `RoleServiceTest.php` - Role service
- `PermissionServiceTest.php` - Permission service
- `SettingsServiceTest.php` - Settings service

**Utility Tests**:
- `ExampleTest.php` - Example unit test
- `InstallerWorkflowTest.php` - Installation workflow logic
- `OptionsTest.php` - Options utility functionality
- `WorkflowResultTest.php` - Workflow result handling

**Test Coverage** (413 tests, 1695 assertions):
- All authentication flows (login, register, 2FA, password reset)
- User management CRUD operations
- Role and permission CRUD
- Admin settings management
- Database browser
- Audit logging
- Notifications
- Cookie consent management
- Impersonation functionality
- Repository pattern with caching
- Service pattern with transactions
- Action pattern execution

---

## Performance Considerations

### Caching Strategy

**Repository Caching**:
- Repositories automatically cache read operations (`find()`, `all()`)
- Cache is automatically invalidated on write operations (`create()`, `update()`, `delete()`)
- Default cache TTL: 3600 seconds (1 hour)
- Override `$cacheTtl` in repository classes to customize

**Example**:
```php
final class PostRepository extends BaseRepository
{
    protected int $cacheTtl = 1800; // 30 minutes

    // Cache is automatically used for find(), all()
    // Automatically cleared on create(), update(), delete()
}
```

### Eager Loading

**Always eager load relationships to avoid N+1 queries**:

```php
// ✅ Good - Eager loading
$users = $this->repository->query()
    ->with('posts', 'roles')
    ->get();

// ❌ Bad - N+1 queries
$users = $this->repository->all();
foreach ($users as $user) {
    $user->posts; // N+1 query
}
```

### Database Indexing

**Add indexes for frequently queried columns**:

```php
// Migration
Schema::table('posts', function (Blueprint $table): void {
    $table->index('slug');
    $table->index('published');
    $table->index(['user_id', 'published']);
});
```

### Frontend Optimization

**Code Splitting**:
- Vite automatically splits vendor and UI libraries
- Manual chunks configured in `vite.config.ts`

**Inertia Prefetching**:
- Links are automatically prefetched on hover
- Configured in `resources/js/app.ts`

**Lazy Loading**:
- Use Inertia's deferred props for heavy data
- Use `WhenVisible` component for infinite scroll

---

## Security Best Practices

### Authentication & Authorization

**Always check permissions**:
```php
// In controllers
public function destroy(Post $post): RedirectResponse
{
    $this->authorize('delete', $post);
    // ... delete logic
}

// In Blade/Inertia
@can('delete', $post)
    <button>Delete</button>
@endcan
```

**Use Spatie Permissions**:
```php
// Check role
if ($user->hasRole('admin')) {
    // Admin only
}

// Check permission
if ($user->can('edit posts')) {
    // User can edit posts
}
```

### Input Validation

**Always validate user input**:
- Use Form Requests for validation
- Never trust user input
- Validate on both frontend and backend

### SQL Injection Prevention

**Use Eloquent/Query Builder**:
```php
// ✅ Safe - Eloquent handles escaping
$posts = Post::where('title', $title)->get();

// ✅ Safe - Query Builder
$posts = DB::table('posts')->where('title', $title)->get();

// ❌ Never use raw queries with user input
DB::select("SELECT * FROM posts WHERE title = '{$title}'");
```

### XSS Prevention

**Inertia automatically escapes data**:
- All props passed to Inertia are automatically escaped
- `v-html` is not used in this codebase — pagination labels use `decodePaginationLabel()` utility from `@/lib/utils` to safely decode HTML entities (e.g., `&laquo;` → `«`)
- Always use `{{ }}` interpolation or `v-text` instead of `v-html`

### Security Headers

**SecurityHeaders middleware** adds HTTP security headers to all responses:
- X-Frame-Options (default: SAMEORIGIN)
- X-Content-Type-Options: nosniff
- Referrer-Policy (default: strict-origin-when-cross-origin)
- Permissions-Policy (default: camera=(), microphone=(), geolocation=())
- Optional HSTS for production (configurable via `SECURITY_HSTS_ENABLED`)
- Configurable via `config/security.php` and environment variables

### Sensitive Data Masking

**Database browser** masks sensitive columns (passwords, tokens, secrets):
- Configured via `config('security.database_browser.masked_columns')`
- Values displayed as `••••••••` in the admin UI

### CSRF Protection

**Laravel automatically handles CSRF**:
- All forms include CSRF tokens
- Inertia automatically includes CSRF tokens in requests

---

## Troubleshooting

### Common Issues

#### Issue: "Unable to locate file in Vite manifest"

**Solution**:
```bash
npm run build
# or
npm run dev
```

#### Issue: Routes not found after adding new routes

**Solution**:
```bash
php artisan route:clear
php artisan route:cache  # Production only
```

#### Issue: Changes not reflecting in frontend

**Solution**:
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
npm run build
```

#### Issue: TypeScript errors in Wayfinder

**Solution**:
```bash
php artisan wayfinder:generate
```

#### Issue: Permission checks not working

**Solution**:
- Ensure user has roles assigned: `$user->assignRole('admin')`
- Ensure role has permissions: `$role->givePermissionTo('edit posts')`
- Check `AuthServiceProvider` for Gate::before configuration
- Clear cache: `php artisan permission:cache-reset`

#### Issue: Repository cache not clearing

**Solution**:
- Check cache driver in `.env`
- Manually clear cache: `php artisan cache:clear`
- Verify cache key prefix in repository

#### Issue: Inertia shared data not updating

**Solution**:
- Check `HandleInertiaRequests` middleware
- Clear view cache: `php artisan view:clear`
- Ensure middleware is registered in `bootstrap/app.php`

### Debugging Tips

**Enable Debug Mode**:
```env
APP_DEBUG=true
APP_ENV=local
```

**View Routes**:
```bash
php artisan route:list
```

**View Logs**:
```bash
# Real-time logs
php artisan pail

# Or view log file
tail -f storage/logs/laravel.log
```

**Database Queries**:
- Laravel Debugbar shows all queries in development
- Use `DB::enableQueryLog()` and `DB::getQueryLog()`

**Frontend Debugging**:
- Use browser DevTools
- Check Inertia requests in Network tab
- Use Vue DevTools extension

---

## Development Workflow

### Adding a New Feature

When adding a new feature, follow this workflow:

1. **Create Model** (if needed)
   ```bash
   php artisan make:model ModelName -m
   ```

2. **Create Migration**
   ```bash
   php artisan make:migration create_model_name_table
   ```

3. **Create Repository Interface**
   - Create interface in `app/Contracts/Repositories/`
   - Extend `RepositoryInterface`

4. **Create Repository**
   - Create in `app/Repositories/`
   - Extend `BaseRepository` or `AbstractRepository`
   - Implement your interface
   - Register in `RepositoryServiceProvider`

5. **Create Service Interface** (if needed)
   - Create in `app/Contracts/Services/`

6. **Create Service**
   - Create in `app/Services/`
   - Extend `AbstractService`
   - Implement your interface
   - Use repository for data access

7. **Create Actions** (if needed)
   - Create in `app/Actions/`
   - Implement `ActionInterface`

8. **Create Form Requests**
   - Create in `app/Http/Requests/`
   - Extend `AbstractFormRequest`

9. **Create Controller**
   - Create in `app/Http/Controllers/`
   - Use service, not repository directly
   - Return Inertia responses

10. **Create Routes**
    - Add to `routes/web.php` or create new route file

11. **Create Frontend Pages**
    - Create in `resources/js/pages/`
    - Use appropriate layout

12. **Write Tests**
    - Feature tests for HTTP endpoints
    - Unit tests for services/repositories

13. **Format Code**
    ```bash
    vendor/bin/pint --dirty
    npm run format
    ```

14. **Run Tests**
    ```bash
    php artisan test
    ```

### Code Quality Checklist

Before committing:

- [ ] All PHP files have `declare(strict_types=1);`
- [ ] All methods have explicit return types
- [ ] Code formatted with Pint: `vendor/bin/pint --dirty`
- [ ] Frontend code formatted: `npm run format`
- [ ] All tests passing: `php artisan test`
- [ ] No linter errors
- [ ] Repository pattern followed (no direct model queries in controllers)
- [ ] Service pattern followed (business logic in services)
- [ ] Form Request validation used (no inline validation)

---

## Tracking Changes

### Using Git History

To track when files were created or modified:

```bash
# View file history
git log --follow <file>

# View file history with dates
git log --follow --date=short --pretty=format:"%h %ad %s" <file>

# View when file was created
git log --follow --diff-filter=A -- <file>
```

### Updating Documentation

When adding new features:

1. Update this `CODEBASE.md` file
2. Add new files to the appropriate section in "Comprehensive File Inventory"
3. Document the purpose of new files
4. Update architecture diagrams if needed
5. Update data flow examples if patterns change

### Version Tracking

This starter kit is designed to be cloned and customized. When using this as a base:

1. Document your project-specific changes
2. Keep track of which features you've added
3. Update this documentation as you extend the starter kit

---

## Additional Notes

- This codebase is designed to be **cloned and customized** for different projects
- The architecture patterns (Repository, Service, Action) should be followed **consistently**
- All code must adhere to **PHP 8.4+ standards** with strict types
- **Spatie Permissions** is a core dependency - roles and permissions are built into the foundation
- **Laravel Pint** is required for code formatting - always run before committing
- **Explicit return types** are required for all methods and functions
- This is a **generic starter kit** - not specific to any domain

---

## Key Features Summary

This starter kit includes the following comprehensive features out of the box:

### Authentication & Security Features

**Laravel Fortify Authentication**:
- User registration (can be toggled via settings)
- Login/logout with rate limiting (5 attempts/minute)
- Password reset via email
- Email verification
- Two-factor authentication (TOTP) with QR codes
- 2FA recovery codes
- Password confirmation for sensitive actions
- Quick login for local development

**Authorization (Spatie Permission)**:
- Role-based access control (RBAC)
- Three default roles: super-admin, admin, user
- Dynamic permission system with grouping
- Permission-based route protection
- Role middleware for route guards
- Granular permission assignment

### User Management

**User Features**:
- Full CRUD operations via admin panel
- User search functionality
- Profile management (name, email)
- Password change with current password verification
- Account deletion with password confirmation
- Email verification workflow
- Last login tracking
- User impersonation for admins/super-admins

**User Repository & Service Pattern**:
- Clean architecture with Repository/Service layers
- Transaction support for data integrity
- Validation helpers
- Caching with automatic invalidation

### Admin Panel Features

**User Management**:
- List, create, and search users
- Assign roles during creation
- View user details

**Role Management**:
- Full CRUD for roles
- Assign permissions to roles
- Search and filter roles
- Cannot delete super-admin role (protected)

**Permission Management**:
- Full CRUD for permissions
- Group permissions by resource
- Assign permissions to roles
- Search and filter permissions

**Settings Management**:
- Dynamic application settings (key-value pairs)
- Three field types: input, checkbox, multioptions
- Role-based settings (system, user, plugin)
- Bulk settings update
- Settings search and filtering

**Database Browser**:
- View all configured database connections
- Browse tables and views
- Inspect table structure (columns, types, constraints)
- Browse table data with pagination
- View indexes and foreign keys
- Support for: SQLite, MySQL, MariaDB, PostgreSQL, SQL Server
- Row counts for all tables
- Sensitive column masking (passwords, tokens, secrets)
- Audit logging of database views

**Audit Logs**:
- Polymorphic audit trail for user actions
- Tracks event type, old/new values, IP address, user agent
- Admin viewer with pagination
- Used by impersonation, database browser, and other sensitive operations

**Payment Management** (via `nejcc/payment-gateway`):
- Payment dashboard with overview metrics
- Plan management (CRUD)
- Customer management with details view
- Invoice listing and details
- Subscription management with details view
- Transaction listing and details
- Multi-driver support: Stripe, PayPal, Crypto, Bank Transfer, COD

**Subscriber Management** (via `nejcc/subscribe`):
- Subscriber dashboard
- Subscriber listing and details
- Mailing list management

**Notification Center**:
- In-app notifications with mark-as-read
- Mark all as read functionality
- Pagination and filtering

### Cookie Consent & GDPR

**GDPR Compliance**:
- Cookie consent banner for guests and authenticated users
- Category-based consent (essential, analytics, marketing, preferences)
- Accept all / Reject all functionality
- Granular category preferences
- Data processing consent tracking
- IP address logging for audit trail
- Timestamp tracking
- Privacy policy and cookie policy pages
- Session and database persistence

### User Settings Pages

**Profile Settings**:
- Update name and email
- Email verification reset on email change
- Delete account with password confirmation

**Security Settings**:
- Change password (requires current password)
- Enable/disable two-factor authentication
- View and regenerate 2FA recovery codes
- QR code setup for authenticator apps

**Appearance Settings**:
- Light/dark/system theme selection
- Persistent theme across sessions
- Full dark mode support throughout app

**Cookie Preferences**:
- Manage cookie consent categories
- View current consent status
- Update preferences anytime

### Development & Testing Features

**Development Tools**:
- Laravel Pint for PHP code formatting
- Laravel Pail for real-time log viewing
- Laravel Horizon for queue monitoring
- Laravel Debugbar for development debugging
- Quick login for local development (disabled by default, IP-restricted)
- Comprehensive test suite (413 tests, 1695 assertions)

**Testing**:
- 27 feature test files covering all major flows
- 15 unit test files for actions, repositories, and services
- Test coverage for authentication, authorization, CRUD operations, admin panel
- PHPUnit 11 test framework

### Frontend Features

**Vue 3 & Inertia.js 2**:
- Seamless SPA experience with server-side routing
- Prefetching for instant navigation
- Type-safe routes with Laravel Wayfinder
- Form component with validation
- Deferred props support
- TypeScript support

**UI Components**:
- 136+ shadcn-vue components
- Custom app components (sidebar, header, navigation)
- Responsive design
- Dark mode support
- Tailwind CSS 4 styling
- Accessible components (Reka UI)

**Layouts**:
- Multiple layout options (auth, app, admin, settings, public)
- Responsive sidebar navigation
- Header with user menu
- Breadcrumb navigation
- Mobile-friendly design

### Architecture Features

**Clean Architecture**:
- Repository pattern for data access
- Service pattern for business logic
- Action pattern for single-purpose operations
- Dependency injection throughout
- Interface-based programming
- Separation of concerns

**Caching**:
- Repository-level caching
- Automatic cache invalidation
- Configurable TTL per repository
- Redis/File/Database cache driver support

**Code Quality**:
- Strict PHP typing (declare(strict_types=1))
- Explicit return types required
- Laravel Pint formatting enforced
- ESLint for frontend code
- Comprehensive type definitions

### Performance Features

**Optimization**:
- Repository caching with auto-invalidation
- Eager loading for relationships
- Vite code splitting
- Inertia prefetching
- Lazy loading support
- Database indexing on key columns

**Production Ready**:
- Config/route/view caching
- Optimized autoloader
- Asset minification and bundling
- SSR support ready

---

## Quick Reference

### Key Files

- **Core Repository**: `app/Repositories/AbstractRepository.php`
- **Core Service**: `app/Services/AbstractService.php`
- **Service Provider**: `app/Providers/RepositoryServiceProvider.php`
- **Bootstrap**: `bootstrap/app.php`
- **Main Frontend Entry**: `resources/js/app.ts`
- **Inertia Middleware**: `app/Http/Middleware/HandleInertiaRequests.php`
- **Auth Provider**: `app/Providers/AuthServiceProvider.php`
- **Fortify Provider**: `app/Providers/FortifyServiceProvider.php`

### Common Commands

```bash
# Development
composer run dev              # Start all dev services
composer run dev:ssr          # Start with SSR
php artisan test              # Run tests
vendor/bin/pint --dirty       # Format PHP code
npm run format                # Format frontend code
npm run build                 # Build frontend assets

# Database
php artisan migrate           # Run migrations
php artisan migrate:fresh --seed    # Fresh migration (drops all tables) + seed

# Cache
php artisan config:clear      # Clear config cache
php artisan cache:clear       # Clear application cache
php artisan route:clear       # Clear route cache
```

---

## Documentation Updates

**Last Updated**: 2026-01-31

**Recent Changes**:
- Added security hardening documentation (SecurityHeaders middleware, sensitive column masking, XSS prevention)
- Added AuditLog model documentation
- Added NotificationsController and AuditLogsController documentation
- Added ImpersonationService, NotificationService, RoleService, PermissionService documentation
- Added useCurrentUrl, useErrorHandler, useToast composable documentation
- Added config/security.php, config/cookie.php, config/horizon.php documentation
- Added admin payment pages documentation (dashboard, plans, invoices, customers, subscriptions, transactions)
- Added admin subscriber pages documentation (dashboard, list, mailing lists)
- Added notification pages documentation
- Added EnsureUserExists and SecurityHeaders middleware documentation
- Updated test counts (413 tests, 1695 assertions across 27 feature + 15 unit test files)
- Updated XSS prevention section (v-html replaced with safe decodePaginationLabel utility)
- Added Security Headers and Sensitive Data Masking sections
- Updated Quick Login section with security hardening (config toggle, IP restriction)
- Updated database browser with masking and audit logging features

**Documentation Maintenance**:
This documentation should be updated whenever new features are added to the starter kit. Use `git log --follow CODEBASE.md` to track documentation changes over time.
