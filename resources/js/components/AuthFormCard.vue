<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Component } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    icon: Component;
    title: string;
    description: string;
}>();
</script>

<template>
    <div class="flex w-full flex-col items-center gap-6 sm:gap-8">
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
        </Link>

        <div
            class="w-full max-w-md rounded-2xl border border-[#e3e3e0]/80 bg-white/60 p-6 backdrop-blur-sm sm:p-8 dark:border-[#2a2a28] dark:bg-white/[0.03]"
        >
            <div class="mb-6 flex flex-col items-center gap-3 text-center">
                <div
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF2D20]/10 to-[#FF2D20]/5 text-[#FF2D20] ring-1 ring-[#FF2D20]/10"
                >
                    <component :is="icon" :size="20" :stroke-width="1.75" />
                </div>
                <div class="space-y-1.5">
                    <h1 class="text-lg font-semibold tracking-tight sm:text-xl">
                        {{ title }}
                    </h1>
                    <p class="text-xs text-muted-foreground sm:text-sm">
                        {{ description }}
                    </p>
                </div>
            </div>

            <slot />
        </div>

        <div
            v-if="$slots.footer"
            class="text-center text-sm text-muted-foreground"
        >
            <slot name="footer" />
        </div>
    </div>
</template>
