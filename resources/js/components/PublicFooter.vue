<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer
        class="mt-6 w-full max-w-sm text-xs text-[#706f6c] sm:mt-8 sm:max-w-2xl sm:text-sm md:max-w-4xl dark:text-[#A1A09A] lg:max-w-6xl"
    >
        <div
            class="flex flex-col items-center gap-3 border-t border-[#e3e3e0] pt-4 dark:border-[#3E3E3A] sm:gap-4 sm:pt-6 lg:flex-row lg:justify-between"
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
</template>

