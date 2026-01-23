# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added (2026-01-23 Session 8)
- **Toast notification system** - Modern toast notifications using vue-sonner:
  - Toaster component with automatic dark mode support
  - useToast composable with success, error, info, warning, loading, promise methods
  - Automatic flash message handling from Laravel backend
  - Integrated globally via AppShell component

- **AlertDialog component suite** - Full alert/confirmation dialog system:
  - 11 AlertDialog components for building custom dialogs
  - ConfirmDialog wrapper for quick confirmation modals
  - Support for default and destructive action variants

- **Empty state components**:
  - EmptyState component with customizable icon, title, description, and actions
  - SearchEmptyState for "no results" scenarios
  - Applied to Users index page with proper empty state handling

- **Skeleton loader components**:
  - ListItemSkeleton for list views
  - CardSkeleton for card grids
  - TableSkeleton for table views
  - FormSkeleton for form loading states

- **Loading components**:
  - LoadingSpinner with size variants (sm, md, lg)
  - LoadingState combining spinner with custom message

- **Reusable form components**:
  - FormField for text/number inputs with labels and errors
  - FormTextarea for multi-line text input
  - FormCheckbox with label and description support
  - FormSelect with options array
  - Textarea UI component (base component)
  - All with proper ARIA attributes and error handling

- **Enhanced error handling**:
  - ErrorBoundary component for catching component errors
  - ErrorAlert component for inline error/warning display
  - useErrorHandler composable with error handling utilities

### Added (2026-01-23 Session 7)
- **ImpersonationService** - Dedicated service for user impersonation:
  - Created `app/Services/ImpersonationService.php` with comprehensive impersonation logic
  - Methods: `canImpersonate()`, `startImpersonation()`, `stopImpersonation()`, `isImpersonating()`, `getImpersonator()`, `getUsersForImpersonation()`, `searchUsers()`
  - Handles session management, authorization checks, and audit logging
  - Proper error handling for edge cases (deleted impersonators, invalid sessions)
  - Refactored ImpersonateController to use service layer
  - All existing tests passing (8 tests, 64 assertions)

- **SettingsService** - Business logic layer for application settings:
  - Created `app/Services/SettingsService.php` extending AbstractService
  - Full CRUD operations with validation and transaction support
  - Methods: `get()`, `set()`, `has()`, `create()`, `update()`, `delete()`, `search()`, `getByRole()`, `getMultiple()`, `setMultiple()`
  - System setting protection (prevents deletion of system-level settings)
  - Refactored SettingsController to use service layer
  - Maintains all existing functionality while improving code organization

### Changed (2026-01-23 Session 7)
- **ImpersonateController** - Refactored to use ImpersonationService:
  - Changed from direct UserService dependency to ImpersonationService
  - Simplified controller methods to focus on HTTP concerns
  - Business logic now properly encapsulated in service layer

- **SettingsController** - Refactored to use SettingsService:
  - Changed from direct repository access to service layer
  - Constructor injection of SettingsService
  - All CRUD operations now use service methods
  - Improved separation of concerns

### Added (2026-01-23 Session 6)
- **FormErrors component** for displaying general form-level errors:
  - Created reusable FormErrors.vue component to display non-field-specific errors
  - Supports both single error and multiple errors display
  - Uses AlertCircle icon from lucide-vue-next
  - Added to Roles and Database index pages for better error visibility
  - Handles both string and array error formats

- **Role and Permission Setup**:
  - Created super-admin role with all permissions (view users, create users, edit users, delete users)
  - Created user role with limited permissions (view users only)
  - Assigned super-admin role to user ID 1
  - Configured proper Spatie Permission architecture

### Changed (2026-01-23 Session 6)
- **Admin sidebar active state** - Enhanced `urlIsActive` function to support child routes:
  - Now highlights parent menu item when on child routes (e.g., Users menu active when editing a user)
  - Uses `startsWith` logic to match nested routes
  - Improves navigation clarity and user orientation

