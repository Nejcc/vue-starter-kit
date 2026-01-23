<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
const canRegister = computed(() => page.props.canRegister ?? true);
</script>

<template>
    <header
        class="mb-4 w-full max-w-sm text-sm sm:mb-6 sm:max-w-2xl md:max-w-4xl lg:max-w-6xl"
    >
        <nav class="flex items-center justify-between gap-2 sm:gap-4">
            <Link
                href="/"
                class="text-lg font-semibold text-[#1b1b18] sm:text-xl dark:text-[#EDEDEC]"
                prefetch
            >
                LaravelPlus
            </Link>
            <div class="flex items-center gap-2 sm:gap-4">
                <Link
                    v-if="isAuthenticated"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-3 py-1 text-xs leading-normal text-[#1b1b18] hover:border-[#1915014a] sm:px-5 sm:py-1.5 sm:text-sm dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    prefetch
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-3 py-1 text-xs leading-normal text-[#1b1b18] hover:border-[#19140035] sm:px-5 sm:py-1.5 sm:text-sm dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                        prefetch
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-3 py-1 text-xs leading-normal text-[#1b1b18] hover:border-[#1915014a] sm:px-5 sm:py-1.5 sm:text-sm dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                        prefetch
                    >
                        Register
                    </Link>
                </template>
            </div>
        </nav>
    </header>
</template>
