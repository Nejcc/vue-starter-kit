# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Privacy Policy and Cookie Policy pages with full GDPR-compliant content
- Reusable `PublicHeader` component with LaravelPlus branding
- Reusable `PublicFooter` component with copyright and policy links
- `PublicLayout` component for consistent public page layouts
- Custom Tailwind breakpoints (xs: <768px, sm: >=768px, md: >=992px, lg: >=1200px)
- Link prefetching across all navigation links for faster page transitions
- Mobile-first responsive design implementation
- Vite build optimizations with code splitting and manual chunks
- Comprehensive TypeScript type definitions for pages, models, and forms
- Comprehensive PHPDoc blocks for all repository, service, and action classes
- Unit tests for `AbstractRepository` and `AbstractService`
- Unit tests for all User actions (DeleteUser, UpdateUserPassword, UpdateUserProfile)
- Integration tests for Repository-Service-Controller flow
- Enhanced unit tests for `UserRepository` and `UserService` with edge cases

### Changed
- Standardized container widths across all pages (max-w-sm → sm:max-w-2xl → md:max-w-4xl → lg:max-w-6xl)
- Updated auth layout to include header and footer matching public pages
- Improved dark mode text visibility on Privacy Policy and Cookie Policy pages
- Refactored Welcome, Privacy Policy, and Cookie Policy pages to use reusable components
- Optimized Vite configuration for better build performance

### Fixed
- Dark mode text visibility issues on policy pages
- Container width inconsistencies across different screen sizes
- Missing prefetch attributes on navigation links

## [1.0.0] - 2025-01-XX (Initial Release)

### Added
- Laravel 12 with PHP 8.4+ support
- Vue 3 with Composition API and TypeScript
- Inertia.js v2 for seamless SPA experience
- Tailwind CSS v4 with custom configuration
- shadcn-vue component library with Reka UI
- Laravel Fortify for authentication
- Server-Side Rendering (SSR) support
- Repository Pattern with `AbstractRepository` and caching
- Service Pattern with `AbstractService` and transaction support
- Action Pattern for single-responsibility operations
- Two-Factor Authentication (2FA) with QR codes and recovery codes
- Email verification flow
- Password reset functionality
- GDPR-compliant cookie consent system
- Cookie preferences management page
- Appearance settings with theme switching (light/dark mode)
- User settings pages (Profile, Password, 2FA)
- Dashboard page
- Authentication pages (Login, Register, Forgot Password, Reset Password, Verify Email, Confirm Password, Two-Factor Challenge)
- Laravel Pint for PHP code formatting
- Laravel Pail for real-time log viewing
- Laravel Debugbar for development debugging
- ESLint and Prettier for code quality
- TypeScript ESLint for TypeScript-specific linting
- Laravel Wayfinder for route organization
- Laravel Boost for enhanced development tools
- Laravel Sail for Docker development environment
- Laravel Updater for application updates
- Comprehensive test suite with PHPUnit
- GitHub Actions CI for automated testing
- Automated setup script (`composer run setup`)
- Concurrent dev server script (`composer run dev`)
- TypeScript type definitions for Inertia pages
- Vue composables (useAppearance, useCookieConsent, useTwoFactorAuth, useInitials)

### Changed
- Base Laravel installation extended with Vue 3 frontend
- Replaced Laravel Mix with Vite
- Updated to Laravel 12 streamlined file structure

### Security
- GDPR-compliant cookie consent system
- Data processing consent on registration
- Secure password reset flow
- Two-factor authentication support

---

## Version History

- **1.0.0** - Initial release with full Laravel + Vue 3 + Inertia.js v2 stack
- **Unreleased** - Privacy/Cookie Policy pages, reusable components, optimizations