- **Back buttons preserve pagination** - Changed from Link to history.back():
  - Roles edit page back button now uses browser history
  - Permissions edit page back button now uses browser history
  - Settings edit page back button now uses browser history
  - Maintains pagination state when navigating back from detail pages

- **Breadcrumb consistency** - Fixed hardcoded URL in Database/Show.vue:
  - Changed from hardcoded `/admin/databases` to using route helper `databasesIndex().url`
  - Ensures consistency across all breadcrumb implementations

### Fixed (2026-01-23 Session 6)
- **Form validation error display** - General form errors now visible:
  - Added FormErrors component to display role_deletion and other non-field errors
  - Errors like "cannot delete role assigned to users" now shown to users
  - Previously these errors were sent back but never displayed

- **Dark mode inconsistencies** - Comprehensive audit and fixes across 26+ instances:
  - Auth pages (Login, ForgotPassword, VerifyEmail): Added dark:text-green-500 to success messages
  - All form success messages: Added dark:text-neutral-400 to "Saved" messages
  - Cookie preferences page: Added dark variants to consent status indicators (green/red text)
  - Admin dashboard: Added dark variants to all quick link icon backgrounds (blue, green, purple, orange, gray)
  - Profile page: Added dark:text-green-500 to verification link sent message

- **Mobile navigation overflow** - Verified proper overflow handling:
  - SidebarContent has overflow-auto with flex-1 for proper scrolling
  - Mobile Sheet component has h-full with flex-col layout
  - Sidebar scrolls correctly when navigation items exceed viewport height

- **Table pagination state** - Back buttons now preserve pagination:
  - Using window.history.back() instead of direct links to index pages
  - Users return to the same page number they were on before viewing details
  - Fixes issue where back button always returned to page 1

### Added (2026-01-22 Session 5)
- **Back button on edit pages** for improved navigation:
  - Added "Back to Roles" button on Roles edit page with ArrowLeft icon
  - Added "Back to Permissions" button on Permissions edit page with ArrowLeft icon
  - Added "Back to Settings" button on Settings edit page with ArrowLeft icon
  - Positioned at top of page for easy access
  - Styled with muted foreground color that transitions to foreground on hover
- **Tooltips on admin settings page** using shadcn-vue Tooltip components:
  - Role badge tooltips explain each role type (System, User, Plugin)
  - Edit link tooltip: "Modify this setting's value and configuration"
  - Delete button tooltip explains why system settings can't be deleted
  - Improves UX by clarifying purpose of UI elements
  - Added cursor-help styling to indicate tooltip availability

### Changed (2026-01-22 Session 5)
- **Improved navigation UX** on admin edit pages with dedicated back buttons
- **Enhanced admin settings page** with contextual help via tooltips

### Fixed (2026-01-22 Session 5)
- **Role deletion confirmation dialog** - Fixed incorrect import of non-existent `AlertDialog` component, now correctly uses `Dialog` component

### Added (2026-01-22 Session 4)
- **EnsureUserExists middleware** for handling deleted user sessions:
  - Checks if authenticated user still exists in database on every request
  - Automatically logs out users whose accounts have been deleted
  - Handles impersonation scenarios - restores original user if available
  - Prevents errors when accessing routes with deleted user accounts
  - Registered in web middleware stack
- **User model boot method** for cleanup on deletion:
  - Automatically detaches all role assignments when user is deleted
  - Automatically detaches all permission assignments when user is deleted
  - Prevents foreign key constraint violations
- **Role assignment check before deletion**:
  - Counts users assigned to role before allowing deletion
  - Shows detailed error message with user count if role is in use
  - Prevents accidental deletion of roles that would affect users
- **Persistent guest cookie consent**:
  - Guest preferences now stored in browser cookie (365 days lifetime)
  - Preferences persist across browser sessions and device restarts
  - Backwards compatible - checks session first, then browser cookie
  - Configurable cookie lifetime via `cookie.storage.lifetime`

