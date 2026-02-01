<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { CreditCard, ExternalLink, Search } from 'lucide-vue-next';
import { ref } from 'vue';

import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import SearchEmptyState from '@/components/SearchEmptyState.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { usePaymentNav } from '@/composables/usePaymentNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { decodePaginationLabel } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';

const { title: moduleTitle, icon: moduleIcon, items: moduleItems } = usePaymentNav();

interface Plan {
    id: number;
    name: string;
    slug: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface Subscription {
    id: number;
    uuid: string;
    provider_id: string | null;
    amount: number;
    formatted_amount: string;
    currency: string;
    status: string;
    driver: string;
    interval: string;
    interval_count: number;
    billing_description: string;
    plan: Plan | null;
    user: User | null;
    current_period_end: string | null;
    trial_end: string | null;
    on_trial: boolean;
    on_grace_period: boolean;
    created_at: string;
}

interface Pagination {
    data: Subscription[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Status {
    value: string;
}

interface Props {
    subscriptions: Pagination;
    filters: {
        status?: string;
        driver?: string;
        plan?: string;
        search?: string;
    };
    statuses: Status[];
    drivers: string[];
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters?.search ?? '');
const statusFilter = ref(props.filters?.status ?? '');
const driverFilter = ref(props.filters?.driver ?? '');

const applyFilters = (): void => {
    router.get(
        window.location.pathname,
        {
            search: searchQuery.value || null,
            status: statusFilter.value || null,
            driver: driverFilter.value || null,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 300);

const clearFilters = (): void => {
    searchQuery.value = '';
    statusFilter.value = '';
    driverFilter.value = '';
    router.get(window.location.pathname, {}, { preserveState: false });
};

const getStatusColor = (status: string): string => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
        case 'trialing':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400';
        case 'canceled':
            return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
        case 'paused':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
        case 'past_due':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
    }
};

const hasFilters = (): boolean => {
    return !!(
        props.filters?.search ||
        props.filters?.status ||
        props.filters?.driver
    );
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Payments', href: '/admin/payments' },
    { title: 'Subscriptions', href: '#' },
];
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Subscriptions" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    title="Subscriptions"
                    description="View and manage subscriptions"
                    variant="small"
                />

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative min-w-[200px] flex-1">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search subscriptions..."
                            class="pl-10"
                            @input="debouncedSearch"
                        />
                    </div>
                    <select
                        v-model="statusFilter"
                        class="rounded-md border bg-background px-3 py-2 text-sm"
                        @change="applyFilters"
                    >
                        <option value="">All Statuses</option>
                        <option
                            v-for="status in statuses"
                            :key="status.value"
                            :value="status.value"
                        >
                            {{ status.value }}
                        </option>
                    </select>
                    <select
                        v-model="driverFilter"
                        class="rounded-md border bg-background px-3 py-2 text-sm"
                        @change="applyFilters"
                    >
                        <option value="">All Drivers</option>
                        <option
                            v-for="driver in drivers"
                            :key="driver"
                            :value="driver"
                        >
                            {{ driver }}
                        </option>
                    </select>
                    <Button
                        v-if="hasFilters()"
                        variant="outline"
                        @click="clearFilters"
                    >
                        Clear
                    </Button>
                </div>

                <!-- Subscriptions List -->
                <div v-if="subscriptions.data.length === 0">
                    <SearchEmptyState
                        v-if="hasFilters()"
                        :search-query="filters.search || 'filtered results'"
                        @clear-search="clearFilters"
                    />
                    <EmptyState
                        v-else
                        :icon="CreditCard"
                        title="No subscriptions yet"
                        description="Subscriptions will appear here once customers subscribe to plans."
                    />
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="sub in subscriptions.data"
                        :key="sub.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-medium">
                                        {{ sub.plan?.name || 'Custom Plan' }}
                                    </h3>
                                    <Badge :class="getStatusColor(sub.status)">
                                        {{ sub.status }}
                                    </Badge>
                                    <Badge
                                        v-if="sub.on_trial"
                                        class="bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                                    >
                                        Trial
                                    </Badge>
                                    <Badge
                                        v-if="sub.on_grace_period"
                                        class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400"
                                    >
                                        Grace Period
                                    </Badge>
                                    <Badge variant="outline">
                                        {{ sub.driver }}
                                    </Badge>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{ sub.billing_description }}
                                </p>
                                <div
                                    class="flex items-center gap-4 text-sm text-muted-foreground"
                                >
                                    <span v-if="sub.user">
                                        {{ sub.user.name }} ({{
                                            sub.user.email
                                        }})
                                    </span>
                                </div>
                                <div
                                    class="flex items-center gap-4 text-xs text-muted-foreground"
                                >
                                    <span v-if="sub.current_period_end">
                                        Renews:
                                        {{
                                            new Date(
                                                sub.current_period_end,
                                            ).toLocaleDateString()
                                        }}
                                    </span>
                                    <span v-if="sub.trial_end">
                                        Trial ends:
                                        {{
                                            new Date(
                                                sub.trial_end,
                                            ).toLocaleDateString()
                                        }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-semibold">
                                    {{ sub.formatted_amount }}
                                </span>
                                <Link
                                    :href="`/admin/payments/subscriptions/${sub.id}`"
                                    class="flex items-center gap-1 text-sm text-primary hover:underline"
                                >
                                    View
                                    <ExternalLink class="h-3 w-3" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="subscriptions.last_page > 1"
                        class="flex items-center justify-between"
                    >
                        <p class="text-sm text-muted-foreground">
                            Showing {{ subscriptions.data.length }} of
                            {{ subscriptions.total }} subscriptions
                        </p>
                        <div class="flex gap-2">
                            <Button
                                v-for="link in subscriptions.links"
                                :key="link.label"
                                variant="outline"
                                size="sm"
                                :disabled="!link.url"
                                @click="link.url && router.get(link.url)"
                            >
                                {{ decodePaginationLabel(link.label) }}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
