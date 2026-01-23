<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

interface Props {
    fields?: number;
}

const props = withDefaults(defineProps<Props>(), {
    fields: 3,
});
</script>

<template>
    <div class="space-y-6">
        <div v-for="i in props.fields" :key="i" class="space-y-2">
            <Skeleton class="h-4 w-24" />
            <Skeleton class="h-10 w-full" />
        </div>
        <div class="flex justify-end gap-4">
            <Skeleton class="h-10 w-24" />
            <Skeleton class="h-10 w-24" />
        </div>
    </div>
</template>
