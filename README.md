# Laravel + Vue Starter Kit

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

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.4+** with required extensions
- **Composer** (latest version)
- **Node.js 18+** and npm
- **SQLite** (default) or MySQL/PostgreSQL
- **Git**

## 🛠️ Installation

### Quick Start

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
│   ├── Actions/          # Fortify authentication actions
│   ├── Http/
│   │   ├── Controllers/  # Application controllers
│   │   ├── Middleware/   # Custom middleware
│   │   └── Requests/     # Form request validation
│   ├── Models/           # Eloquent models
│   └── Providers/        # Service providers
├── resources/
│   ├── js/
│   │   ├── components/   # Vue components
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

# Generate application key
php artisan key:generate
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

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
composer run test

# Run specific test
php artisan test --filter=TestName
```

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
