<script setup lang="ts">
type BadgeVariant =
    | 'default'
    | 'success'
    | 'warning'
    | 'danger'
    | 'info'
    | 'purple';

interface Props {
    label: string;
    variant?: BadgeVariant;
}

withDefaults(defineProps<Props>(), {
    variant: 'default',
});

const variantClasses: Record<BadgeVariant, string> = {
    default: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    success:
        'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    warning:
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    danger: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    info: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    purple: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
};
</script>

<template>
    <span
        :class="[
            variantClasses[variant],
            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
        ]"
    >
        {{ label }}
    </span>
</template>
