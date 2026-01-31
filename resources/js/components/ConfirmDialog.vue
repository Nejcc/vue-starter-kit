<script setup lang="ts">
import { ref } from 'vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

interface Props {
    title?: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Are you sure?',
    description: 'This action cannot be undone.',
    confirmText: 'Continue',
    cancelText: 'Cancel',
    variant: 'default',
});

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();

const open = ref(false);

defineExpose({
    open: () => {
        open.value = true;
    },
    close: () => {
        open.value = false;
    },
});

const handleConfirm = () => {
    emit('confirm');
    open.value = false;
};

const handleCancel = () => {
    emit('cancel');
    open.value = false;
};
</script>

<template>
    <AlertDialog v-model:open="open">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ props.title }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ props.description }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="handleCancel">
                    {{ props.cancelText }}
                </AlertDialogCancel>
                <AlertDialogAction
                    :class="{
                        'bg-destructive text-destructive-foreground hover:bg-destructive/90':
                            props.variant === 'destructive',
                    }"
                    @click="handleConfirm"
                >
                    {{ props.confirmText }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
