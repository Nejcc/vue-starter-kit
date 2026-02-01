<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard, login, register } from '@/routes';

const page = usePage();
const appName = computed(() => page.props.name ?? 'Laravel');
const isAuthenticated = computed(() => !!page.props.auth?.user);
const canRegister = computed(() => page.props.canRegister ?? true);
</script>

<template>
    <header class="w-full">
        <nav class="flex items-center justify-between gap-4">
            <Link
                href="/"
                class="text-lg font-semibold text-[#1b1b18] sm:text-xl dark:text-[#EDEDEC]"
                prefetch
            >
                {{ appName }}
            </Link>
            <div class="flex items-center gap-2 sm:gap-3">
                <Link
                    v-if="isAuthenticated"
                    :href="dashboard()"
                    class="inline-flex items-center justify-center rounded-lg border border-[#e3e3e0] px-4 py-2 text-sm font-medium text-[#1b1b18] transition hover:border-[#19140035] hover:bg-[#f5f5f4] sm:px-5 dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b] dark:hover:bg-[#1a1a19]"
                    prefetch
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium text-[#1b1b18] transition hover:bg-[#f5f5f4] sm:px-5 dark:text-[#EDEDEC] dark:hover:bg-[#1a1a19]"
                        prefetch
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-flex items-center justify-center rounded-lg border border-[#e3e3e0] px-4 py-2 text-sm font-medium text-[#1b1b18] transition hover:border-[#19140035] hover:bg-[#f5f5f4] sm:px-5 dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b] dark:hover:bg-[#1a1a19]"
                        prefetch
                    >
                        Register
                    </Link>
                </template>
            </div>
        </nav>
    </header>
</template>
