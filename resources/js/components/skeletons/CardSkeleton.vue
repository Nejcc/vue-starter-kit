<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

interface Props {
    count?: number;
}

const props = withDefaults(defineProps<Props>(), {
    count: 1,
});
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="i in props.count" :key="i" class="rounded-lg border p-6">
            <div class="space-y-3">
                <Skeleton class="h-12 w-12 rounded-full" />
                <Skeleton class="h-5 w-2/3" />
                <Skeleton class="h-4 w-full" />
                <Skeleton class="h-4 w-4/5" />
            </div>
        </div>
    </div>
</template>
