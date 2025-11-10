/**
 * Form-related type definitions.
 */

/**
 * Validation error structure from Laravel.
 */
export interface ValidationErrors {
    [key: string]: string[];
}

/**
 * Form error response.
 */
export interface FormErrors {
    message: string;
    errors: ValidationErrors;
}

/**
 * Form state interface.
 */
export interface FormState {
    processing: boolean;
    progress: number | null;
    wasSuccessful: boolean;
    recentlySuccessful: boolean;
    errors: ValidationErrors;
    hasErrors: boolean;
}

/**
 * Generic form data structure.
 */
export interface FormData {
    [key: string]: unknown;
}

/**
 * Profile update form data.
 */
export interface ProfileUpdateFormData {
    name: string;
    email: string;
}

/**
 * Password update form data.
 */
export interface PasswordUpdateFormData {
    current_password: string;
    password: string;
    password_confirmation: string;
}

/**
 * Cookie consent form data.
 */
export interface CookieConsentFormData {
    preferences: Record<string, boolean>;
}

