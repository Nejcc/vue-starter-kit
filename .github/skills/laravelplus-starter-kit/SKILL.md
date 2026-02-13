---
name: laravelplus-starter-kit
description: >-
  Activate when working on the core starter kit application — adding
  controllers, services, repositories, actions, models, admin pages, settings
  pages, middleware, Inertia pages, Vue components, composables, or modifying
  the architecture, navigation, shared props, or authentication flow.
---

# LaravelPlus Starter Kit Development

## When to Apply

Activate when working on core application code outside of the three local packages:

- Creating or modifying controllers, services, repositories, actions, or models in `app/`
- Adding admin pages, settings pages, or public pages
- Modifying middleware pipeline, shared Inertia props, or navigation
- Working with authentication, authorization, impersonation, or audit logging
- Creating Vue pages, layouts, components, or composables in `resources/js/`
- Adding or modifying routes in `routes/web.php` or `routes/settings.php`
- Working with form requests, validation, or the service/repository contracts
- Registering new modules or sidebar navigation items

## Architecture Flow

```
Route → Controller → Form Request (validation) → Service → Repository → Model
                         ↓
                    Action (single-responsibility alternative)
```

Controllers never contain business logic or validation. Services handle transactions and business rules. Repositories handle data access. Actions are thin wrappers delegating to services for single operations.

## Backend Structure

### Contracts & Dependency Injection

All services and repositories are bound via interfaces in providers:

<code-snippet name="Service Registration Pattern" lang="php">
// AppServiceProvider.php — service bindings
UserServiceInterface::class => UserService::class
RoleServiceInterface::class => RoleService::class
PermissionServiceInterface::class => PermissionService::class
AuditLogServiceInterface::class => AuditLogService::class
ImpersonationServiceInterface::class => ImpersonationService::class
NotificationServiceInterface::class => NotificationService::class

// RepositoryServiceProvider.php — repository bindings
UserRepositoryInterface::class => UserRepository::class
RoleRepositoryInterface::class => RoleRepository::class
PermissionRepositoryInterface::class => PermissionRepository::class
AuditLogRepositoryInterface::class => AuditLogRepository::class
</code-snippet>

### Base Classes

**BaseRepository** (`app/Repositories/BaseRepository.php`):
- Generic abstract class with `TModel` template type
- Provides: `query()`, `find()`, `findOrFail()`, `findBy()`, `findAllBy()`, `create()`, `update()`, `delete()`, `all()`, `paginate()`
- Constructor receives model class name string

**AbstractService** (`app/Services/AbstractService.php`):
- Generic abstract class with `T extends Model` template type
- Constructor accepts `RepositoryInterface`
- Provides: `transaction(Closure)`, `validate(array, array)`, `getRepository()`

**ActionInterface** (`app/Contracts/Actions/ActionInterface.php`):
- Single method: `handle(mixed ...$parameters): mixed`

**AbstractFormRequest** (`app/Http/Requests/AbstractFormRequest.php`):
- Extends `FormRequest` with hooks: `authorize()`, `rules()`, `messages()`, `attributes()`, `withValidator()`, `afterValidation()`

### Creating New Services

<code-snippet name="New Service Pattern" lang="php">
// 1. Contract — app/Contracts/Services/FooServiceInterface.php
interface FooServiceInterface
{
    public function getAll(): Collection;
    public function create(array $data): Foo;
}

// 2. Service — app/Services/FooService.php
final class FooService extends AbstractService implements FooServiceInterface
{
    public function __construct(
        private readonly FooRepositoryInterface $fooRepository,
    ) {
        parent::__construct($this->fooRepository);
    }

    public function create(array $data): Foo
    {
        return $this->transaction(function () use ($data): Foo {
            $foo = $this->fooRepository->create($data);
            AuditLog::log('foo.created', $foo, null, $data);
            return $foo;
        });
    }
}

// 3. Bind in AppServiceProvider
$this->app->bind(FooServiceInterface::class, FooService::class);
</code-snippet>

