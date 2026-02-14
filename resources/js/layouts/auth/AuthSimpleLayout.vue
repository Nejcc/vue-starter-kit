<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CookieConsentBanner from '@/components/CookieConsentBanner.vue';
import PublicFooter from '@/components/PublicFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';

defineProps<{
    cookieConsent?: any;
}>();
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div
        class="relative flex min-h-svh flex-col items-center overflow-hidden bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
        style="font-family: 'Inter', sans-serif"
    >
        <!-- Background grid pattern -->
        <div
            class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,#8881_1px,transparent_1px),linear-gradient(to_bottom,#8881_1px,transparent_1px)] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)] bg-[size:4rem_4rem] dark:bg-[linear-gradient(to_right,#fff1_1px,transparent_1px),linear-gradient(to_bottom,#fff1_1px,transparent_1px)]"
        />

        <!-- Gradient orb -->
        <div
            class="pointer-events-none absolute -top-24 left-1/2 h-[600px] w-[900px] -translate-x-1/2 rounded-full bg-gradient-to-br from-[#FF2D20]/20 via-[#FF2D20]/5 to-transparent opacity-60 blur-3xl dark:from-[#FF2D20]/10 dark:via-[#FF2D20]/5 dark:opacity-40"
        />

        <!-- Header -->
        <div class="relative z-10 w-full px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <PublicHeader />
            </div>
        </div>

        <!-- Content -->
        <div
            class="relative z-10 flex w-full flex-1 items-center justify-center px-4 py-8 sm:px-6 sm:py-12"
        >
            <slot />
        </div>

        <!-- Cookie Consent Banner -->
        <CookieConsentBanner :cookie-consent="cookieConsent" />

        <!-- Footer -->
        <div class="relative z-10 w-full px-4 pb-6 sm:px-6 sm:pb-8 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <PublicFooter />
            </div>
        </div>
    </div>
</template>
