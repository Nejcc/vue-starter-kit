<script setup lang="ts">
import { AlertCircle, AlertTriangle, CheckCircle2, Info, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';

type AlertVariant = 'error' | 'warning' | 'success' | 'info';

interface Props {
    variant?: AlertVariant;
    title?: string;
    messages?: string | string[];
    dismissible?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'error',
    title: undefined,
    messages: undefined,
    dismissible: false,
});

const emit = defineEmits<{
    dismiss: [];
}>();

const dismissed = ref(false);

const icon = computed(() => {
    switch (props.variant) {
        case 'success':
            return CheckCircle2;
        case 'warning':
            return AlertTriangle;
        case 'info':
            return Info;
        default:
            return AlertCircle;
    }
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'success':
            return 'border-green-500/50 text-green-600 dark:border-green-500 dark:text-green-500';
        case 'warning':
            return 'border-yellow-500/50 text-yellow-600 dark:border-yellow-500 dark:text-yellow-500';
        case 'info':
            return 'border-blue-500/50 text-blue-600 dark:border-blue-500 dark:text-blue-500';
        default:
            return 'border-destructive/50 text-destructive dark:border-destructive';
    }
});

const descriptionClasses = computed(() => {
    switch (props.variant) {
        case 'success':
            return 'text-green-600/90 dark:text-green-500/90';
        case 'warning':
            return 'text-yellow-600/90 dark:text-yellow-500/90';
        case 'info':
            return 'text-blue-600/90 dark:text-blue-500/90';
        default:
            return 'text-destructive/90';
    }
});

const normalizedMessages = computed(() => {
    if (!props.messages) return [];
    if (typeof props.messages === 'string') return [props.messages];
    return Array.from(new Set(props.messages));
});

function dismiss(): void {
    dismissed.value = true;
    emit('dismiss');
}
</script>

<template>
    <Alert v-if="!dismissed" :class="variantClasses">
        <component :is="icon" class="h-4 w-4" />
        <div class="flex flex-1 items-start justify-between gap-4">
            <div class="flex-1">
                <AlertTitle v-if="title">{{ title }}</AlertTitle>
                <AlertDescription :class="descriptionClasses">
                    <template v-if="normalizedMessages.length === 1">
                        {{ normalizedMessages[0] }}
                    </template>
                    <ul v-else-if="normalizedMessages.length > 1" class="list-inside list-disc text-sm">
                        <li v-for="(msg, idx) in normalizedMessages" :key="idx">
                            {{ msg }}
                        </li>
                    </ul>
                    <slot v-else />
                </AlertDescription>
            </div>
            <Button
                v-if="dismissible"
                variant="ghost"
                size="sm"
                class="h-6 w-6 p-0"
                @click="dismiss"
            >
                <X class="h-4 w-4" />
            </Button>
        </div>
    </Alert>
</template>
