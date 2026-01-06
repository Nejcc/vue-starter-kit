import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

/**
 * Base props shared across all authenticated pages.
 *
 * These props are automatically provided by the HandleInertiaRequests middleware
 * and are available on all pages that use AppLayout.
 */
export interface AppPageProps {
    /** The current page name */
    name: string;
    /** Daily quote for inspiration */
    quote: { message: string; author: string };
    /** Authentication information including current user */
    auth: Auth;
    /** Whether the sidebar is open */
    sidebarOpen: boolean;
}

/**
 * User model interface matching Laravel User model.
 */
export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    two_factor_confirmed_at?: string | null;
    cookie_consent_preferences?: Record<string, boolean> | null;
    cookie_consent_given_at?: string | null;
    data_processing_consent?: boolean;
    data_processing_consent_given_at?: string | null;
    gdpr_ip_address?: string | null;
    roles?: string[];
}

export type BreadcrumbItemType = BreadcrumbItem;

// Re-export page types for convenience
export type {
    DashboardPageProps,
    WelcomePageProps,
    LoginPageProps,
    RegisterPageProps,
    ForgotPasswordPageProps,
    ResetPasswordPageProps,
    VerifyEmailPageProps,
    ConfirmPasswordPageProps,
    TwoFactorChallengePageProps,
    ProfilePageProps,
    PasswordPageProps,
    TwoFactorPageProps,
    CookiePreferencesPageProps,
    AppearancePageProps,
    RegistrationPageProps,
} from './pages';

// Re-export model types
export type {
    PaginatedResponse,
    Paginator,
} from './models';

// Re-export form types
export type {
    ValidationErrors,
    FormErrors,
    FormState,
    FormData,
    ProfileUpdateFormData,
    PasswordUpdateFormData,
    CookieConsentFormData,
} from './forms';
