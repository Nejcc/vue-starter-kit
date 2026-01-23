<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import CookieConsentBanner from '@/components/CookieConsentBanner.vue';
import { home, login } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    title?: string;
    description?: string;
    cookieConsent?: any;
}>();

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-between gap-6 bg-background p-4 sm:p-6 md:p-8 lg:p-10"
    >
        <header
            class="mb-4 w-full max-w-sm text-sm sm:mb-6 sm:max-w-2xl md:max-w-4xl lg:max-w-6xl"
        >
            <nav class="flex items-center justify-between gap-2 sm:gap-4">
                <Link
                    :href="home()"
                    class="text-lg font-semibold text-[#1b1b18] sm:text-xl dark:text-[#EDEDEC]"
                    prefetch
                >
                    LaravelPlus
                </Link>
                <Link
                    v-if="!isAuthenticated"
                    :href="login()"
                    class="inline-block rounded-sm border border-transparent px-3 py-1 text-xs leading-normal text-[#1b1b18] hover:border-[#19140035] sm:px-5 sm:py-1.5 sm:text-sm dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    prefetch
                >
                    Log in
                </Link>
            </nav>
        </header>

        <div class="flex w-full flex-1 items-center justify-center">
            <div class="w-full max-w-sm">
                <div class="flex flex-col gap-6 sm:gap-8">
                    <div class="flex flex-col items-center gap-3 sm:gap-4">
                        <Link
                            :href="home()"
                            class="flex flex-col items-center gap-2 font-medium"
                            prefetch
                        >
                            <div
                                class="mb-1 flex h-8 w-8 items-center justify-center rounded-md sm:h-9 sm:w-9"
                            >
                                <AppLogoIcon
                                    class="size-8 fill-current text-[var(--foreground)] sm:size-9 dark:text-white"
                                />
                            </div>
                            <span class="sr-only">{{ title }}</span>
                        </Link>
                        <div class="space-y-1.5 text-center sm:space-y-2">
                            <h1 class="text-lg font-medium sm:text-xl">
                                {{ title }}
                            </h1>
                            <p class="text-xs text-muted-foreground sm:text-sm">
                                {{ description }}
                            </p>
                        </div>
                    </div>
                    <slot />
                </div>
            </div>
        </div>

        <!-- Cookie Consent Banner -->
        <CookieConsentBanner :cookie-consent="cookieConsent" />

        <footer
            class="mt-6 w-full max-w-sm text-xs text-[#706f6c] sm:mt-8 sm:max-w-2xl sm:text-sm md:max-w-4xl lg:max-w-6xl dark:text-[#A1A09A]"
        >
            <div
                class="flex flex-col items-center gap-3 border-t border-[#e3e3e0] pt-4 sm:gap-4 sm:pt-6 lg:flex-row lg:justify-between dark:border-[#3E3E3A]"
            >
                <div class="text-center lg:text-left">
                    <p class="text-[10px] sm:text-xs">
                        © {{ new Date().getFullYear() }} LaravelPlus. All rights
                        reserved.
                    </p>
                </div>
                <nav
                    class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 lg:justify-end"
                >
                    <Link
                        href="/privacy-policy"
                        class="text-[10px] text-[#706f6c] hover:text-[#1b1b18] hover:underline sm:text-xs dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        prefetch
                    >
                        Privacy Policy
                    </Link>
                    <Link
                        href="/cookie-policy"
                        class="text-[10px] text-[#706f6c] hover:text-[#1b1b18] hover:underline sm:text-xs dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        prefetch
                    >
                        Cookie Policy
                    </Link>
                </nav>
            </div>
        </footer>
    </div>
</template>
