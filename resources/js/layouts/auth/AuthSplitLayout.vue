<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import CookieConsentBanner from '@/components/CookieConsentBanner.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

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
        class="relative flex min-h-dvh"
        style="font-family: 'Inter', sans-serif"
    >
        <!-- Left branded panel — hidden on mobile -->
        <div
            class="relative hidden w-1/2 flex-col overflow-hidden bg-[#0a0a0a] text-white lg:flex"
        >
            <!-- Grid pattern -->
            <div
                class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,#fff1_1px,transparent_1px),linear-gradient(to_bottom,#fff1_1px,transparent_1px)] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)] bg-[size:4rem_4rem]"
            />

            <!-- Gradient orb -->
            <div
                class="pointer-events-none absolute -top-24 left-1/2 h-[600px] w-[900px] -translate-x-1/2 rounded-full bg-gradient-to-br from-[#FF2D20]/15 via-[#FF2D20]/5 to-transparent opacity-60 blur-3xl"
            />

            <!-- Content -->
            <div
                class="relative z-10 flex h-full flex-col justify-between p-10"
            >
                <!-- Top: logo + name -->
                <Link
                    :href="home()"
                    class="flex items-center gap-2.5 text-lg font-semibold tracking-tight"
                >
                    <AppLogoIcon class="size-8 fill-current text-white" />
                    {{ name }}
                </Link>

                <!-- Center: tagline -->
                <div class="space-y-6">
                    <h1
                        class="text-4xl leading-[1.1] font-extrabold tracking-tight xl:text-5xl"
                    >
                        <span
                            class="bg-gradient-to-b from-white to-white/60 bg-clip-text text-transparent"
                        >
                            Ship your SaaS
                        </span>
                        <br />
                        <span
                            class="bg-gradient-to-r from-[#FF2D20] via-[#ff6a5e] to-[#FF2D20] bg-clip-text text-transparent"
                        >
                            in record time
                        </span>
                    </h1>
                    <p class="max-w-sm text-sm leading-relaxed text-[#A1A09A]">
                        Auth, payments, admin panel, RBAC, audit logs, and more
                        — all wired up with TypeScript and Tailwind CSS.
                    </p>

                    <!-- Feature pills -->
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="feature in [
                                'Auth & 2FA',
                                'Payments',
                                'RBAC',
                                'Admin Panel',
                                'Audit Logs',
                                'Dark Mode',
                            ]"
                            :key="feature"
                            class="rounded-full border border-[#2a2a28] bg-white/[0.03] px-3 py-1.5 text-xs font-medium text-[#A1A09A]"
                        >
                            {{ feature }}
                        </span>
                    </div>
                </div>

                <!-- Bottom: subtle footer -->
                <p class="text-xs text-[#706f6c]">
                    &copy; {{ new Date().getFullYear() }} {{ name }}. All rights
                    reserved.
                </p>
            </div>
        </div>

        <!-- Right panel — form content -->
        <div
            class="flex w-full flex-col items-center justify-center bg-[#FDFDFC] px-4 py-8 sm:px-6 sm:py-12 lg:w-1/2 dark:bg-[#0a0a0a]"
        >
            <div class="w-full max-w-md">
                <slot />
            </div>
        </div>

        <!-- Cookie Consent Banner -->
        <CookieConsentBanner :cookie-consent="cookieConsent" />
    </div>
</template>
