# Laravel + Vue Starter Kit

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4.svg)](https://php.net)
[![Vue](https://img.shields.io/badge/Vue-3.x-4FC08D.svg)](https://vuejs.org)
[![Inertia](https://img.shields.io/badge/Inertia-2.x-9553E9.svg)](https://inertiajs.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A modern, production-ready starter kit for building Laravel applications with Vue 3 frontend using Inertia.js. Ships with authentication, admin panel, payment processing, email subscriptions, GDPR compliance, and more.

## Features

### Frontend Stack
- **Vue 3** with Composition API and TypeScript
- **Inertia.js v2** for seamless SPA experience with deferred props, prefetching, and polling
- **Tailwind CSS v4** with custom breakpoints (xs/sm/md/lg)
- **shadcn-vue** component library built on Reka UI
- **Vite** with hot module replacement and code splitting
- **VueUse** composition utilities
- **Lucide Vue** icon library
- **Server-Side Rendering (SSR)** support
- **Dark mode** with appearance settings

### Authentication & Security (Laravel Fortify)
- Login with email/password and "remember me"
- User registration (toggleable via global settings)
- Email verification flow
- Password reset functionality
- Two-factor authentication (TOTP) with QR codes and recovery codes
- Password confirmation for sensitive operations
- Session management with last login tracking
- Rate limiting on authentication endpoints

### Role-Based Access Control (Spatie Permission)
- Three built-in roles: super-admin, admin, user
- Granular permission management with grouping
- Role-permission association
- Super-admin auto-granted all permissions via Gate
- Middleware-based route protection

### Admin Panel
- **Dashboard** with user stats, role/permission counts, recent activity
- **User Management** — CRUD with role assignment, search, pagination, slug generation
- **Role Management** — Create, edit, delete roles with permission assignment
- **Permission Management** — Granular permission control with grouping
- **Settings Management** — Dynamic key-value settings with field types and role-based access
- **Database Browser** — View tables, columns, indexes, foreign keys, and data across multiple database engines
- **Audit Log Viewer** — Search and filter audit trail by event, user, or IP address
- **Impersonation** — Login as other users for debugging (super-admin/admin only) with audit logging

### Payment Gateway (Multi-Provider)
- **Providers**: Stripe, PayPal, Coinbase Commerce (crypto), bank transfer, cash on delivery
- **Subscriptions**: Plans with trial periods, cancellation, grace periods, monthly/yearly billing
- **Invoices**: Automatic generation, PDF export (DomPDF/Browsershot), line items, multiple states
- **Refunds**: Full and partial refund processing with tracking
- **Webhooks**: Automatic handling for Stripe, PayPal, and Coinbase
- **Email Notifications**: Payment receipts, failure alerts, subscription confirmations, trial reminders
- **Admin Panel**: Payment dashboard, customer/invoice/plan/subscription/transaction management
- **Artisan Commands**: `payment-gateway:install`, `payment-gateway:sync-plans`, `payment-gateway:trial-reminders`, `payment-gateway:cleanup-subscriptions`

### Email Subscriptions & Newsletters
- Subscriber management with search and bulk operations
- Subscription list creation and organization
- **Multi-provider support**: Brevo, Mailchimp, HubSpot, ConvertKit
- Admin panel with subscriber dashboard, listing, and list management

### Global Settings
- Key-value settings store with typed fields (input, checkbox, multioptions)
- Role-based access control (system, user, plugin)
- Admin CRUD with search and bulk update
- Facade access: `GlobalSettings::get()`, `GlobalSettings::set()`
- Audit log integration

### Notifications System
- Database-backed notifications with UUID primary keys
- Read/unread status with filtering
- Mark single or all as read
- Delete individual notifications
- Unread count and recent notifications dropdown

### GDPR & Cookie Consent
- Granular cookie consent by category (essential, analytics, marketing, custom)
- Accept all / reject all / selective preferences
- Guest storage via session + browser cookies, authenticated via database
- GDPR mode with data processing consent during registration
- Consent audit logging with IP tracking
- Cookie and privacy policy pages
- Configurable cookie lifetime (365 days default)

### Audit Logging
- Polymorphic audit trail tracking user, event, old/new values, IP, and user agent
- Tracked events: user CRUD, role/permission changes, impersonation, settings changes, payment transactions, consent changes
- Admin viewer with search, filtering, and pagination

### User Settings Pages
- Profile editing (name, email)
- Password management with throttling
- Two-factor authentication setup/teardown
- Appearance/theme preferences
- Cookie consent preferences
- Account deletion with password confirmation

## Architecture

### Backend Layer Flow
```
Controllers -> Services -> Repositories -> Models
     |
  Actions (single-responsibility operations)
```

- **Repositories** (`app/Repositories/`) — Extend `BaseRepository` with CRUD and query building
- **Services** (`app/Services/`) — Extend `AbstractService` with transaction support, bound via contracts
- **Actions** (`app/Actions/`) — Single-responsibility classes implementing `ActionInterface`, organized by domain

### Frontend Structure
- **Pages** (`resources/js/pages/`) — 57+ Inertia page components organized by feature
- **Layouts** (`resources/js/layouts/`) — AppLayout, AuthLayout, PublicLayout, AdminLayout with sidebar/header/card/split variants
- **Components** (`resources/js/components/`) — shadcn-vue UI components
- **Wayfinder** (`resources/js/actions/`) — Auto-generated TypeScript route functions
- **Types** (`resources/js/types/`) — TypeScript interfaces for page props, models, forms

## Prerequisites

- **PHP 8.4+** with required extensions
- **Composer** (latest version)
- **Node.js 18+** and npm
- **SQLite** (default) or MySQL/PostgreSQL
- **Git**

## Installation

### Quick Start

```bash
git clone <repository-url> your-project-name
cd your-project-name
composer run setup
```

The setup script installs PHP and Node dependencies, copies `.env.example`, generates the app key, runs migrations, and builds frontend assets.

### Manual Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm run build
```

### Development

```bash
composer run dev        # Starts Laravel server, queue worker, Pail log viewer, and Vite
composer run dev:ssr    # Same with SSR enabled
```

## Commands

### Testing
```bash
php artisan test --compact                                # Run all tests
php artisan test --compact tests/Feature/ExampleTest.php  # Run single file
php artisan test --compact --filter=testName              # Run single test
```

### Code Formatting & Linting
```bash
vendor/bin/pint --dirty   # Format changed PHP files
vendor/bin/pint           # Format all PHP files
npm run lint              # ESLint
npm run format            # Prettier
```

### Frontend Build
```bash
npm run build             # Production build
npm run build:ssr         # SSR build
```

### Payment Gateway
```bash
php artisan payment-gateway:install            # Interactive installer
php artisan payment-gateway:sync-plans         # Sync plans to provider
php artisan payment-gateway:trial-reminders    # Send trial ending reminders
php artisan payment-gateway:cleanup-subscriptions  # Clean expired subscriptions
```

## Project Structure

```
app/
├── Actions/          # Single-responsibility business operations
├── Contracts/        # Interfaces (Repository, Service, Action)
├── Facades/          # Application facades
├── Http/
│   ├── Controllers/  # Web and admin controllers
│   ├── Middleware/    # Custom middleware
│   └── Requests/     # Form request validation
├── Models/           # Eloquent models
├── Providers/        # Service providers
├── Repositories/     # Data access layer (Repository Pattern)
└── Services/         # Business logic layer (Service Pattern)

packages/
├── nejcc/payment-gateway/   # Multi-provider payment processing
└── nejcc/subscribe/         # Email subscription management

resources/js/
├── components/       # Vue components (shadcn-vue in ui/)
├── composables/      # Vue composition functions
├── layouts/          # Layout components (9+ variants)
├── pages/            # Inertia page components (57+ pages)
└── types/            # TypeScript type definitions

routes/
├── web.php           # Main web routes (47+ routes)
└── settings.php      # User settings routes
```

## Custom Packages

| Package | Description |
|---------|-------------|
| `laravelplus/global-settings` | Key-value settings with admin panel and role-based access |
| `nejcc/payment-gateway` | Multi-provider payments, subscriptions, invoices, refunds, webhooks |
| `nejcc/subscribe` | Email subscriptions with Brevo, Mailchimp, HubSpot, ConvertKit support |

## Tech Stack

### Backend
- Laravel 12, PHP 8.4+, Laravel Fortify, Spatie Permission, Laravel Wayfinder, Laravel Horizon, Inertia.js (server adapter)

### Frontend
- Vue 3, TypeScript, Inertia.js v2 (client adapter), Tailwind CSS 4, shadcn-vue, Reka UI, Lucide Vue, VueUse

### Development Tools
- Vite, ESLint 9, Prettier 3, Laravel Pint, Laravel Pail, Laravel Debugbar, Laravel Boost (MCP), PHPUnit 11

## Testing

PHPUnit with feature and unit tests. Uses in-memory SQLite and model factories.

```bash
php artisan test --compact              # All tests
php artisan test --compact --filter=X   # Specific test
```

GitHub Actions CI runs tests automatically on push and pull request.

## Configuration

### Environment Variables

```env
APP_NAME="Your App Name"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
```

### Tailwind CSS v4 Breakpoints

| Breakpoint | Size | Target |
|------------|------|--------|
| xs | < 768px | Phones |
| sm | >= 768px | Tablets |
| md | >= 992px | Small laptops |
| lg | >= 1200px | Desktops |

### Adding shadcn-vue Components

```bash
npx shadcn-vue@latest add [component-name]
```

## Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [Vue 3 Documentation](https://vuejs.org/guide/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [shadcn-vue Documentation](https://www.shadcn-vue.com/)

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