### Fixed (2026-01-22 Session 4)
- **Deleted user session handling** - Users with deleted accounts are now properly logged out instead of causing errors
- **Impersonation with deleted users** - Gracefully handles when impersonated user is deleted during session
- **User deletion failures** - User deletion now properly cleans up role and permission assignments
- **Role deletion data loss** - Cannot delete roles that are assigned to users (prevents accidental data loss)
- **Guest cookie consent loss** - Guest cookie preferences now persist across sessions instead of being lost

### Security (2026-01-22 Session 4)
- Added middleware to detect and handle deleted user accounts
- Improved impersonation security by handling edge cases with deleted users
- Audit logs maintained even when users are deleted (user_id set to null via nullOnDelete)

### Added (2026-01-22 Session 3)
- **Empty states for database tables**:
  - Enhanced `Data.vue` with Database icon and "No data in this table" message showing table name
  - Enhanced `Database/Index.vue` with search-aware empty states (shows Database icon when empty, Search icon when no results)
  - Improved visual hierarchy with better spacing and typography
- **Search debouncing across admin panels** (300ms delay):
  - Added to Users index page - debounced user search
  - Added to Roles index page - debounced role search
  - Added to Permissions index page - debounced permission search
  - Added to Settings page - debounced settings search
  - Uses `useDebounceFn` from @vueuse/core for consistent behavior
  - Removed manual "Search" buttons as search now triggers automatically
- **Copy to clipboard for 2FA recovery codes**:
  - Added "Copy Codes" button in `TwoFactorRecoveryCodes` component
  - Copies all recovery codes as newline-separated text to clipboard
  - Visual feedback with icon change (Copy → Check) and text change ("Copy Codes" → "Copied!")
  - Auto-resets after 2 seconds
  - Only visible when recovery codes are displayed
- **Loading spinner for impersonation**:
  - Added loading state tracking in `ImpersonateModal` component
  - Added loading state tracking in `Impersonate/Index.vue` page
  - Enhanced `UserCard` component with `Loader2` spinner from lucide-vue-next
  - Shows animated spinner on clicked user during impersonation
  - Disables button and shows reduced opacity during loading
- **Dialog confirmation for role deletion**:
  - Replaced browser's `confirm()` with shadcn-vue `Dialog` component
  - Proper modal UI with "Cancel" and "Delete" buttons
  - Delete button styled with destructive variant (red)
  - Shows role name in confirmation message
  - Cannot be dismissed accidentally by clicking outside

### Changed (2026-01-22 Session 3)
- **Settings validation moved to Form Request** - Replaced inline validation in `SettingsController::update()` with dedicated `SettingUpdateRequest`
- **Search inputs now auto-search** - Admin panel search inputs trigger search automatically after 300ms delay instead of requiring button click
- **Improved UX for role deletion** - More intuitive and harder to accidentally delete roles

### Fixed (2026-01-22 Session 3)
- **Settings unique key validation** - Now properly validates at application level using `Rule::unique()->ignore()` in Form Request
- **No loading feedback on impersonation** - Users now see clear loading state when impersonating another user
- **Poor empty states** - Database tables now show helpful, visually appealing empty states instead of generic messages

### Security (2026-01-22 Session 3)
- Added proper validation in Form Request for Settings updates (follows Laravel best practices)

### Added (2026-01-22 Session 2)
- **Audit logging system** for tracking sensitive actions:
  - Created `AuditLog` model with polymorphic relations to track any model changes
  - Created migration for `audit_logs` table with indexed columns (user_id, event, auditable, timestamps)
  - Static `log()` method for easy audit logging: `AuditLog::log('event.name', $model, $oldValues, $newValues)`
  - Automatic IP address and user agent tracking
  - JSON storage for old/new values for detailed change tracking
- **Audit logging for impersonation events**:
  - Logs `impersonation.started` when admin starts impersonating a user
  - Logs `impersonation.stopped` when impersonation session ends
  - Includes impersonated user details and impersonator ID
