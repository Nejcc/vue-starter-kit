import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
    resolve: {
        alias: {
            '@ecommerce': path.resolve(__dirname, 'packages/laravelplus/ecommerce/resources/js'),
        },
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue', '@inertiajs/vue3'],
                    ui: ['reka-ui', 'lucide-vue-next'],
                },
            },
        },
        chunkSizeWarningLimit: 1000,
    },
    optimizeDeps: {
        include: ['vue', '@inertiajs/vue3', 'reka-ui'],
    },
});
