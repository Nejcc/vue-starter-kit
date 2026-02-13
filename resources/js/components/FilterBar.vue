<script setup lang="ts">
import SearchInput from '@/components/SearchInput.vue';

interface Props {
    searchValue: string;
    searchPlaceholder?: string;
    showClear?: boolean;
}

withDefaults(defineProps<Props>(), {
    searchPlaceholder: 'Search...',
    showClear: true,
});

const emit = defineEmits<{
    'update:searchValue': [value: string];
    search: [];
    clear: [];
}>();
</script>

<template>
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex-1">
            <SearchInput
                :model-value="searchValue"
                :placeholder="searchPlaceholder"
                :show-clear="showClear"
                @update:model-value="emit('update:searchValue', $event)"
                @search="emit('search')"
                @clear="emit('clear')"
            />
        </div>
        <slot name="filters" />
        <slot name="actions" />
    </div>
</template>
