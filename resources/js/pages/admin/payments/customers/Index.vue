<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ExternalLink, Search, Users } from 'lucide-vue-next';
import { ref } from 'vue';

import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import SearchEmptyState from '@/components/SearchEmptyState.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Customer {
    id: number;
    email: string;
    name: string | null;
    phone: string | null;
    stripe_id: string | null;
    paypal_id: string | null;
    is_business: boolean;
    transactions_count: number;
    subscriptions_count: number;
    user: User | null;
    created_at: string;
}

interface Pagination {
    data: Customer[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    customers: Pagination;
    filters: {
        search?: string;
        has_subscriptions?: boolean;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters?.search ?? '');

const applyFilters = (): void => {
    router.get(
        window.location.pathname,
        {
            search: searchQuery.value || null,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 300);

const clearFilters = (): void => {
    searchQuery.value = '';
    router.get(window.location.pathname, {}, { preserveState: false });
};

const hasFilters = (): boolean => {
    return !!props.filters?.search;
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Payments', href: '/admin/payments' },
    { title: 'Customers', href: '#' },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Customers" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    title="Customers"
                    description="View and manage payment customers"
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
                            placeholder="Search customers..."
                            class="pl-10"
                            @input="debouncedSearch"
                        />
                    </div>
                    <Button
                        v-if="hasFilters()"
                        variant="outline"
                        @click="clearFilters"
                    >
                        Clear
                    </Button>
                </div>

                <!-- Customers List -->
                <div v-if="customers.data.length === 0">
                    <SearchEmptyState
                        v-if="hasFilters()"
                        :search-query="filters.search || 'filtered results'"
                        @clear-search="clearFilters"
                    />
                    <EmptyState
                        v-else
                        :icon="Users"
                        title="No customers yet"
                        description="Customers will appear here once they make a payment."
                    />
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="customer in customers.data"
                        :key="customer.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-medium">
                                        {{ customer.name || customer.email }}
                                    </h3>
                                    <Badge
                                        v-if="customer.is_business"
                                        variant="outline"
                                    >
                                        Business
                                    </Badge>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    {{ customer.email }}
                                </p>
                                <div
                                    class="flex items-center gap-4 text-sm text-muted-foreground"
                                >
                                    <span
                                        v-if="customer.stripe_id"
                                        class="font-mono text-xs"
                                    >
                                        Stripe: {{ customer.stripe_id }}
                                    </span>
                                    <span
                                        v-if="customer.paypal_id"
                                        class="font-mono text-xs"
                                    >
                                        PayPal: {{ customer.paypal_id }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-sm">
                                    <span>
                                        {{
                                            customer.transactions_count
                                        }}
                                        transactions
                                    </span>
                                    <span>
                                        {{
                                            customer.subscriptions_count
                                        }}
                                        subscriptions
                                    </span>
                                </div>
                                <p
                                    v-if="customer.user"
                                    class="text-xs text-muted-foreground"
                                >
                                    Linked to:
                                    {{ customer.user.name }} ({{
                                        customer.user.email
                                    }})
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="`/admin/payments/customers/${customer.id}`"
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
                        v-if="customers.last_page > 1"
                        class="flex items-center justify-between"
                    >
                        <p class="text-sm text-muted-foreground">
                            Showing {{ customers.data.length }} of
                            {{ customers.total }} customers
                        </p>
                        <div class="flex gap-2">
                            <Button
                                v-for="link in customers.links"
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
