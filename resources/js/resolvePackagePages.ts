import type { DefineComponent } from 'vue';

/**
 * Remap package page glob paths to the standard `./pages/{name}.vue` format.
 *
 * Vite's import.meta.glob returns paths like:
 *   `../../packages/laravelplus/ecommerce/resources/js/pages/admin/ecommerce/Dashboard.vue`
 *
 * This function normalizes them to:
 *   `./pages/admin/ecommerce/Dashboard.vue`
 *
 * This allows `resolvePageComponent` to find package pages using the same
 * key format as app pages. App pages take priority when merged after package pages.
 */
export function resolvePackagePages(
    globResult: Record<
        string,
        DefineComponent | (() => Promise<DefineComponent>)
    >,
): Record<string, DefineComponent | (() => Promise<DefineComponent>)> {
    const remapped: Record<
        string,
        DefineComponent | (() => Promise<DefineComponent>)
    > = {};

    for (const [path, module] of Object.entries(globResult)) {
        // Extract everything after `resources/js/pages/`
        const match = path.match(/\/resources\/js\/pages\/(.+)$/);
        if (match) {
            remapped[`./pages/${match[1]}`] = module;
        }
    }

    return remapped;
}
