import type { AppPageProps } from './index';

/**
 * Dashboard page props.
 */
export type DashboardPageProps = AppPageProps;

/**
 * Welcome page props.
 */
export interface WelcomePageProps {
    canRegister: boolean;
}

/**
 * Login page props.
 */
export interface LoginPageProps {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}

/**
 * Register page props.
 */
export interface RegisterPageProps {
    status?: string;
}

/**
 * Forgot password page props.
 */
export interface ForgotPasswordPageProps {
    status?: string;
}

/**
 * Reset password page props.
 */
export interface ResetPasswordPageProps {
    email: string;
    token: string;
}

/**
 * Verify email page props.
 */
export interface VerifyEmailPageProps {
    status?: string;
}

/**
 * Confirm password page props.
 */
export interface ConfirmPasswordPageProps {
    status?: string;
}

/**
 * Two factor challenge page props.
 */
export interface TwoFactorChallengePageProps {
    recovery?: boolean;
}

/**
 * Two factor settings page props.
 */
export interface TwoFactorSettingsPageProps extends AppPageProps {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

/**
 * Profile settings page props.
 */
export interface ProfilePageProps extends AppPageProps {
    mustVerifyEmail: boolean;
    status?: string;
}

/**
 * Password settings page props.
 */
export interface PasswordPageProps extends AppPageProps {
    status?: string;
}

/**
 * Two factor authentication settings page props.
 */
export interface TwoFactorPageProps extends AppPageProps {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

/**
 * Cookie preferences page props.
 */
export interface CookiePreferencesPageProps extends AppPageProps {
    cookieConsent?: {
        hasConsent: boolean;
        preferences: Record<string, boolean>;
        categories: Record<string, unknown>;
        config?: unknown;
    };
}

/**
 * Appearance settings page props.
 */
export type AppearancePageProps = AppPageProps;

