<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Sparkles,
    Shield,
    CreditCard,
    Users,
    Bell,
    Database,
    Moon,
    ScrollText,
    Cookie,
    UserCog,
    Mail,
    Copy,
    Check,
    Terminal,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import CookieConsentBanner from '@/components/CookieConsentBanner.vue';
import PublicFooter from '@/components/PublicFooter.vue';
import PublicHeader from '@/components/PublicHeader.vue';
import SeoHead from '@/components/SeoHead.vue';
import { register, dashboard } from '@/routes';
import type { WelcomePageProps } from '@/types';

interface Props extends WelcomePageProps {
    cookieConsent?: any;
}

withDefaults(defineProps<Props>(), {
    canRegister: true,
    cookieConsent: () => ({}),
});

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);

const visible = ref(false);
onMounted(() => {
    requestAnimationFrame(() => {
        visible.value = true;
    });
});

const installCommand =
    'git clone https://github.com/aspect-dev/vue-starter-kit.git && cd vue-starter-kit && composer run setup';
const copied = ref(false);
function copyCommand() {
    navigator.clipboard.writeText(installCommand);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
}
</script>

<template>
    <SeoHead
        title="Welcome"
        description="Laravel 12 + Vue 3 + Inertia.js v2 starter kit. Auth, payments, admin panel, notifications, RBAC, audit logs, dark mode, and GDPR — all wired up with TypeScript and Tailwind CSS."
    >
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </SeoHead>
    <div
        class="relative flex min-h-screen flex-col items-center overflow-hidden bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
        style="font-family: 'Inter', sans-serif"
    >
        <!-- Background grid pattern -->
        <div
            class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,#8881_1px,transparent_1px),linear-gradient(to_bottom,#8881_1px,transparent_1px)] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)] bg-[size:4rem_4rem] dark:bg-[linear-gradient(to_right,#fff1_1px,transparent_1px),linear-gradient(to_bottom,#fff1_1px,transparent_1px)]"
        />

        <!-- Gradient orbs -->
        <div
            class="pointer-events-none absolute -top-24 left-1/2 h-[600px] w-[900px] -translate-x-1/2 rounded-full bg-gradient-to-br from-[#FF2D20]/20 via-[#FF2D20]/5 to-transparent opacity-60 blur-3xl dark:from-[#FF2D20]/10 dark:via-[#FF2D20]/5 dark:opacity-40"
        />
        <div
            class="pointer-events-none absolute top-[60%] -right-40 h-[400px] w-[400px] rounded-full bg-gradient-to-l from-orange-400/10 to-transparent blur-3xl dark:from-orange-500/5"
        />
        <div
            class="pointer-events-none absolute -bottom-20 -left-40 h-[400px] w-[500px] rounded-full bg-gradient-to-tr from-rose-400/8 to-transparent blur-3xl dark:from-rose-500/5"
        />

        <!-- Header -->
        <div class="relative z-10 w-full px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <PublicHeader />
            </div>
        </div>

        <!-- Hero Section -->
        <section
            class="relative z-10 flex w-full flex-col items-center justify-center px-4 pt-16 pb-12 sm:px-6 sm:pt-24 sm:pb-16 lg:px-8 lg:pt-32 lg:pb-20"
        >
            <div class="mx-auto max-w-4xl text-center">
                <!-- Badge -->
                <div
                    class="mb-6 inline-flex items-center gap-1.5 rounded-full border border-[#FF2D20]/20 bg-[#FF2D20]/5 px-3.5 py-1.5 text-xs font-medium text-[#FF2D20] transition-all duration-700 dark:border-[#FF2D20]/25 dark:bg-[#FF2D20]/10"
                    :class="
                        visible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-4 opacity-0'
                    "
                >
                    <Sparkles :size="13" :stroke-width="2" />
                    <span>Laravel 12 + Vue 3 + Inertia.js v2</span>
                </div>

                <h1
                    class="text-5xl leading-[1.08] font-extrabold tracking-tight transition-all delay-100 duration-700 sm:text-6xl lg:text-7xl"
                    :class="
                        visible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-6 opacity-0'
                    "
                >
                    <span
                        class="bg-gradient-to-b from-[#1b1b18] to-[#1b1b18]/70 bg-clip-text text-transparent dark:from-white dark:to-white/60"
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

                <p
                    class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-[#706f6c] transition-all delay-200 duration-700 sm:mt-8 sm:text-lg dark:text-[#A1A09A]"
                    :class="
                        visible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-6 opacity-0'
                    "
                >
                    Auth, payments, admin panel, notifications, RBAC, audit
                    logs, dark mode, GDPR — all wired up with TypeScript and
                    Tailwind CSS. Start building features on day one.
                </p>

                <!-- Terminal command -->
                <div
                    class="mx-auto mt-8 max-w-2xl transition-all delay-300 duration-700 sm:mt-10"
                    :class="
                        visible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-6 opacity-0'
                    "
                >
                    <button
                        type="button"
                        class="group flex w-full items-center gap-3 overflow-hidden rounded-xl border border-[#e3e3e0]/80 bg-[#1b1b18] p-1 pl-4 text-left transition-all duration-200 hover:border-[#FF2D20]/30 hover:shadow-lg hover:shadow-[#FF2D20]/5 sm:pl-5 dark:border-[#2a2a28] dark:bg-[#111110] dark:hover:border-[#FF2D20]/30"
                        @click="copyCommand"
                    >
                        <Terminal
                            :size="14"
                            class="hidden shrink-0 text-[#706f6c] sm:block"
                        />
                        <code
                            class="flex-1 truncate pr-2 font-mono text-xs text-[#A1A09A] sm:text-sm"
                        >
                            <span class="text-[#FF2D20]">$</span>
                            git clone
                            <span class="text-emerald-400"
                                >vue-starter-kit</span
                            >
                            <span class="text-[#706f6c]">&&</span> cd
                            vue-starter-kit
                            <span class="text-[#706f6c]">&&</span> composer run
                            setup
                        </code>
                        <span
                            class="flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-2.5 text-xs font-medium transition-colors duration-200 sm:px-4 sm:py-3"
                            :class="
                                copied
                                    ? 'bg-emerald-500/10 text-emerald-400'
                                    : 'bg-white/10 text-[#A1A09A] group-hover:bg-white/15 group-hover:text-white'
                            "
                        >
                            <Check
                                v-if="copied"
                                :size="14"
                                :stroke-width="2.5"
                            />
                            <Copy v-else :size="14" />
                            <span class="hidden sm:inline">{{
                                copied ? 'Copied!' : 'Copy'
                            }}</span>
                        </span>
                    </button>
                </div>

                <div
                    class="mt-6 flex flex-col items-center justify-center gap-3 transition-all delay-[400ms] duration-700 sm:mt-8 sm:flex-row sm:gap-4"
                    :class="
                        visible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-6 opacity-0'
                    "
                >
                    <Link
                        :href="isAuthenticated ? dashboard() : register()"
                        class="group inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF2D20] px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#FF2D20]/25 transition-all duration-200 hover:scale-[1.02] hover:bg-[#e0261b] hover:shadow-xl hover:shadow-[#FF2D20]/30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#FF2D20] active:scale-[0.98] sm:text-base"
                        prefetch
                    >
                        Get Started
                        <ArrowRight
                            :size="16"
                            class="transition-transform duration-200 group-hover:translate-x-0.5"
                        />
                    </Link>
                    <a
                        href="https://laravel.com/docs"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#e3e3e0] bg-white/50 px-7 py-3.5 text-sm font-semibold text-[#1b1b18] backdrop-blur-sm transition-all duration-200 hover:scale-[1.02] hover:border-[#d5d5d0] hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#FF2D20] active:scale-[0.98] sm:text-base dark:border-[#3E3E3A] dark:bg-white/5 dark:text-[#EDEDEC] dark:hover:border-[#62605b] dark:hover:bg-white/10"
                    >
                        Documentation
                        <svg
                            class="h-3.5 w-3.5 opacity-50"
                            viewBox="0 0 10 11"
                            fill="none"
                        >
                            <path
                                d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001"
                                stroke="currentColor"
                                stroke-linecap="square"
                            />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- Bento Features Grid -->
        <section
            class="relative z-10 w-full px-4 pb-20 sm:px-6 sm:pb-28 lg:px-8 lg:pb-36"
        >
            <div class="mx-auto max-w-6xl">
                <div
                    class="mb-10 text-center transition-all delay-400 duration-700 sm:mb-14"
                    :class="
                        visible
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-6 opacity-0'
                    "
                >
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">
                        Everything you need to launch
                    </h2>
                    <p
                        class="mt-3 text-sm text-[#706f6c] sm:text-base dark:text-[#A1A09A]"
                    >
                        Not just scaffolding — a complete production stack.
                    </p>
                </div>

                <!-- Bento Grid: lg layout
                     Row 1: Auth(2col,2row) | Payments(2col)
                     Row 2: Auth(cont.)     | RBAC | Notifications
                     Row 3: Admin(2col)     | Subscriber(2col)
                     Row 4: Audit | DB      | Dark Mode | GDPR
                -->
                <div
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4"
                >
                    <!-- 1. Auth & 2FA — 2 cols wide, 2 rows tall -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-[#e3e3e0]/80 bg-white/60 p-6 backdrop-blur-sm transition-all duration-500 hover:border-[#FF2D20]/20 hover:shadow-xl hover:shadow-[#FF2D20]/5 sm:col-span-2 sm:p-8 lg:row-span-2 dark:border-[#2a2a28] dark:bg-white/[0.03] dark:hover:border-[#FF2D20]/20"
                        :class="
                            visible
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#FF2D20]/[0.04] via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative flex h-full flex-col">
                            <div
                                class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF2D20]/10 to-[#FF2D20]/5 text-[#FF2D20] ring-1 ring-[#FF2D20]/10 transition-shadow duration-300 group-hover:shadow-md group-hover:shadow-[#FF2D20]/10"
                            >
                                <Shield :size="20" :stroke-width="1.75" />
                            </div>
                            <h3 class="mb-2 text-lg font-bold tracking-tight">
                                Authentication & 2FA
                            </h3>
                            <p
                                class="mb-6 text-sm leading-relaxed text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Full Fortify-powered auth with login,
                                registration, email verification, password
                                reset, and TOTP two-factor authentication with
                                recovery codes.
                            </p>
                            <!-- Mock auth UI -->
                            <div
                                class="mt-auto overflow-hidden rounded-lg border border-[#e3e3e0]/60 bg-[#fafaf9] p-4 dark:border-[#2a2a28] dark:bg-[#111110]"
                            >
                                <div class="mb-3 flex items-center gap-2">
                                    <div
                                        class="h-2 w-2 rounded-full bg-emerald-400"
                                    />
                                    <span
                                        class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]"
                                        >Secure session</span
                                    >
                                </div>
                                <div class="space-y-2">
                                    <div
                                        class="h-7 rounded-md bg-[#e3e3e0]/50 dark:bg-[#2a2a28]/50"
                                    />
                                    <div
                                        class="h-7 rounded-md bg-[#e3e3e0]/50 dark:bg-[#2a2a28]/50"
                                    />
                                    <div class="flex gap-2">
                                        <div
                                            class="h-7 flex-1 rounded-md bg-[#FF2D20]/15"
                                        />
                                        <div
                                            class="h-7 w-20 rounded-md bg-[#e3e3e0]/40 dark:bg-[#2a2a28]/40"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Payments — 2 cols wide (fills row 1 right half) -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-[#e3e3e0]/80 bg-white/60 p-6 backdrop-blur-sm transition-all delay-75 duration-500 hover:border-[#FF2D20]/20 hover:shadow-xl hover:shadow-[#FF2D20]/5 sm:col-span-2 sm:p-8 dark:border-[#2a2a28] dark:bg-white/[0.03] dark:hover:border-[#FF2D20]/20"
                        :class="
                            visible
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#FF2D20]/[0.04] via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative">
                            <div
                                class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF2D20]/10 to-[#FF2D20]/5 text-[#FF2D20] ring-1 ring-[#FF2D20]/10 transition-shadow duration-300 group-hover:shadow-md group-hover:shadow-[#FF2D20]/10"
                            >
                                <CreditCard :size="20" :stroke-width="1.75" />
                            </div>
                            <h3 class="mb-2 text-lg font-bold tracking-tight">
                                Payment Gateway
                            </h3>
                            <p
                                class="text-sm leading-relaxed text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Multi-provider billing with Stripe, PayPal, and
                                crypto. Subscriptions, invoices, refunds, and
                                customer management built in.
                            </p>
                            <!-- Provider pills -->
                            <div class="mt-5 flex flex-wrap gap-2">
                                <span
                                    v-for="provider in [
                                        'Stripe',
                                        'PayPal',
                                        'Crypto',
                                        'Invoices',
                                        'Subscriptions',
                                        'Refunds',
                                    ]"
                                    :key="provider"
                                    class="rounded-full border border-[#e3e3e0]/60 bg-[#fafaf9] px-3 py-1 text-xs font-medium text-[#706f6c] dark:border-[#2a2a28] dark:bg-[#111110] dark:text-[#A1A09A]"
                                >
                                    {{ provider }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 3. RBAC — 1x1 (fills row 2 col 3) -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-[#e3e3e0]/80 bg-white/60 p-6 backdrop-blur-sm transition-all delay-150 duration-500 hover:border-[#FF2D20]/20 hover:shadow-xl hover:shadow-[#FF2D20]/5 sm:p-7 dark:border-[#2a2a28] dark:bg-white/[0.03] dark:hover:border-[#FF2D20]/20"
                        :class="
                            visible
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#FF2D20]/[0.04] via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative">
                            <div
                                class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF2D20]/10 to-[#FF2D20]/5 text-[#FF2D20] ring-1 ring-[#FF2D20]/10 transition-shadow duration-300 group-hover:shadow-md group-hover:shadow-[#FF2D20]/10"
                            >
                                <UserCog :size="18" :stroke-width="1.75" />
                            </div>
                            <h3
                                class="mb-1.5 text-sm font-bold tracking-tight sm:text-base"
                            >
                                Roles & Permissions
                            </h3>
                            <p
                                class="text-[13px] leading-relaxed text-[#706f6c] sm:text-sm dark:text-[#A1A09A]"
                            >
                                Spatie RBAC with super-admin, admin, and user
                                roles plus granular permission groups.
                            </p>
                        </div>
                    </div>

                    <!-- 4. Notifications — 1x1 (fills row 2 col 4) -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-[#e3e3e0]/80 bg-white/60 p-6 backdrop-blur-sm transition-all delay-200 duration-500 hover:border-[#FF2D20]/20 hover:shadow-xl hover:shadow-[#FF2D20]/5 sm:p-7 dark:border-[#2a2a28] dark:bg-white/[0.03] dark:hover:border-[#FF2D20]/20"
                        :class="
                            visible
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#FF2D20]/[0.04] via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative">
                            <div
                                class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF2D20]/10 to-[#FF2D20]/5 text-[#FF2D20] ring-1 ring-[#FF2D20]/10 transition-shadow duration-300 group-hover:shadow-md group-hover:shadow-[#FF2D20]/10"
                            >
                                <Bell :size="18" :stroke-width="1.75" />
                            </div>
                            <h3
                                class="mb-1.5 text-sm font-bold tracking-tight sm:text-base"
                            >
                                Notifications
                            </h3>
                            <p
                                class="text-[13px] leading-relaxed text-[#706f6c] sm:text-sm dark:text-[#A1A09A]"
                            >
                                In-app notification system with read/unread,
                                dropdown, and database-backed history.
                            </p>
                        </div>
                    </div>

                    <!-- 5. Admin Panel — 2 cols wide (row 3 left) -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-[#e3e3e0]/80 bg-white/60 p-6 backdrop-blur-sm transition-all delay-300 duration-500 hover:border-[#FF2D20]/20 hover:shadow-xl hover:shadow-[#FF2D20]/5 sm:col-span-2 sm:p-8 dark:border-[#2a2a28] dark:bg-white/[0.03] dark:hover:border-[#FF2D20]/20"
                        :class="
                            visible
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#FF2D20]/[0.04] via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative flex h-full flex-col">
                            <div
                                class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF2D20]/10 to-[#FF2D20]/5 text-[#FF2D20] ring-1 ring-[#FF2D20]/10 transition-shadow duration-300 group-hover:shadow-md group-hover:shadow-[#FF2D20]/10"
                            >
                                <Users :size="20" :stroke-width="1.75" />
                            </div>
                            <h3 class="mb-2 text-lg font-bold tracking-tight">
                                Admin Panel
                            </h3>
                            <p
                                class="mb-5 text-sm leading-relaxed text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Full dashboard with user management, database
                                inspector, settings, and impersonation.
                            </p>
                            <!-- Mock sidebar nav -->
                            <div class="mt-auto flex flex-wrap gap-1.5">
                                <span
                                    v-for="(item, idx) in [
                                        'Dashboard',
                                        'Users',
                                        'Roles',
                                        'Permissions',
                                        'Settings',
                                        'Audit Logs',
                                        'Database',
                                    ]"
                                    :key="item"
                                    class="rounded-md px-2.5 py-1.5 text-xs"
                                    :class="
                                        idx === 0
                                            ? 'bg-[#FF2D20]/10 font-medium text-[#FF2D20]'
                                            : 'border border-[#e3e3e0]/60 bg-[#fafaf9] text-[#706f6c] dark:border-[#2a2a28] dark:bg-[#111110] dark:text-[#A1A09A]'
                                    "
                                >
                                    {{ item }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Subscriber Management — 2 cols wide (row 3 right) -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-[#e3e3e0]/80 bg-white/60 p-6 backdrop-blur-sm transition-all delay-[350ms] duration-500 hover:border-[#FF2D20]/20 hover:shadow-xl hover:shadow-[#FF2D20]/5 sm:col-span-2 sm:p-7 dark:border-[#2a2a28] dark:bg-white/[0.03] dark:hover:border-[#FF2D20]/20"
                        :class="
                            visible
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#FF2D20]/[0.04] via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative">
                            <div
                                class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF2D20]/10 to-[#FF2D20]/5 text-[#FF2D20] ring-1 ring-[#FF2D20]/10 transition-shadow duration-300 group-hover:shadow-md group-hover:shadow-[#FF2D20]/10"
                            >
                                <Mail :size="18" :stroke-width="1.75" />
                            </div>
                            <h3
                                class="mb-1.5 text-sm font-bold tracking-tight sm:text-base"
                            >
                                Subscriber Management
                            </h3>
                            <p
                                class="text-[13px] leading-relaxed text-[#706f6c] sm:text-sm dark:text-[#A1A09A]"
                            >
                                Mailing lists with Mailchimp, Brevo, MailerLite,
                                ConvertKit, and HubSpot integrations. Double
                                opt-in and list management included.
                            </p>
                        </div>
                    </div>

                    <!-- 7. Audit Logs — 1x1 (row 4 col 1) -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-[#e3e3e0]/80 bg-white/60 p-6 backdrop-blur-sm transition-all delay-[400ms] duration-500 hover:border-[#FF2D20]/20 hover:shadow-xl hover:shadow-[#FF2D20]/5 sm:p-7 dark:border-[#2a2a28] dark:bg-white/[0.03] dark:hover:border-[#FF2D20]/20"
                        :class="
                            visible
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#FF2D20]/[0.04] via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative">
                            <div
                                class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF2D20]/10 to-[#FF2D20]/5 text-[#FF2D20] ring-1 ring-[#FF2D20]/10 transition-shadow duration-300 group-hover:shadow-md group-hover:shadow-[#FF2D20]/10"
                            >
                                <ScrollText :size="18" :stroke-width="1.75" />
                            </div>
                            <h3
                                class="mb-1.5 text-sm font-bold tracking-tight sm:text-base"
                            >
                                Audit Logs
                            </h3>
                            <p
                                class="text-[13px] leading-relaxed text-[#706f6c] sm:text-sm dark:text-[#A1A09A]"
                            >
                                Every action tracked with old/new values, IP,
                                user agent, and timestamps.
                            </p>
                        </div>
                    </div>

                    <!-- 8. Database Inspector — 1x1 (row 4 col 2) -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-[#e3e3e0]/80 bg-white/60 p-6 backdrop-blur-sm transition-all delay-[450ms] duration-500 hover:border-[#FF2D20]/20 hover:shadow-xl hover:shadow-[#FF2D20]/5 sm:p-7 dark:border-[#2a2a28] dark:bg-white/[0.03] dark:hover:border-[#FF2D20]/20"
                        :class="
                            visible
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#FF2D20]/[0.04] via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative">
                            <div
                                class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF2D20]/10 to-[#FF2D20]/5 text-[#FF2D20] ring-1 ring-[#FF2D20]/10 transition-shadow duration-300 group-hover:shadow-md group-hover:shadow-[#FF2D20]/10"
                            >
                                <Database :size="18" :stroke-width="1.75" />
                            </div>
                            <h3
                                class="mb-1.5 text-sm font-bold tracking-tight sm:text-base"
                            >
                                Database Inspector
                            </h3>
                            <p
                                class="text-[13px] leading-relaxed text-[#706f6c] sm:text-sm dark:text-[#A1A09A]"
                            >
                                Browse tables, columns, indexes, and data across
                                multiple connections from the admin UI.
                            </p>
                        </div>
                    </div>

                    <!-- 9. Dark Mode — 1x1 (row 4 col 3) -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-[#e3e3e0]/80 bg-white/60 p-6 backdrop-blur-sm transition-all delay-500 duration-500 hover:border-[#FF2D20]/20 hover:shadow-xl hover:shadow-[#FF2D20]/5 sm:p-7 dark:border-[#2a2a28] dark:bg-white/[0.03] dark:hover:border-[#FF2D20]/20"
                        :class="
                            visible
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#FF2D20]/[0.04] via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative">
                            <div
                                class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF2D20]/10 to-[#FF2D20]/5 text-[#FF2D20] ring-1 ring-[#FF2D20]/10 transition-shadow duration-300 group-hover:shadow-md group-hover:shadow-[#FF2D20]/10"
                            >
                                <Moon :size="18" :stroke-width="1.75" />
                            </div>
                            <h3
                                class="mb-1.5 text-sm font-bold tracking-tight sm:text-base"
                            >
                                Dark Mode
                            </h3>
                            <p
                                class="text-[13px] leading-relaxed text-[#706f6c] sm:text-sm dark:text-[#A1A09A]"
                            >
                                System-aware theming across every component with
                                seamless toggling.
                            </p>
                        </div>
                    </div>

                    <!-- 10. GDPR / Cookie Consent — 1x1 (row 4 col 4) -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-[#e3e3e0]/80 bg-white/60 p-6 backdrop-blur-sm transition-all delay-[550ms] duration-500 hover:border-[#FF2D20]/20 hover:shadow-xl hover:shadow-[#FF2D20]/5 sm:p-7 dark:border-[#2a2a28] dark:bg-white/[0.03] dark:hover:border-[#FF2D20]/20"
                        :class="
                            visible
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <div
                            class="pointer-events-none absolute inset-0 bg-gradient-to-br from-[#FF2D20]/[0.04] via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        />
                        <div class="relative">
                            <div
                                class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF2D20]/10 to-[#FF2D20]/5 text-[#FF2D20] ring-1 ring-[#FF2D20]/10 transition-shadow duration-300 group-hover:shadow-md group-hover:shadow-[#FF2D20]/10"
                            >
                                <Cookie :size="18" :stroke-width="1.75" />
                            </div>
                            <h3
                                class="mb-1.5 text-sm font-bold tracking-tight sm:text-base"
                            >
                                GDPR & Cookies
                            </h3>
                            <p
                                class="text-[13px] leading-relaxed text-[#706f6c] sm:text-sm dark:text-[#A1A09A]"
                            >
                                Cookie consent banner, per-category preferences,
                                consent audit trail, and privacy policies.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <PublicFooter />
        <CookieConsentBanner />
    </div>
</template>
