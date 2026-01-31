<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';

defineProps<{
    cookieConsent?: any;
}>();

const page = usePage();
const layoutComponent = computed(() =>
    page.props.auth_layout === 'split' ? AuthSplitLayout : AuthSimpleLayout,
);
</script>

<template>
    <component :is="layoutComponent" :cookie-consent="cookieConsent">
        <slot />
    </component>
</template>
