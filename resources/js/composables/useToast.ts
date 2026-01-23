import { toast as sonnerToast } from 'vue-sonner';

export interface ToastOptions {
    description?: string;
    action?: {
        label: string;
        onClick: () => void;
    };
    cancel?: {
        label: string;
        onClick?: () => void;
    };
    duration?: number;
    important?: boolean;
}

export function useToast() {
    const toast = {
        /**
         * Show a success toast notification
         */
        success: (message: string, options?: ToastOptions) => {
            return sonnerToast.success(message, {
                description: options?.description,
                action: options?.action,
                cancel: options?.cancel,
                duration: options?.duration ?? 4000,
                important: options?.important,
            });
        },

        /**
         * Show an error toast notification
         */
        error: (message: string, options?: ToastOptions) => {
            return sonnerToast.error(message, {
                description: options?.description,
                action: options?.action,
                cancel: options?.cancel,
                duration: options?.duration ?? 5000,
                important: options?.important,
            });
        },

        /**
         * Show an info toast notification
         */
        info: (message: string, options?: ToastOptions) => {
            return sonnerToast.info(message, {
                description: options?.description,
                action: options?.action,
                cancel: options?.cancel,
                duration: options?.duration ?? 4000,
                important: options?.important,
            });
        },

        /**
         * Show a warning toast notification
         */
        warning: (message: string, options?: ToastOptions) => {
            return sonnerToast.warning(message, {
                description: options?.description,
                action: options?.action,
                cancel: options?.cancel,
                duration: options?.duration ?? 4000,
                important: options?.important,
            });
        },

        /**
         * Show a loading toast notification
         */
        loading: (message: string, options?: ToastOptions) => {
            return sonnerToast.loading(message, {
                description: options?.description,
                duration: options?.duration ?? Infinity,
                important: options?.important,
            });
        },

        /**
         * Show a promise toast notification (automatically transitions between loading/success/error)
         */
        promise: <T>(
            promise: Promise<T>,
            messages: {
                loading: string;
                success: string | ((data: T) => string);
                error: string | ((error: any) => string);
            },
        ) => {
            return sonnerToast.promise(promise, messages);
        },

        /**
         * Dismiss a toast by ID or all toasts
         */
        dismiss: (toastId?: string | number) => {
            return sonnerToast.dismiss(toastId);
        },
    };

    return { toast };
}
