<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ShoppingBag, Trash2 } from 'lucide-vue-next';

import DataCard from '@/components/DataCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchEmptyState from '@/components/SearchEmptyState.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useEcommerceNav } from '@ecommerce/composables/useEcommerceNav';
import { useSearch } from '@/composables/useSearch';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem, type PaginatedResponse } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useEcommerceNav();

interface OrderUser {
    id: number;
    name: string;
    email: string;
}

interface Order {
    id: number;
    uuid: string;
    order_number: string;
    status: string;
    total: number;
    currency: string;
    user: OrderUser | null;
    items_count?: number;
    created_at: string;
}

interface StatusOption {
    value: string;
    label: string;
    color: string;
}

interface OrdersPageProps {
    orders: PaginatedResponse<Order>;
    statuses: StatusOption[];
    filters?: {
        search?: string;
        status?: string | null;
    };
    status?: string;
}

const props = defineProps<OrdersPageProps>();
const { searchQuery, handleSearch, clearSearch } = useSearch({
    url: '/admin/ecommerce/orders',
});

if (props.filters?.search) {
    searchQuery.value = props.filters.search;
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Orders', href: '#' },
];

function formatPrice(cents: number, currency: string): string {
    const amount = cents / 100;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
    }).format(amount);
}

function statusVariant(
    color: string,
): 'success' | 'warning' | 'destructive' | 'default' | 'purple' {
    switch (color) {
        case 'green':
        case 'teal':
            return 'success';
        case 'yellow':
        case 'amber':
            return 'warning';
        case 'red':
            return 'destructive';
        case 'indigo':
        case 'blue':
        case 'cyan':
            return 'purple';
        default:
            return 'default';
    }
}

function getStatusColor(statusValue: string): string {
    const found = props.statuses.find((s) => s.value === statusValue);
    return found?.color ?? 'gray';
}

function getStatusLabel(statusValue: string): string {
    const found = props.statuses.find((s) => s.value === statusValue);
    return found?.label ?? statusValue;
}

function filterByStatus(value: string) {
    const url = new URL(window.location.href);
    if (value === 'all') {
        url.searchParams.delete('status');
    } else {
        url.searchParams.set('status', value);
    }
    url.searchParams.delete('page');
    router.get(url.pathname + url.search);
}

function deleteOrder(uuid: string) {
    if (confirm('Are you sure you want to delete this order?')) {
        router.delete(`/admin/ecommerce/orders/${uuid}`);
    }
}
</script>

<template>
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head title="Orders" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Orders"
                        description="Manage customer orders"
                    />
                </div>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <SearchInput
                            v-model="searchQuery"
                            placeholder="Search orders..."
                            show-clear
                            @search="handleSearch"
                            @clear="clearSearch"
                        />
                    </div>
                    <Select
                        :model-value="filters?.status ?? 'all'"
                        @update:model-value="filterByStatus"
                    >
                        <SelectTrigger class="w-full sm:w-[180px]">
                            <SelectValue placeholder="Filter by status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Statuses</SelectItem>
                            <SelectItem
                                v-for="s in statuses"
                                :key="s.value"
                                :value="s.value"
                            >
                                {{ s.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <template v-if="orders.data.length > 0">
                    <div class="grid gap-4">
                        <DataCard
                            v-for="order in orders.data"
                            :key="order.id"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                                    >
                                        <ShoppingBag
                                            class="h-5 w-5 text-primary"
                                        />
                                    </div>
                                    <div>
                                        <Link
                                            :href="`/admin/ecommerce/orders/${order.uuid}`"
                                            class="font-medium hover:underline"
                                        >
                                            {{ order.order_number }}
                                        </Link>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            <span v-if="order.user">
                                                {{ order.user.name }}
                                                &middot;
                                            </span>
                                            {{
                                                formatPrice(
                                                    order.total,
                                                    order.currency,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <StatusBadge
                                    :label="getStatusLabel(order.status)"
                                    :variant="
                                        statusVariant(
                                            getStatusColor(order.status),
                                        )
                                    "
                                />
                            </div>

                            <template #footer>
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link
                                        :href="`/admin/ecommerce/orders/${order.uuid}`"
                                    >
                                        <Button variant="outline" size="sm">
                                            View
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="text-destructive hover:text-destructive"
                                        @click="deleteOrder(order.uuid)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </template>
                        </DataCard>
                    </div>

                    <Pagination :pagination="orders" />
                </template>

                <SearchEmptyState
                    v-else-if="filters?.search"
                    title="No orders found"
                    :search-query="filters.search"
                    @clear="clearSearch"
                />

                <EmptyState
                    v-else
                    title="No orders yet"
                    description="Orders will appear here when customers place them."
                />
            </div>
        </div>
    </ModuleLayout>
</template>
