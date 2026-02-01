<script setup lang="ts">
interface Props {
    title?: string;
    description?: string;
}

withDefaults(defineProps<Props>(), {
    title: undefined,
    description: undefined,
});
</script>

<template>
    <div class="space-y-6">
        <div v-if="title || description">
            <h3 v-if="title" class="text-lg font-medium">{{ title }}</h3>
            <p v-if="description" class="text-sm text-muted-foreground">
                {{ description }}
            </p>
        </div>
        <div class="space-y-4">
            <slot />
        </div>
        <div v-if="$slots.actions" class="flex items-center gap-4">
            <slot name="actions" />
        </div>
    </div>
</template>
