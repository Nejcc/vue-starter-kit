<script setup lang="ts">
import LoadingSpinner from './LoadingSpinner.vue';

interface Props {
    message?: string;
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    message: 'Loading...',
    size: 'md',
});
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-4 py-12">
        <LoadingSpinner :size="props.size" />
        <p class="text-sm text-muted-foreground">{{ props.message }}</p>
    </div>
</template>
