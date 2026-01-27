<script setup lang="ts">
import { AlertDialogRoot, useForwardPropsEmits } from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

interface Props {
    defaultOpen?: boolean;
    open?: boolean;
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

const emits = defineEmits<{
    'update:open': [value: boolean];
}>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <AlertDialogRoot v-bind="forwarded">
        <slot />
    </AlertDialogRoot>
</template>
