import { useCookieConsent } from '@/composables/useCookieConsent';

let initialized = false;

function injectGtmScript(gtmId: string): void {
    if (typeof window === 'undefined') {
        return;
    }

    const w = window as any;
    w.dataLayer = w.dataLayer || [];
    w.dataLayer.push({
        'gtm.start': new Date().getTime(),
        event: 'gtm.js',
    });

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtm.js?id=${gtmId}`;
    document.head.appendChild(script);
}

/**
 * Push a custom event to the GTM data layer.
 */
export function pushEvent(
    event: string,
    data: Record<string, unknown> = {},
): void {
    if (typeof window === 'undefined') {
        return;
    }

    const w = window as any;
    w.dataLayer = w.dataLayer || [];
    w.dataLayer.push({ event, ...data });
}

/**
 * Initialize Google Tag Manager, gated by analytics cookie consent.
 * Uses a module-level singleton to prevent double-injection.
 */
export function initialize(gtmId: string): void {
    if (!gtmId || initialized || typeof window === 'undefined') {
        return;
    }

    const { isCategoryAllowed } = useCookieConsent();

    if (isCategoryAllowed('analytics')) {
        initialized = true;
        injectGtmScript(gtmId);
        return;
    }

    const handler = (): void => {
        if (!initialized && isCategoryAllowed('analytics')) {
            initialized = true;
            injectGtmScript(gtmId);
        }
        window.removeEventListener('cookieConsentGiven', handler);
    };

    window.addEventListener('cookieConsentGiven', handler);
}

export function useGoogleTagManager(): {
    initialize: (gtmId: string) => void;
    pushEvent: (event: string, data?: Record<string, unknown>) => void;
} {
    return { initialize, pushEvent };
}
