export * from './auth';
export * from './navigation';
export * from './ui';

// Re-export page types from pages.d.ts
export type {
    AppearancePageProps,
    ConfirmPasswordPageProps,
    CookiePreferencesPageProps,
    DashboardPageProps,
    ForgotPasswordPageProps,
    LoginPageProps,
    NotificationsPageProps,
    PasswordPageProps,
    ProfilePageProps,
    RegisterPageProps,
    RegistrationPageProps,
    ResetPasswordPageProps,
    TwoFactorChallengePageProps,
    TwoFactorPageProps,
    VerifyEmailPageProps,
    WelcomePageProps,
} from './pages';

// Re-export model types
export type {
    DatabaseNotification,
    Organization,
    OrganizationInvitation,
    OrganizationMember,
    PaginatedResponse,
    Paginator,
} from './models';

// Re-export form types
export type {
    CookieConsentFormData,
    FormData,
    FormErrors,
    FormState,
    PasswordUpdateFormData,
    ProfileUpdateFormData,
    ValidationErrors,
} from './forms';

import type { Auth } from './auth';
import type { ModuleNavGroupData } from './navigation';

export interface InstalledModules {
    globalSettings: boolean;
    payments: boolean;
    subscribers: boolean;
    horizon: boolean;
    organizations: boolean;
    localizations: boolean;
    ecommerce: boolean;
}

export interface SeoSharedProps {
    gtmId: string;
    defaultDescription: string;
    siteName: string;
}

export interface AppPageProps {
    name: string;
    auth: Auth;
    auth_layout: 'simple' | 'split';
    sidebarOpen: boolean;
    modules: InstalledModules;
    moduleNavigation: ModuleNavGroupData[];
    notifications: {
        unreadCount: number;
    };
    seo: SeoSharedProps;
}
