import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    const url = toUrl(urlToCheck);

    // Exact match
    if (url === currentUrl) {
        return true;
    }

    // Check if current URL is a child route of the checked URL
    // e.g., /admin/users matches /admin/users/1/edit
    const normalizedUrl = url.endsWith('/') ? url : url + '/';
    const normalizedCurrentUrl = currentUrl.endsWith('/')
        ? currentUrl
        : currentUrl + '/';

    return normalizedCurrentUrl.startsWith(normalizedUrl);
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
