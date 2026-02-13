<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';

interface Props {
    label: string;
    value: string | number;
    icon?: LucideIcon;
    iconColor?: string;
    iconBg?: string;
    trend?: string;
}

withDefaults(defineProps<Props>(), {
    icon: undefined,
    iconColor: 'text-blue-600 dark:text-blue-400',
    iconBg: 'bg-blue-50 dark:bg-blue-950/50',
    trend: undefined,
});
</script>

<template>
    <div class="rounded-lg border p-4">
        <div class="flex items-center gap-3">
            <div v-if="icon" :class="[iconBg, 'rounded-lg p-2']">
                <component :is="icon" :class="[iconColor, 'h-5 w-5']" />
            </div>
            <div>
                <p class="text-2xl font-bold">{{ value }}</p>
                <p class="text-xs text-muted-foreground">{{ label }}</p>
            </div>
            <span
                v-if="trend"
                class="ml-auto text-xs font-medium text-muted-foreground"
            >
                {{ trend }}
            </span>
        </div>
    </div>
</template>
