<script setup lang="ts">
import { AlertDialogDescription, type AlertDialogDescriptionProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

interface Props extends AlertDialogDescriptionProps {
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <AlertDialogDescription :class="cn('text-sm text-muted-foreground', props.class)">
        <slot />
    </AlertDialogDescription>
</template>
