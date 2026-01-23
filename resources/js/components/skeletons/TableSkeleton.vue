<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

interface Props {
    rows?: number;
    columns?: number;
}

const props = withDefaults(defineProps<Props>(), {
    rows: 5,
    columns: 4,
});
</script>

<template>
    <div class="rounded-lg border">
        <div class="border-b p-4">
            <div class="flex gap-4">
                <Skeleton
                    v-for="i in props.columns"
                    :key="`header-${i}`"
                    class="h-4 flex-1"
                />
            </div>
        </div>
        <div class="divide-y">
            <div v-for="i in props.rows" :key="`row-${i}`" class="p-4">
                <div class="flex gap-4">
                    <Skeleton
                        v-for="j in props.columns"
                        :key="`cell-${i}-${j}`"
                        class="h-4 flex-1"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
