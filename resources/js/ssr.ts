import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createSSRApp, h } from 'vue';
import { renderToString } from 'vue/server-renderer';
import { resolvePackagePages } from './resolvePackagePages';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const appPages = import.meta.glob<DefineComponent>('./pages/**/*.vue');
const packagePages = resolvePackagePages(
    import.meta.glob<DefineComponent>(
        '../../packages/laravelplus/*/resources/js/pages/**/*.vue',
    ),
);

createServer(
    (page) =>
        createInertiaApp({
            page,
            render: renderToString,
            title: (title) => (title ? `${title} - ${appName}` : appName),
            resolve: (name) =>
                resolvePageComponent(`./pages/${name}.vue`, {
                    ...packagePages,
                    ...appPages,
                }),
            setup: ({ App, props, plugin }) =>
                createSSRApp({ render: () => h(App, props) }).use(plugin),
        }),
    { cluster: true },
);
