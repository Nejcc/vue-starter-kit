<script setup lang="ts">
interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div :class="['rounded-lg border p-4', $props.class]">
        <div class="flex items-start justify-between">
            <div class="flex-1 space-y-2">
                <slot />
            </div>
            <div v-if="$slots.actions" class="flex items-center gap-2">
                <slot name="actions" />
            </div>
        </div>
        <div v-if="$slots.footer" class="mt-3 border-t pt-3">
            <slot name="footer" />
        </div>
    </div>
</template>
