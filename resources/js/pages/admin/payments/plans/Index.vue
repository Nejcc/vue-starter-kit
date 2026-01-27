<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ExternalLink, FileText, Plus, Search } from 'lucide-vue-next';
import { ref } from 'vue';

import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import SearchEmptyState from '@/components/SearchEmptyState.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Plan {
    id: number;
    uuid: string;
    name: string;
    slug: string;
    description: string | null;
    amount: number;
    formatted_price: string;
    billing_description: string;
    currency: string;
    interval: string;
    interval_count: number;
    trial_days: number | null;
    is_active: boolean;
    is_public: boolean;
    is_featured: boolean;
    is_free: boolean;
    sort_order: number;
    subscriptions_count: number;
    stripe_price_id: string | null;
    paypal_plan_id: string | null;
    created_at: string;
}

interface Pagination {
    data: Plan[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    plans: Pagination;
    filters: {
        interval?: string;
        active?: string;
        search?: string;
    };
    intervals: string[];
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters?.search ?? '');
const intervalFilter = ref(props.filters?.interval ?? '');
const activeFilter = ref(props.filters?.active ?? '');

const applyFilters = (): void => {
    router.get(
        window.location.pathname,
        {
            search: searchQuery.value || null,
            interval: intervalFilter.value || null,
            active: activeFilter.value || null,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 300);

const clearFilters = (): void => {
    searchQuery.value = '';
    intervalFilter.value = '';
    activeFilter.value = '';
    router.get(window.location.pathname, {}, { preserveState: false });
};

const hasFilters = (): boolean => {
    return !!(
        props.filters?.search ||
        props.filters?.interval ||
        props.filters?.active
    );
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Payments', href: '/admin/payments' },
    { title: 'Plans', href: '#' },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Plans" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        title="Plans"
                        description="Manage subscription plans"
                        variant="small"
                    />
                    <Link
                        href="/admin/payments/plans/create"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Create Plan
                    </Link>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 min-w-[200px]">
                        <Search
                            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search plans..."
                            class="pl-10"
                            @input="debouncedSearch"
                        />
                    </div>
                    <select
                        v-model="intervalFilter"
                        class="rounded-md border bg-background px-3 py-2 text-sm"
                        @change="applyFilters"
                    >
                        <option value="">All Intervals</option>
                        <option
                            v-for="interval in intervals"
                            :key="interval"
                            :value="interval"
                        >
                            {{ interval }}
                        </option>
                    </select>
                    <select
                        v-model="activeFilter"
                        class="rounded-md border bg-background px-3 py-2 text-sm"
                        @change="applyFilters"
                    >
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <Button
                        v-if="hasFilters()"
                        variant="outline"
                        @click="clearFilters"
                    >
                        Clear
                    </Button>
                </div>

                <!-- Plans List -->
                <div v-if="plans.data.length === 0">
                    <SearchEmptyState
                        v-if="hasFilters()"
                        :search-query="filters.search || 'filtered results'"
                        @clear-search="clearFilters"
                    />
                    <EmptyState
                        v-else
                        :icon="FileText"
                        title="No plans yet"
                        description="Get started by creating your first subscription plan."
                        action-text="Create Plan"
                        action-href="/admin/payments/plans/create"
                    />
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="plan in plans.data"
                        :key="plan.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-medium">
                                        {{ plan.name }}
                                    </h3>
                                    <Badge
                                        v-if="plan.is_active"
                                        class="bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400"
                                    >
                                        Active
                                    </Badge>
                                    <Badge
                                        v-else
                                        class="bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400"
                                    >
                                        Inactive
                                    </Badge>
                                    <Badge
                                        v-if="plan.is_featured"
                                        class="bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400"
                                    >
                                        Featured
                                    </Badge>
                                    <Badge v-if="plan.is_free" variant="outline">
                                        Free
                                    </Badge>
                                </div>
                                <p class="text-lg font-semibold">
                                    {{ plan.formatted_price }}
                                    <span class="text-sm font-normal text-muted-foreground">
                                        / {{ plan.billing_description }}
                                    </span>
                                </p>
                                <p
                                    v-if="plan.description"
                                    class="text-sm text-muted-foreground"
                                >
                                    {{ plan.description }}
                                </p>
                                <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                    <span>
                                        {{ plan.subscriptions_count }} subscriber(s)
                                    </span>
                                    <span v-if="plan.trial_days">
                                        {{ plan.trial_days }} day trial
                                    </span>
                                    <span v-if="plan.stripe_price_id" class="font-mono text-xs">
                                        Stripe synced
                                    </span>
                                    <span v-if="plan.paypal_plan_id" class="font-mono text-xs">
                                        PayPal synced
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="`/admin/payments/plans/${plan.id}/edit`"
                                    class="flex items-center gap-1 text-sm text-primary hover:underline"
                                >
                                    Edit
                                    <ExternalLink class="h-3 w-3" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="plans.last_page > 1"
                        class="flex items-center justify-between"
                    >
                        <p class="text-sm text-muted-foreground">
                            Showing {{ plans.data.length }} of
                            {{ plans.total }} plans
                        </p>
                        <div class="flex gap-2">
                            <Button
                                v-for="link in plans.links"
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
