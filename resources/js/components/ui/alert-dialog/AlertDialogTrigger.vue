<script setup lang="ts">
import { AlertDialogTrigger, type AlertDialogTriggerProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';

interface Props extends AlertDialogTriggerProps {
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <AlertDialogTrigger v-bind="props" :class="props.class">
        <slot />
    </AlertDialogTrigger>
</template>
