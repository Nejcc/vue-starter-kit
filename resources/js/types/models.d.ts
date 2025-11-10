/**
 * TypeScript interfaces matching Laravel Eloquent models.
 */

/**
 * User model interface.
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
}

/**
 * Pagination response interface.
 */
export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

/**
 * Laravel paginator interface.
 */
export interface Paginator<T> extends PaginatedResponse<T> {
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

