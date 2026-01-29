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
export type { PaginatedResponse, Paginator } from './models';

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

export interface InstalledModules {
    payments: boolean;
    subscribers: boolean;
    horizon: boolean;
}

export interface AppPageProps {
    name: string;
    auth: Auth;
    sidebarOpen: boolean;
    modules: InstalledModules;
}
