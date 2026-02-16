import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import '../css/app.css';
import { initializeTheme } from './composables/useAppearance';
import { initializeToastPlugin } from './plugins/toastPlugin';
import { resolvePackagePages } from './resolvePackagePages';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// App pages (highest priority — can override package pages)
const appPages = import.meta.glob<DefineComponent>('./pages/**/*.vue');

// Package pages (auto-discovered from packages/laravelplus/*)
const packagePages = resolvePackagePages(
    import.meta.glob<DefineComponent>(
        '../../packages/laravelplus/*/resources/js/pages/**/*.vue',
    ),
);

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(`./pages/${name}.vue`, {
            ...packagePages,
            ...appPages,
        }),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
    defaults: {
        prefetch: {
            cacheFor: '1m',
        },
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// Initialize toast plugin for flash messages
initializeToastPlugin();
