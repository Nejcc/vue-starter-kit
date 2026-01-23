<template>
    <div
        v-if="isImpersonating"
        class="flex items-center justify-between gap-4 bg-yellow-500 px-4 py-2 text-yellow-900 dark:bg-yellow-600 dark:text-yellow-100"
    >
        <div class="flex items-center gap-2">
            <AlertTriangle class="h-4 w-4" />
            <span class="text-sm font-medium">
                Impersonating
                <span v-if="currentUser" class="font-semibold">{{
                    currentUser.name
                }}</span>
                <span v-if="impersonator" class="ml-1 text-xs opacity-75">
                    (logged in as {{ impersonator.name }})
                </span>
            </span>
        </div>
        <Button
            variant="outline"
            size="sm"
            class="bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700"
            @click="stopImpersonating"
        >
            Stop Impersonating
        </Button>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router, usePage } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();

const auth = computed(
    () =>
        page.props.auth as {
            user?: { id: number; name: string; email: string };
            isImpersonating?: boolean;
            impersonator?: { id: number; name: string; email: string };
        },
);

const isImpersonating = computed(() => {
    return auth.value?.isImpersonating ?? false;
});

const currentUser = computed(() => {
    return auth.value?.user ?? null;
});

const impersonator = computed(() => {
    return auth.value?.impersonator ?? null;
});

const stopImpersonating = () => {
    router.delete('/impersonate', {
        onSuccess: () => {
            // Redirect handled by backend
        },
    });
};
</script>
