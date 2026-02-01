<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ExternalLink, Receipt, Search } from 'lucide-vue-next';
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

interface User {
    id: number;
    name: string;
    email: string;
}

interface Transaction {
    id: number;
    uuid: string;
    provider_id: string | null;
    amount: number;
    formatted_amount: string;
    currency: string;
    status: string;
    driver: string;
    description: string | null;
    user: User | null;
    created_at: string;
    can_refund: boolean;
}

interface Pagination {
    data: Transaction[];
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
    transactions: Pagination;
    filters: {
        status?: string;
        driver?: string;
        search?: string;
        from?: string;
        to?: string;
    };
    statuses: Status[];
    drivers: string[];
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters?.search ?? '');
const statusFilter = ref(props.filters?.status ?? '');
const driverFilter = ref(props.filters?.driver ?? '');
const fromDate = ref(props.filters?.from ?? '');
const toDate = ref(props.filters?.to ?? '');

const applyFilters = (): void => {
    router.get(
        window.location.pathname,
        {
            search: searchQuery.value || null,
            status: statusFilter.value || null,
            driver: driverFilter.value || null,
            from: fromDate.value || null,
            to: toDate.value || null,
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
    fromDate.value = '';
    toDate.value = '';
    router.get(window.location.pathname, {}, { preserveState: false });
};

const getStatusColor = (status: string): string => {
    switch (status) {
        case 'succeeded':
            return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
        case 'failed':
            return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
        case 'refunded':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
    }
};

const hasFilters = (): boolean => {
    return !!(
        props.filters?.search ||
        props.filters?.status ||
        props.filters?.driver ||
        props.filters?.from ||
        props.filters?.to
    );
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Payments', href: '/admin/payments' },
    { title: 'Transactions', href: '#' },
];
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Transactions" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    title="Transactions"
                    description="View and manage payment transactions"
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
                            placeholder="Search transactions..."
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
                    <Input
                        v-model="fromDate"
                        type="date"
                        placeholder="From"
                        class="w-40"
                        @change="applyFilters"
                    />
                    <Input
                        v-model="toDate"
                        type="date"
                        placeholder="To"
                        class="w-40"
                        @change="applyFilters"
                    />
                    <Button
                        v-if="hasFilters()"
                        variant="outline"
                        @click="clearFilters"
                    >
                        Clear
                    </Button>
                </div>

                <!-- Transactions List -->
                <div v-if="transactions.data.length === 0">
                    <SearchEmptyState
                        v-if="hasFilters()"
                        :search-query="filters.search || 'filtered results'"
                        @clear-search="clearFilters"
                    />
                    <EmptyState
                        v-else
                        :icon="Receipt"
                        title="No transactions yet"
                        description="Transactions will appear here once payments are processed."
                    />
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="tx in transactions.data"
                        :key="tx.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-medium">
                                        {{ tx.formatted_amount }}
                                    </h3>
                                    <Badge :class="getStatusColor(tx.status)">
                                        {{ tx.status }}
                                    </Badge>
                                    <Badge variant="outline">
                                        {{ tx.driver }}
                                    </Badge>
                                </div>
                                <p
                                    v-if="tx.description"
                                    class="text-sm text-muted-foreground"
                                >
                                    {{ tx.description }}
                                </p>
                                <div
                                    class="flex items-center gap-4 text-sm text-muted-foreground"
                                >
                                    <span v-if="tx.user">
                                        {{ tx.user.name }} ({{ tx.user.email }})
                                    </span>
                                    <span
                                        v-if="tx.provider_id"
                                        class="font-mono text-xs"
                                    >
                                        {{ tx.provider_id }}
                                    </span>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        new Date(tx.created_at).toLocaleString()
                                    }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="`/admin/payments/transactions/${tx.id}`"
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
                        v-if="transactions.last_page > 1"
                        class="flex items-center justify-between"
                    >
                        <p class="text-sm text-muted-foreground">
                            Showing {{ transactions.data.length }} of
                            {{ transactions.total }} transactions
                        </p>
                        <div class="flex gap-2">
                            <Button
                                v-for="link in transactions.links"
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