- **Database connection testing** in `DatabaseController`:
  - Added `testConnection()` private method to validate connections before use
  - Returns success/error status with driver information
  - Prevents errors by testing connections before attempting to list tables
  - Redirects with error message if connection fails

### Changed (2026-01-22 Session 2)
- **Enhanced impersonation security** with rate limiting (5 attempts per minute via throttle middleware)
- **Improved database browser reliability** with connection testing before operations

### Removed (2026-01-22 Session 2)
- **Unused RegistrationController** (`app/Http/Controllers/Settings/RegistrationController.php`)
- **Unused RegistrationSettingRequest** (`app/Http/Requests/Settings/RegistrationSettingRequest.php`)
- Registration setting is now managed exclusively through Admin Settings panel

### Fixed (2026-01-22 Session 2)
- **Impersonation abuse prevention** - Rate limiting prevents rapid impersonation attempts
- **Database connection failures** - Now properly tested and handled before attempting operations

### Security (2026-01-22 Session 2)
- Added comprehensive audit trail for impersonation events
- Rate limiting on impersonation to prevent abuse (5 per minute)
- Connection validation prevents exposure of database connection errors
- Audit logs track IP address and user agent for security investigations

### Added (2026-01-22 Session 1)
- **About page** (`resources/js/pages/About.vue`) with comprehensive information about the starter kit
  - Mission statement and features overview
  - Technology stack breakdown (backend & frontend)
  - Key features explanation with examples
  - Call-to-action links for authenticated and guest users
- **20 comprehensive default settings** in `SettingsSeeder.php`:
  - Authentication settings (registration, email verification, 2FA requirements)
  - Site configuration (name, description, contact email)
  - Security settings (session timeout, password expiry, max login attempts)
  - Feature flags (maintenance mode, impersonation, database browser)
  - Email settings (welcome emails, admin notifications)
  - User settings (timezone, items per page)
  - GDPR settings (cookie consent requirement, data retention period)
- **Email configuration check** in `FortifyServiceProvider`:
  - `isEmailConfigured()` method to validate email setup
  - Prevents password reset when email is not configured
  - Shows alert on email verification page when email not available
- **Encrypted casts** for 2FA sensitive data in User model:
  - `two_factor_secret` now explicitly encrypted
  - `two_factor_recovery_codes` now explicitly encrypted
- **Quick Wins section** in TODO.md for easy-to-implement improvements
- **Progress tracking** in TODO.md with completion statistics

### Added (Previous)
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

### Changed (2026-01-22 Session 1)
- **Registration now enabled by default** (changed `registration_enabled` from '0' to '1')
- **Password reset link** now hidden on login page when email is not configured
- **Email verification resend button** disabled when email is not configured
- **TODO.md structure** improved with Recent Progress section and Quick Wins

### Changed (Previous)
- Standardized container widths across all pages (max-w-sm → sm:max-w-2xl → md:max-w-4xl → lg:max-w-6xl)
- Updated auth layout to include header and footer matching public pages
- Improved dark mode text visibility on Privacy Policy and Cookie Policy pages
- Refactored Welcome, Privacy Policy, and Cookie Policy pages to use reusable components
- Optimized Vite configuration for better build performance

### Fixed (2026-01-22 Session 1)
- **Missing About page** - Controller existed but page was not implemented
- **Incomplete SettingsSeeder** - Only had one setting (registration_enabled), now has 20 comprehensive defaults
- **No email validation** before password reset - Now checks if email is configured before showing password reset
- **2FA recovery codes not explicitly encrypted** - Added encrypted casts to User model

### Fixed (Previous)
- Dark mode text visibility issues on policy pages
- Container width inconsistencies across different screen sizes
- Missing prefetch attributes on navigation links

### Security (2026-01-22 Session 1)
- Added explicit encryption for `two_factor_secret` and `two_factor_recovery_codes` fields
- Enhanced email configuration validation prevents password reset attempts when email is misconfigured
- Improved security warnings on email verification page when email is unavailable

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

