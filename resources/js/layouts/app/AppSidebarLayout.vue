<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import CookieConsentBanner from '@/components/CookieConsentBanner.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    cookieConsent?: any;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    cookieConsent: () => ({}),
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        
        <!-- Cookie Consent Banner -->
        <CookieConsentBanner :cookie-consent="cookieConsent" />
    </AppShell>
</template>
