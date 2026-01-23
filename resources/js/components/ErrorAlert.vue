<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { AlertTriangle, XCircle } from 'lucide-vue-next';

interface Props {
    title?: string;
    message: string;
    variant?: 'error' | 'warning';
    dismissible?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Error',
    variant: 'error',
    dismissible: false,
});

const emit = defineEmits<{
    dismiss: [];
}>();

const icon = props.variant === 'error' ? XCircle : AlertTriangle;
</script>

<template>
    <Alert
        :class="{
            'border-destructive/50 text-destructive dark:border-destructive':
                variant === 'error',
            'border-yellow-500/50 text-yellow-600 dark:border-yellow-500 dark:text-yellow-500':
                variant === 'warning',
        }"
    >
        <component :is="icon" class="h-4 w-4" />
        <div class="flex flex-1 items-start justify-between gap-4">
            <div class="flex-1">
                <AlertTitle>{{ props.title }}</AlertTitle>
                <AlertDescription
                    :class="{
                        'text-destructive/90': variant === 'error',
                        'text-yellow-600/90 dark:text-yellow-500/90':
                            variant === 'warning',
                    }"
                >
                    {{ props.message }}
                </AlertDescription>
            </div>
            <Button
                v-if="props.dismissible"
                variant="ghost"
                size="sm"
                class="h-6 w-6 p-0"
                @click="emit('dismiss')"
            >
                <XCircle class="h-4 w-4" />
            </Button>
        </div>
    </Alert>
</template>
