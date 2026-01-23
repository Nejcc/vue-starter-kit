<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

interface Props {
    count?: number;
}

const props = withDefaults(defineProps<Props>(), {
    count: 3,
});
</script>

<template>
    <div class="space-y-4">
        <div v-for="i in props.count" :key="i" class="rounded-lg border p-4">
            <div class="flex items-start justify-between">
                <div class="flex-1 space-y-3">
                    <Skeleton class="h-5 w-1/3" />
                    <Skeleton class="h-4 w-1/2" />
                    <Skeleton class="h-4 w-1/4" />
                </div>
                <Skeleton class="h-8 w-16" />
            </div>
        </div>
    </div>
</template>
