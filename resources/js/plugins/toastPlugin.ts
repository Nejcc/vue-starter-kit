import type { Page } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

/**
 * Toast plugin for Inertia.js
 *
 * Automatically displays toast notifications for flash messages from the server.
 * Supports: status, success, error, info, warning flash message keys.
 */
export function initializeToastPlugin() {
    router.on('finish', (event) => {
        const page = event.detail.page as Page;
        const props = page.props;

        // Success messages
        if (props.status) {
            toast.success(props.status as string);
        }

        if (props.success) {
            toast.success(props.success as string);
        }

        // Error messages
        if (props.error) {
            toast.error(props.error as string);
        }

        // Info messages
        if (props.info) {
            toast.info(props.info as string);
        }

        // Warning messages
        if (props.warning) {
            toast.warning(props.warning as string);
        }
    });
}
