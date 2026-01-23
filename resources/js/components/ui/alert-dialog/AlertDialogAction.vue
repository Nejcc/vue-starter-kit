<script setup lang="ts">
import { AlertDialogAction, type AlertDialogActionProps } from 'reka-ui';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

interface Props extends AlertDialogActionProps {
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <AlertDialogAction :class="cn(buttonVariants(), props.class)">
        <slot />
    </AlertDialogAction>
</template>
