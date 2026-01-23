<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Toaster as Sonner } from 'vue-sonner';

const { theme } = useAppearance();
</script>

<template>
    <Sonner
        position="top-right"
        :theme="theme === 'dark' ? 'dark' : 'light'"
        :duration="4000"
        close-button
        rich-colors
        :toast-options="{
            classNames: {
                toast: 'group toast group-[.toaster]:bg-background group-[.toaster]:text-foreground group-[.toaster]:border-border group-[.toaster]:shadow-lg',
                description: 'group-[.toast]:text-muted-foreground',
                actionButton:
                    'group-[.toast]:bg-primary group-[.toast]:text-primary-foreground',
                cancelButton:
                    'group-[.toast]:bg-muted group-[.toast]:text-muted-foreground',
            },
        }"
    />
</template>
