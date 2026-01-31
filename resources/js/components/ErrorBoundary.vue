<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import { onErrorCaptured, ref } from 'vue';
import { Button } from '@/components/ui/button';

interface Props {
    fallbackMessage?: string;
    showReload?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    fallbackMessage: 'Something went wrong. Please try again.',
    showReload: true,
});

const error = ref<Error | null>(null);

onErrorCaptured((err) => {
    error.value = err;
    console.error('Error captured:', err);
    return false;
});

const handleReload = () => {
    error.value = null;
    router.reload();
};

const handleReset = () => {
    error.value = null;
};
</script>

<template>
    <div v-if="error" class="flex min-h-[400px] items-center justify-center">
        <div class="text-center">
            <AlertTriangle class="mx-auto mb-4 h-12 w-12 text-destructive" />
            <h3 class="mb-2 text-lg font-semibold">
                {{ props.fallbackMessage }}
            </h3>
            <p class="mb-4 text-sm text-muted-foreground">
                {{ error.message }}
            </p>
            <div class="flex justify-center gap-2">
                <Button variant="outline" @click="handleReset">
                    Try Again
                </Button>
                <Button v-if="props.showReload" @click="handleReload">
                    Reload Page
                </Button>
            </div>
        </div>
    </div>
    <slot v-else />
</template>
