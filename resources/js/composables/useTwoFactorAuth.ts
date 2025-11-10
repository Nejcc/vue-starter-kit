import { qrCode, recoveryCodes, secretKey } from '@/routes/two-factor';
import { computed, ref, type Ref, type ComputedRef } from 'vue';

/**
 * Return type for useTwoFactorAuth composable.
 */
export interface UseTwoFactorAuthReturn {
    /** QR code SVG for 2FA setup */
    qrCodeSvg: Ref<string | null>;
    /** Manual setup key for 2FA */
    manualSetupKey: Ref<string | null>;
    /** List of recovery codes */
    recoveryCodesList: Ref<string[]>;
    /** List of errors */
    errors: Ref<string[]>;
    /** Whether setup data is available */
    hasSetupData: ComputedRef<boolean>;
    /** Clear setup data */
    clearSetupData: () => void;
    /** Clear errors */
    clearErrors: () => void;
    /** Clear all 2FA data */
    clearTwoFactorAuthData: () => void;
    /** Fetch QR code */
    fetchQrCode: () => Promise<void>;
    /** Fetch setup key */
    fetchSetupKey: () => Promise<void>;
    /** Fetch all setup data */
    fetchSetupData: () => Promise<void>;
    /** Fetch recovery codes */
    fetchRecoveryCodes: () => Promise<void>;
}

const fetchJson = async <T>(url: string): Promise<T> => {
    const response = await fetch(url, {
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error(`Failed to fetch: ${response.status}`);
    }

    return response.json();
};

const errors = ref<string[]>([]);
const manualSetupKey = ref<string | null>(null);
const qrCodeSvg = ref<string | null>(null);
const recoveryCodesList = ref<string[]>([]);

const hasSetupData = computed<boolean>(
    () => qrCodeSvg.value !== null && manualSetupKey.value !== null,
);

/**
 * Composable for managing two-factor authentication setup and data.
 *
 * @returns UseTwoFactorAuthReturn Object containing 2FA state and methods
 */
export const useTwoFactorAuth = (): UseTwoFactorAuthReturn => {
    const fetchQrCode = async (): Promise<void> => {
        try {
            const { svg } = await fetchJson<{ svg: string; url: string }>(
                qrCode.url(),
            );

            qrCodeSvg.value = svg;
        } catch {
            errors.value.push('Failed to fetch QR code');
            qrCodeSvg.value = null;
        }
    };

    const fetchSetupKey = async (): Promise<void> => {
        try {
            const { secretKey: key } = await fetchJson<{ secretKey: string }>(
                secretKey.url(),
            );

            manualSetupKey.value = key;
        } catch {
            errors.value.push('Failed to fetch a setup key');
            manualSetupKey.value = null;
        }
    };

    const clearSetupData = (): void => {
        manualSetupKey.value = null;
        qrCodeSvg.value = null;
        clearErrors();
    };

    const clearErrors = (): void => {
        errors.value = [];
    };

    const clearTwoFactorAuthData = (): void => {
        clearSetupData();
        clearErrors();
        recoveryCodesList.value = [];
    };

    const fetchRecoveryCodes = async (): Promise<void> => {
        try {
            clearErrors();
            recoveryCodesList.value = await fetchJson<string[]>(
                recoveryCodes.url(),
            );
        } catch {
            errors.value.push('Failed to fetch recovery codes');
            recoveryCodesList.value = [];
        }
    };

    const fetchSetupData = async (): Promise<void> => {
        try {
            clearErrors();
            await Promise.all([fetchQrCode(), fetchSetupKey()]);
        } catch {
            qrCodeSvg.value = null;
            manualSetupKey.value = null;
        }
    };

    return {
        qrCodeSvg,
        manualSetupKey,
        recoveryCodesList,
        errors,
        hasSetupData,
        clearSetupData,
        clearErrors,
        clearTwoFactorAuthData,
        fetchQrCode,
        fetchSetupKey,
        fetchSetupData,
        fetchRecoveryCodes,
    };
};
