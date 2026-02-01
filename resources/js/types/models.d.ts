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

/**
 * Organization model interface.
 */
export interface Organization {
    id: number;
    uuid: string;
    name: string;
    slug: string;
    description: string | null;
    owner_id: number | null;
    is_personal: boolean;
    logo_path: string | null;
    metadata: Record<string, unknown> | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    owner?: User;
    members?: OrganizationMember[];
    members_count?: number;
}

/**
 * Organization member (pivot) interface.
 */
export interface OrganizationMember {
    id: number;
    organization_id: number;
    user_id: number;
    role: string;
    joined_at: string;
    created_at: string;
    updated_at: string;
    user?: User;
    pivot?: {
        role: string;
        joined_at: string;
    };
}

/**
 * Organization invitation interface.
 */
export interface OrganizationInvitation {
    id: number;
    uuid: string;
    organization_id: number;
    email: string;
    role: string;
    token: string;
    invited_by: number | null;
    accepted_at: string | null;
    declined_at: string | null;
    expires_at: string;
    created_at: string;
    updated_at: string;
    organization?: Organization;
}

/**
 * Database notification interface.
 */
export interface DatabaseNotification {
    id: string;
    type: string;
    data: {
        title: string;
        body: string;
        action_url?: string | null;
        icon?: string | null;
    };
    read_at: string | null;
    created_at: string;
    updated_at: string;
}
