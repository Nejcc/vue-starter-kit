<script setup lang="ts">
import { AlertDialogCancel, type AlertDialogCancelProps } from 'reka-ui';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

interface Props extends AlertDialogCancelProps {
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <AlertDialogCancel
        :class="cn(buttonVariants({ variant: 'outline' }), 'mt-2 sm:mt-0', props.class)"
    >
        <slot />
    </AlertDialogCancel>
</template>
