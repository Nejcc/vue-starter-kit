<script setup lang="ts">
import PublicFooter from '@/components/PublicFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import CookieConsentBanner from '@/components/CookieConsentBanner.vue';

interface Props {
    centered?: boolean;
    cookieConsent?: any;
}

withDefaults(defineProps<Props>(), {
    centered: false,
    cookieConsent: () => ({}),
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-4 text-[#1b1b18] sm:p-6 dark:bg-[#0a0a0a]"
        :class="{ 'lg:justify-center lg:p-8': centered }"
    >
        <PublicHeader />
        <slot />
        <PublicFooter />
        
        <!-- Cookie Consent Banner -->
        <CookieConsentBanner :cookie-consent="cookieConsent" />
    </div>
</template>

