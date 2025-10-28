<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
    cookieConsent?: any;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description" :cookie-consent="cookieConsent">
        <slot />
    </AuthLayout>
</template>
