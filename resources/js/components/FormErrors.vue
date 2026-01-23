<script setup lang="ts">
import { AlertCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    errors?: Record<string, string | string[]>;
    excludeFields?: string[];
}

const props = withDefaults(defineProps<Props>(), {
    errors: () => ({}),
    excludeFields: () => [],
});

/**
 * Get all errors that are not tied to specific form fields
 * These are usually general form-level errors
 */
const generalErrors = computed(() => {
    if (!props.errors || Object.keys(props.errors).length === 0) {
        return [];
    }

    return Object.entries(props.errors)
        .filter(([key]) => !props.excludeFields.includes(key))
        .map(([, value]) => {
            // Handle both string and array error formats
            return Array.isArray(value) ? value : [value];
        })
        .flat()
        .filter(Boolean);
});

const hasErrors = computed(() => generalErrors.value.length > 0);
</script>

<template>
    <div
        v-if="hasErrors"
        class="rounded-md border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20"
    >
        <div class="flex gap-3">
            <AlertCircle
                class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600 dark:text-red-500"
            />
            <div class="flex-1">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-400">
                    {{
                        generalErrors.length === 1
                            ? 'Error'
                            : 'There were errors with your request'
                    }}
                </h3>
                <div
                    v-if="generalErrors.length === 1"
                    class="mt-1 text-sm text-red-700 dark:text-red-500"
                >
                    {{ generalErrors[0] }}
                </div>
                <ul
                    v-else
                    class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700 dark:text-red-500"
                >
                    <li v-for="(error, index) in generalErrors" :key="index">
                        {{ error }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
