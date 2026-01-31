<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Download, ExternalLink, FileText, Search } from 'lucide-vue-next';
import { ref } from 'vue';

import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import SearchEmptyState from '@/components/SearchEmptyState.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Invoice {
    id: number;
    uuid: string;
    number: string;
    status: string;
    total: number;
    formatted_total: string;
    amount_due: number;
    formatted_amount_due: string;
    currency: string;
    billing_name: string | null;
    billing_email: string | null;
    invoice_date: string;
    due_date: string | null;
    paid_at: string | null;
    is_overdue: boolean;
    has_pdf: boolean;
    user: User | null;
}

interface Pagination {
    data: Invoice[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Status {
    value: string;
}

interface Summary {
    total_paid: number;
    formatted_total_paid: string;
    total_open: number;
    formatted_total_open: string;
    total_overdue: number;
    formatted_total_overdue: string;
}

interface Props {
    invoices: Pagination;
    filters: {
        status?: string;
        search?: string;
        from?: string;
        to?: string;
    };
    statuses: Status[];
    summary: Summary;
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters?.search ?? '');
const statusFilter = ref(props.filters?.status ?? '');
const fromDate = ref(props.filters?.from ?? '');
const toDate = ref(props.filters?.to ?? '');

const applyFilters = (): void => {
    router.get(
        window.location.pathname,
        {
            search: searchQuery.value || null,
            status: statusFilter.value || null,
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
    fromDate.value = '';
    toDate.value = '';
    router.get(window.location.pathname, {}, { preserveState: false });
};

const getStatusColor = (status: string, isOverdue: boolean): string => {
    if (isOverdue) {
        return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
    }
    switch (status) {
        case 'paid':
            return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
        case 'open':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400';
        case 'draft':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
        case 'void':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
    }
};

const hasFilters = (): boolean => {
    return !!(
        props.filters?.search ||
        props.filters?.status ||
        props.filters?.from ||
        props.filters?.to
    );
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Payments', href: '/admin/payments' },
    { title: 'Invoices', href: '#' },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Invoices" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    title="Invoices"
                    description="View and manage invoices"
                    variant="small"
                />

                <!-- Summary Cards -->
                <div class="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">
                                Total Paid
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-green-600">
                                {{ summary.formatted_total_paid }}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">
                                Open
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-blue-600">
                                {{ summary.formatted_total_open }}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">
                                Overdue
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-red-600">
                                {{ summary.formatted_total_overdue }}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative min-w-[200px] flex-1">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search invoices..."
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

                <!-- Invoices List -->
                <div v-if="invoices.data.length === 0">
                    <SearchEmptyState
                        v-if="hasFilters()"
                        :search-query="filters.search || 'filtered results'"
                        @clear-search="clearFilters"
                    />
                    <EmptyState
                        v-else
                        :icon="FileText"
                        title="No invoices yet"
                        description="Invoices will appear here once they are generated."
                    />
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="invoice in invoices.data"
                        :key="invoice.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-medium">
                                        {{ invoice.number }}
                                    </h3>
                                    <Badge
                                        :class="
                                            getStatusColor(
                                                invoice.status,
                                                invoice.is_overdue,
                                            )
                                        "
                                    >
                                        {{
                                            invoice.is_overdue
                                                ? 'Overdue'
                                                : invoice.status
                                        }}
                                    </Badge>
                                </div>
                                <p class="text-lg font-semibold">
                                    {{ invoice.formatted_total }}
                                    <span
                                        v-if="invoice.amount_due > 0"
                                        class="text-sm font-normal text-muted-foreground"
                                    >
                                        ({{ invoice.formatted_amount_due }} due)
                                    </span>
                                </p>
                                <div
                                    class="flex items-center gap-4 text-sm text-muted-foreground"
                                >
                                    <span v-if="invoice.billing_name">
                                        {{ invoice.billing_name }}
                                    </span>
                                    <span v-if="invoice.billing_email">
                                        {{ invoice.billing_email }}
                                    </span>
                                    <span v-if="invoice.user">
                                        User: {{ invoice.user.name }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center gap-4 text-xs text-muted-foreground"
                                >
                                    <span>
                                        Issued:
                                        {{
                                            new Date(
                                                invoice.invoice_date,
                                            ).toLocaleDateString()
                                        }}
                                    </span>
                                    <span v-if="invoice.due_date">
                                        Due:
                                        {{
                                            new Date(
                                                invoice.due_date,
                                            ).toLocaleDateString()
                                        }}
                                    </span>
                                    <span v-if="invoice.paid_at">
                                        Paid:
                                        {{
                                            new Date(
                                                invoice.paid_at,
                                            ).toLocaleDateString()
                                        }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a
                                    v-if="invoice.has_pdf"
                                    :href="`/admin/payments/invoices/${invoice.id}/download`"
                                    class="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                                >
                                    <Download class="h-4 w-4" />
                                </a>
                                <Link
                                    :href="`/admin/payments/invoices/${invoice.id}`"
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
                        v-if="invoices.last_page > 1"
                        class="flex items-center justify-between"
                    >
                        <p class="text-sm text-muted-foreground">
                            Showing {{ invoices.data.length }} of
                            {{ invoices.total }} invoices
                        </p>
                        <div class="flex gap-2">
                            <Button
                                v-for="link in invoices.links"
                                :key="link.label"
                                variant="outline"
                                size="sm"
                                :disabled="!link.url"
                                @click="link.url && router.get(link.url)"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
