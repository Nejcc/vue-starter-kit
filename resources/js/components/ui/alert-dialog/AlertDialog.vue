<script setup lang="ts">
import {
    AlertDialogRoot,
    type AlertDialogRootEmits,
    type AlertDialogRootProps,
    useForwardPropsEmits,
} from 'reka-ui';
import { computed, type HTMLAttributes } from 'vue';

const props = defineProps<AlertDialogRootProps & { class?: HTMLAttributes['class'] }>();

const emits = defineEmits<AlertDialogRootEmits>();

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
