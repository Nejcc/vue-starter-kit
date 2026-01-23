<script setup lang="ts">
import { AlertDialogTitle, type AlertDialogTitleProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

interface Props extends AlertDialogTitleProps {
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <AlertDialogTitle :class="cn('text-lg font-semibold', props.class)">
        <slot />
    </AlertDialogTitle>
</template>
