# Laravel + Vue Starter Kit

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4.svg)](https://php.net)
[![Vue](https://img.shields.io/badge/Vue-3.x-4FC08D.svg)](https://vuejs.org)
[![Inertia](https://img.shields.io/badge/Inertia-2.x-9553E9.svg)](https://inertiajs.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A modern, production-ready starter kit for building Laravel applications with Vue 3 frontend using Inertia.js. This kit provides a robust foundation with TypeScript, Tailwind CSS, and shadcn-vue components.

## 🚀 Features

- **Laravel 12** with PHP 8.4+ support
- **Vue 3** with Composition API and TypeScript
- **Inertia.js 2** for seamless SPA experience
- **Tailwind CSS 4** for utility-first styling
- **shadcn-vue** component library with Reka UI
- **Laravel Fortify** for authentication
- **Server-Side Rendering (SSR)** support
- **Repository Pattern** implementation
- **ESLint & Prettier** for code quality
- **Vite** for lightning-fast development

## 📝 What's Changed from Base Laravel

This starter kit extends the base Laravel installation with the following additions and modifications:

### 🎨 Frontend Stack
- **Inertia.js v2** - Complete SPA setup with Vue 3 adapter
- **Vue 3** with Composition API and TypeScript support
- **Tailwind CSS v4** - Modern utility-first CSS framework
- **shadcn-vue** - Beautiful component library built on Reka UI
- **Vite** - Modern build tool replacing Laravel Mix
- **TypeScript** - Full type safety for frontend code
- **VueUse** - Essential Vue composition utilities
- **Lucide Vue** - Modern icon library

### 🔐 Authentication & Security
- **Laravel Fortify** - Headless authentication backend
- **Two-Factor Authentication (2FA)** - QR code-based 2FA with recovery codes
- **Email Verification** - Built-in email verification flow
- **Password Reset** - Complete password reset functionality
- **Cookie Consent** - GDPR-compliant cookie consent system

### 🏛️ Architecture Patterns
- **Repository Pattern** - `AbstractRepository` with built-in caching
- **Service Pattern** - `AbstractService` with transaction support
- **Action Pattern** - Single-responsibility action classes
- **Contract Interfaces** - Type-safe interfaces for all layers
- **Facades** - Application-specific facades for easy access

### 📁 Directory Structure
- `app/Actions/` - Business logic actions (User, Fortify)
- `app/Contracts/` - Interface definitions
- `app/Facades/` - Application facades
- `app/Repositories/` - Data access layer
- `app/Services/` - Business logic layer
- `resources/js/pages/` - Inertia page components
- `resources/js/components/` - Reusable Vue components (PublicHeader, PublicFooter, etc.)
- `resources/js/composables/` - Vue composition functions
- `resources/js/layouts/` - Layout components (PublicLayout, AuthLayout, etc.)
- `resources/js/types/` - TypeScript type definitions (pages, models, forms)

### 🛠️ Development Tools
- **Laravel Pint** - PHP code style fixer
- **Laravel Pail** - Real-time log viewer
- **Laravel Debugbar** - Development debugging toolbar
- **ESLint** - JavaScript/TypeScript linting
- **Prettier** - Code formatting
- **TypeScript ESLint** - TypeScript-specific linting rules
- **Concurrently** - Run multiple dev processes simultaneously

### 🚀 Development Experience
- **Automated Setup Script** - `composer run setup` for one-command installation
- **Concurrent Dev Server** - `composer run dev` runs server, queue, logs, and Vite together
- **SSR Support** - Server-side rendering for improved performance
- **Hot Module Replacement** - Instant frontend updates during development
- **TypeScript Support** - Full type checking and IntelliSense
- **Link Prefetching** - Automatic prefetching for faster navigation
- **Mobile-First Design** - Responsive design starting from mobile screens
- **Code Splitting** - Optimized bundle sizes with manual chunks

### 🧪 Testing
- **PHPUnit** - Comprehensive test suite
- **Feature Tests** - Full HTTP endpoint testing
- **Unit Tests** - Individual class and method testing
- **GitHub Actions CI** - Automated testing on push/PR

### 📦 Additional Packages
- **Laravel Wayfinder** - Route organization and type-safe route generation
- **Laravel Boost** - Enhanced development tools (MCP server)
- **Laravel Sail** - Docker development environment
- **Laravel Updater** - Application update system

### 🎯 Pre-built Features
- **Dashboard** - Starter dashboard page
- **User Settings** - Profile, password, and 2FA management pages
- **Authentication Pages** - Login, register, password reset, email verification
- **Cookie Preferences** - User-configurable cookie consent
- **Appearance Settings** - Theme switching (light/dark mode)
- **Privacy Policy** - GDPR-compliant privacy policy page
- **Cookie Policy** - Comprehensive cookie policy page
- **Public Layout Components** - Reusable header and footer components

### 🔧 Configuration
- **Fortify Configuration** - Pre-configured authentication features
- **Inertia Configuration** - SSR and shared data setup
- **Tailwind v4 Configuration** - Modern CSS setup with custom breakpoints
  - **xs**: < 768px (phones)
  - **sm**: >= 768px (tablets)
  - **md**: >= 992px (small laptops)
  - **lg**: >= 1200px (laptops and desktops)
- **TypeScript Configuration** - Strict type checking
- **ESLint Configuration** - Vue 3 + TypeScript rules
- **Prettier Configuration** - Consistent code formatting
- **Vite Build Optimization** - Code splitting and chunk optimization

### 📝 Code Quality
- **Strict TypeScript** - Type safety across frontend
- **PHP Type Hints** - Full type declarations in PHP 8.4
- **PHPDoc Blocks** - Comprehensive documentation for all classes and methods
- **Code Formatting** - Automated with Pint and Prettier
- **Linting** - ESLint for frontend, Pint for backend
- **Reusable Components** - DRY principle with shared components (PublicHeader, PublicFooter)
- **Type Definitions** - Complete TypeScript types for pages, models, and forms

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.4+** with required extensions
- **Composer** (latest version)
- **Node.js 18+** and npm
- **SQLite** (default) or MySQL/PostgreSQL
- **Git**

## 🛠️ Installation

### Quick Start

The fastest way to get started is using the setup script:

```bash
# Clone the repository
git clone <repository-url> your-project-name
cd your-project-name

# Run the automated setup script
composer run setup
```

This script will:
- Install PHP dependencies
- Copy `.env.example` to `.env` if it doesn't exist
- Generate application key
- Run migrations
- Install Node.js dependencies
- Build frontend assets

### Manual Installation

If you prefer to set up manually:

```bash
# Clone the repository
git clone <repository-url> your-project-name
cd your-project-name

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create database (SQLite)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Build assets
npm run build
```

### Development Setup

For development with hot reloading:

```bash
# Start all development services
composer run dev
```

This command runs:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite dev server with hot reload

### SSR Development

For Server-Side Rendering development:

```bash
# Build SSR assets and start development
composer run dev:ssr
```

## 🏗️ Project Structure

```
├── app/
│   ├── Actions/          # Business logic actions (User, Fortify)
│   ├── Contracts/        # Interfaces (Repository, Service, Action)
│   ├── Facades/          # Application facades
│   ├── Http/
│   │   ├── Controllers/  # Application controllers
│   │   ├── Middleware/   # Custom middleware
│   │   └── Requests/     # Form request validation
│   ├── Models/           # Eloquent models
│   ├── Providers/        # Service providers
│   ├── Repositories/     # Data access layer (Repository Pattern)
│   └── Services/         # Business logic layer (Service Pattern)
├── resources/
│   ├── js/
│   │   ├── components/   # Vue components (shadcn-vue)
│   │   ├── composables/  # Vue composables
│   │   ├── layouts/      # Inertia layouts
│   │   ├── pages/        # Inertia pages
│   │   └── types/        # TypeScript type definitions
│   └── css/              # Global styles
├── routes/
│   ├── web.php           # Web routes
│   └── settings.php      # Settings routes
└── tests/                # Feature and unit tests
```

## 🎯 Available Commands

### Development
```bash
# Start development server with hot reload
composer run dev

# Start development with SSR
composer run dev:ssr

# Run tests
composer run test
```

### Frontend
```bash
# Start Vite dev server
npm run dev

# Build for production
npm run build

# Build with SSR
npm run build:ssr

# Format code
npm run format

# Lint code
npm run lint
```

### Laravel
```bash
# Run migrations
php artisan migrate

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Generate application key
php artisan key:generate

# Format PHP code (Laravel Pint)
vendor/bin/pint

# Format only changed files
vendor/bin/pint --dirty
```

## 🏛️ Architecture

This starter kit follows a clean architecture pattern with clear separation of concerns:

### Repository Pattern
- **AbstractRepository**: Base repository with caching, CRUD operations, and query building
- **RepositoryInterface**: Contract defining repository methods
- Repositories handle all database interactions and provide a clean abstraction layer

### Service Pattern
- **AbstractService**: Base service with transaction support and validation helpers
- Services contain business logic and orchestrate repository operations
- Services can be easily tested and swapped without affecting controllers

### Action Pattern
- Actions encapsulate single, focused business operations
- Actions are reusable and can be composed to build complex workflows
- Used for user management, authentication, and other domain operations

### Layer Flow
```
Controllers → Services → Repositories → Models
     ↓
  Actions (when needed)
```

## 🎨 Tech Stack

### Backend
- **Laravel 12** - PHP framework
- **PHP 8.4+** - Programming language
- **Laravel Fortify** - Authentication scaffolding
- **Laravel Wayfinder** - Route organization
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

### Development Tools
- **Vite** - Build tool and dev server
- **ESLint** - Code linting
- **Prettier** - Code formatting
- **TypeScript ESLint** - TypeScript linting
- **Laravel Pint** - PHP code style fixer
- **Laravel Pail** - Real-time log viewer
- **Laravel Debugbar** - Development debugging toolbar

## 🔧 Configuration

### Environment Variables

Key environment variables to configure:

```env
APP_NAME="Your App Name"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

MAIL_MAILER=log
```

### Tailwind Configuration

The project uses Tailwind CSS 4 with custom configuration. Styles are located in `resources/css/app.css`.

### Component Library

shadcn-vue components are configured in `components.json`. Add new components using:

```bash
npx shadcn-vue@latest add [component-name]
```

### Repository & Service Pattern

When creating new features, follow the established architecture:

1. **Create Model**: `php artisan make:model ModelName -m`
2. **Create Repository**: Extend `AbstractRepository` in `app/Repositories/`
3. **Create Service**: Extend `AbstractService` in `app/Services/`
4. **Create Actions**: Implement `ActionInterface` in `app/Actions/`
5. **Create Controller**: Use services in controllers, not repositories directly

Example repository usage:
```php
// In a Service
$user = $this->repository->find($id);
$users = $this->repository->paginate(15);
```

Example service usage:
```php
// In a Controller
$user = app(UserService::class)->getUser($id);
```

## 🧪 Testing

This project uses PHPUnit for testing. All tests are located in the `tests/` directory.

### Running Tests

```bash
# Run all tests
composer run test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run specific test by name
php artisan test --filter=TestName

# Run with coverage (if configured)
php artisan test --coverage
```

### Test Structure
- **Feature Tests**: Located in `tests/Feature/` - Test complete features and HTTP endpoints
- **Unit Tests**: Located in `tests/Unit/` - Test individual classes and methods

### CI/CD
The project includes a GitHub Actions workflow (`.github/workflows/ci.yml`) that:
- Runs tests on PHP 8.4
- Validates composer.json
- Ensures code quality on every push and pull request

## 📚 Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [Vue 3 Documentation](https://vuejs.org/guide/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [shadcn-vue Documentation](https://www.shadcn-vue.com/)

## 🤝 Contributing

Thank you for considering contributing to this starter kit! Please review our contribution guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

- [Laravel](https://laravel.com/) for the amazing PHP framework
- [Vue.js](https://vuejs.org/) for the progressive JavaScript framework
- [Inertia.js](https://inertiajs.com/) for bridging Laravel and Vue
- [Tailwind CSS](https://tailwindcss.com/) for the utility-first CSS framework
- [shadcn-vue](https://www.shadcn-vue.com/) for the beautiful component library
