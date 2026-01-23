import { useToast } from './useToast';

export interface ErrorHandlerOptions {
    showToast?: boolean;
    toastTitle?: string;
    logToConsole?: boolean;
    onError?: (error: Error) => void;
}

export function useErrorHandler() {
    const { toast } = useToast();

    /**
     * Handle errors with consistent error handling logic
     */
    const handleError = (error: unknown, options: ErrorHandlerOptions = {}) => {
        const {
            showToast = true,
            toastTitle = 'Error',
            logToConsole = true,
            onError,
        } = options;

        // Convert error to Error instance
        const err = error instanceof Error ? error : new Error(String(error));

        // Log to console
        if (logToConsole) {
            console.error('Error:', err);
        }

        // Show toast notification
        if (showToast) {
            toast.error(toastTitle, {
                description: err.message,
            });
        }

        // Call custom error handler
        if (onError) {
            onError(err);
        }

        return err;
    };

    /**
     * Wrap an async function with error handling
     */
    const withErrorHandling = <T extends (...args: any[]) => Promise<any>>(
        fn: T,
        options: ErrorHandlerOptions = {},
    ): T => {
        return (async (...args: Parameters<T>) => {
            try {
                return await fn(...args);
            } catch (error) {
                handleError(error, options);
                throw error;
            }
        }) as T;
    };

    /**
     * Handle validation errors from Laravel
     */
    const handleValidationErrors = (
        errors: Record<string, string | string[]>,
    ) => {
        const errorMessages = Object.entries(errors)
            .map(([field, messages]) => {
                const messageArray = Array.isArray(messages)
                    ? messages
                    : [messages];
                return messageArray.join(', ');
            })
            .join('\n');

        toast.error('Validation Error', {
            description: errorMessages,
        });
    };

    return {
        handleError,
        withErrorHandling,
        handleValidationErrors,
    };
}