### Creating New Repositories

<code-snippet name="New Repository Pattern" lang="php">
// 1. Contract — app/Contracts/Repositories/FooRepositoryInterface.php
interface FooRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug): ?Foo;
}

// 2. Repository — app/Repositories/FooRepository.php
final class FooRepository extends BaseRepository implements FooRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Foo::class);
    }

    public function findBySlug(string $slug): ?Foo
    {
        return $this->query()->where('slug', $slug)->first();
    }
}

// 3. Bind in RepositoryServiceProvider
FooRepositoryInterface::class => FooRepository::class,
</code-snippet>

### Controllers

Controllers inject service interfaces, never repositories directly:

<code-snippet name="Controller Pattern" lang="php">
final class FoosController extends Controller
{
    public function __construct(
        private readonly FooServiceInterface $fooService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('admin/Foos/Index', [
            'foos' => $this->fooService->getAll(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(StoreFooRequest $request): RedirectResponse
    {
        $this->fooService->create($request->validated());
        return redirect()->route('admin.foos.index')
            ->with('success', 'Foo created.');
    }
}
</code-snippet>

### Form Requests

All validation in dedicated Form Request classes extending `AbstractFormRequest`:

<code-snippet name="Form Request Pattern" lang="php">
final class StoreFooRequest extends AbstractFormRequest
{
    public function authorize(): bool
    {
        return true; // or $this->user()->can('create-foos')
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'unique:foos,slug'],
        ];
    }
}
</code-snippet>

### Models

- **User** — Traits: `HasFactory`, `Billable`, `HasRoles`, `Notifiable`, `TracksLastLogin`, `TwoFactorAuthenticatable`. Route key: `slug`. Has cookie consent, GDPR fields, and `last_login_at`.
- **AuditLog** — Polymorphic audit trail. Static helper: `AuditLog::log($event, $auditable, $oldValues, $newValues, $userId)`. Fields: event, auditable (morphTo), old_values, new_values, ip_address, user_agent.
- **Role** / **Permission** — Extend Spatie Permission models. Route key: `name`.

### Roles & Constants

<code-snippet name="Role Constants" lang="php">
// app/Constants/RoleNames.php
final class RoleNames
{
    public const SUPER_ADMIN = 'super-admin'; // has all permissions via Gate::before
    public const ADMIN = 'admin';
    public const USER = 'user';
}
</code-snippet>

### Audit Logging

Log significant operations with old/new values:

<code-snippet name="Audit Logging" lang="php">
AuditLog::log('user.created', $user, null, $user->toArray());
AuditLog::log('user.updated', $user, $oldValues, $newValues);
AuditLog::log('role.deleted', $role, $role->toArray(), null);
AuditLog::log('impersonation.started', $targetUser, null, [
    'impersonator_id' => $impersonator->id,
]);
</code-snippet>

## Middleware Pipeline

Registered in `bootstrap/app.php` (not Kernel.php — Laravel 12):

1. **SecurityHeaders** — X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, HSTS
2. **EnsureCookieConsent** — GDPR cookie consent management
3. **EnsureUserExists** — User validation
4. **HandleAppearance** — Dark mode / theme handling
5. **HandleInertiaRequests** — Shared props injection
6. **AddLinkHeadersForPreloadedAssets** — Link preloading

Route alias: `role` → `EnsureUserHasRole` (usage: `role:super-admin,admin`)

## Shared Inertia Props

`HandleInertiaRequests` provides these props to all pages:

<code-snippet name="Shared Props Structure" lang="typescript">
interface SharedProps {
    name: string;                       // app.name
    quote: { message: string; author: string };
    auth: {
        user: User & { roles: string[]; permissions: string[] } | null;
        isImpersonating: boolean;
        impersonator: { id: number; name: string; email: string } | null;
    };
    auth_layout: 'simple' | 'split';
    sidebarOpen: boolean;
    modules: InstalledModules;          // { globalSettings, payments, subscribers, horizon }
    moduleNavigation: ModuleNavGroupData[];
    notifications: { unreadCount: number };
    cookieConsent: { hasConsent, preferences, categories, config };
}
</code-snippet>

## Routes

### Web Routes (`routes/web.php`)

| Prefix | Middleware | Purpose |
|--------|-----------|---------|
| `/` | web | Welcome, public pages (about, privacy, cookie policy) |
| `/dashboard` | auth, verified | User dashboard |
| `/cookie-consent` | web | Cookie consent CRUD |
| `/impersonate` | auth | Start/stop impersonation |
| `/notifications` | auth | Notification management |
| `/admin/users` | auth, role:super-admin,admin | User CRUD |
| `/admin/roles` | auth, role:super-admin,admin | Role CRUD |
| `/admin/permissions` | auth, role:super-admin,admin | Permission CRUD |
| `/admin/audit-logs` | auth, role:super-admin,admin | Audit log viewer |
| `/admin/database` | auth, role:super-admin,admin | Database explorer |

### Settings Routes (`routes/settings.php`)

All require `auth` middleware:
- `GET|PATCH /settings/profile`, `DELETE /settings/profile`
- `GET|PUT /settings/password`
- `GET /settings/appearance`
- `GET /settings/two-factor`
- `GET /settings/cookie-preferences`

### Rate Limiting

Configured in `FortifyServiceProvider`:
- `login` — 5/minute per email+IP
- `register` — 5/minute per IP
- `two-factor` — 5/minute per session
- `impersonate` — 5/minute per user+IP

## Frontend Structure

### Page Organization

```
resources/js/pages/
├── Welcome.vue, Dashboard.vue, About.vue
├── CookiePolicy.vue, PrivacyPolicy.vue
├── auth/           — Login, Register, ForgotPassword, ResetPassword,
│                     VerifyEmail, ConfirmPassword, TwoFactorChallenge
├── settings/       — Profile, Password, Appearance, TwoFactor, CookiePreferences
├── notifications/  — Index
├── Impersonate/    — Index
└── admin/
    ├── Dashboard.vue, Settings.vue
    ├── Users/      — Index, Create, Edit
    ├── Roles/      — Index, Create, Edit
    ├── Permissions/ — Index, Create, Edit
    ├── AuditLogs/  — Index
    ├── Database/   — Index, Show (tabs: Structure, Indexes, Data, Actions)
    └── Databases/  — Index
```

### Layouts

| Layout | Usage |
|--------|-------|
| `AppLayout` → `AppSidebarLayout` | Authenticated user pages |
| `AppHeaderLayout` | Header-based variant |
| `AuthLayout` | Switches between `AuthSimpleLayout` and `AuthSplitLayout` via `auth_layout` prop |
| `AuthCardLayout` | Centered card auth layout |
| `PublicLayout` | Public pages with header + footer + cookie banner |
| `admin/AdminLayout` | Admin pages with sidebar + impersonation banner |
| `settings/Layout` | Settings with sidebar navigation |

### Key Components (non-UI)

| Component | Purpose |
|-----------|---------|
| `AdminSidebar` | Admin sidebar with module navigation groups |
| `NavMain` | Main navigation with collapsible groups |
| `NavUser` | User dropdown menu |
| `NavFooter` | Sidebar footer |
| `PublicHeader` / `PublicFooter` | Public page header/footer |
| `ImpersonationBanner` | Shows when impersonating |
| `ImpersonateModal` / `ImpersonateButton` | Impersonation UI |
| `NotificationDropdown` | Notification bell with dropdown |
| `CookieConsentBanner` | GDPR cookie consent |
| `ConfirmDialog` | Reusable confirmation dialog |
| `Breadcrumbs` | Page breadcrumbs from `BreadcrumbItem[]` |
| `Heading` | Page heading with title + description |
| `EmptyState` / `SearchEmptyState` | Empty/no-results states |
| `DeleteUser` | Account deletion with password confirmation |
| `TwoFactorSetupModal` / `TwoFactorRecoveryCodes` | 2FA setup UI |
| `AppearanceTabs` | Light/dark/system theme switcher |

### Composables

| Composable | Purpose |
|------------|---------|
| `useCookieConsent` | Cookie consent state, localStorage, server sync |
| `useErrorHandler` | Error handling with toast notifications |
| `useToast` | Toast notifications (success, error, info, warning, loading, promise) |
| `useInitials` | Extract initials from names |
| `useTwoFactorAuth` | 2FA setup data (QR, recovery codes, setup keys) |
| `useAppearance` | Theme management (light/dark/system) |
| `useCurrentUrl` | Current URL detection and comparison |

### Types

| File | Exports |
|------|---------|
| `types/index.ts` | Central barrel — re-exports all types, defines `InstalledModules`, `AppPageProps` |
| `types/auth.ts` | `User`, `Auth` types |
| `types/navigation.ts` | `BreadcrumbItem`, `NavItem`, `NavGroup`, `ModuleNavGroupData` |
| `types/ui.ts` | UI-related types |
| `types/models.d.ts` | `DatabaseNotification`, `Paginator`, `PaginatedResponse` |
| `types/forms.d.ts` | `FormData`, `FormErrors`, `FormState`, `ValidationErrors` |
| `types/pages.d.ts` | Page component prop types |

### Utils

- `utils/iconMap.ts` — Maps string icon names to Lucide Vue components. Use `resolveIcon(name)` for dynamic icon rendering (used by `AdminSidebar` and `NavMain` for module navigation).

## Module Navigation System

Packages register admin sidebar items via `AdminNavigation` singleton:

<code-snippet name="Register Module Navigation" lang="php">
// In a service provider's boot method:
$this->callAfterResolving(AdminNavigation::class, function (AdminNavigation $nav): void {
    $nav->register(
        'module-key',       // unique key
        'Module Title',     // display title
        'IconName',         // Lucide icon name (resolved via iconMap.ts)
        [
            ['title' => 'Dashboard', 'href' => '/admin/module', 'icon' => 'LayoutDashboard'],
            ['title' => 'Items', 'href' => '/admin/module/items', 'icon' => 'List'],
        ],
        10                  // priority (lower = higher in sidebar)
    );
});
</code-snippet>

The `HandleInertiaRequests` middleware serializes registered navigation into `moduleNavigation` shared prop. The `AdminSidebar` component renders these groups with icons resolved from `iconMap.ts`.

## Authentication & Authorization

- **Fortify** handles login, registration, password reset, email verification, 2FA (TOTP)
- **Spatie Permission** provides RBAC with `super-admin` (all permissions via `Gate::before`), `admin`, `user` roles
- **Impersonation** stores `impersonator_id` in session — all actions are audited
- Admin routes use `role:super-admin,admin` middleware alias

## GDPR & Cookie Consent

- User model tracks `cookie_consent_preferences` (array), `cookie_consent_given_at`, `data_processing_consent`, `data_processing_consent_given_at`, `gdpr_ip_address`
- `CookieConsentController` handles accept/reject/custom preferences for both authenticated and guest users
- `EnsureCookieConsent` middleware enforces consent
- `useCookieConsent` composable manages client-side state with localStorage

## Common Pitfalls

- Never put validation in controllers — always use Form Request classes extending `AbstractFormRequest`
- Never inject repositories into controllers — inject service interfaces only
- `super-admin` role cannot be created, renamed, or deleted via the admin UI — this is enforced in `RoleService`
- User `slug` is auto-generated from `name` in the model's `boot()` method — don't set it manually
- Always wrap multi-step operations in `$this->transaction()` in services
- New admin modules must register navigation via `AdminNavigation` and add their icon to `iconMap.ts`
- Shared props structure is in `HandleInertiaRequests` — check `InstalledModules` type when adding new module flags
