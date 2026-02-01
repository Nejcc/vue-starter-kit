import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ref } from 'vue';

interface UseSearchOptions {
    url: string;
    debounceMs?: number;
    extraParams?: () => Record<string, unknown>;
}

export function useSearch(options: UseSearchOptions) {
    const { url, debounceMs = 300, extraParams } = options;

    const searchQuery = ref('');

    const debouncedSearch = useDebounceFn((query: string) => {
        router.get(
            url,
            {
                search: query || null,
                ...(extraParams?.() ?? {}),
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }, debounceMs);

    function handleSearch(): void {
        debouncedSearch(searchQuery.value);
    }

    function clearSearch(): void {
        searchQuery.value = '';
        router.get(url, { ...(extraParams?.() ?? {}) }, { preserveState: false });
    }

    function initFromFilter(value?: string): void {
        searchQuery.value = value ?? '';
    }

    return {
        searchQuery,
        handleSearch,
        clearSearch,
        initFromFilter,
    };
}
