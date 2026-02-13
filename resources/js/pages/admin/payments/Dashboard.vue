<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowDownRight,
    ArrowUpRight,
    CreditCard,
    DollarSign,
    FileText,
    Receipt,
    RefreshCw,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePaymentNav } from '@/composables/usePaymentNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const { title: moduleTitle, icon: moduleIcon, items: moduleItems } = usePaymentNav();

interface Transaction {
    id: number;
    uuid: string;
    amount: number;
    formatted_amount: string;
    currency: string;
    status: string;
    driver: string;
    description: string | null;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    created_at: string;
}

interface Stats {
    revenue: {
        total: number;
        formatted: string;
        change: number;
    };
    transactions: {
        successful: number;
        failed: number;
        total: number;
    };
    subscriptions: {
        active: number;
        new: number;
    };
    customers: {
        total: number;
        new: number;
    };
    mrr: {
        total: number;
        formatted: string;
    };
    currency: string;
}

interface ChartData {
    date: string;
    label: string;
    total: number;
    formatted: string;
}

interface Props {
    stats: Stats;
    recentTransactions: Transaction[];
    revenueChart: ChartData[];
    period: string;
}

const props = defineProps<Props>();

const selectedPeriod = ref(props.period);

const periods = [
    { value: '7', label: '7 days' },
    { value: '30', label: '30 days' },
    { value: '90', label: '90 days' },
    { value: '365', label: '1 year' },
];

const changePeriod = (period: string): void => {
    selectedPeriod.value = period;
    router.get(
        window.location.pathname,
        { period },
        { preserveState: true, preserveScroll: true },
    );
};

const getStatusColor = (status: string): string => {
    switch (status) {
        case 'succeeded':
            return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
        case 'failed':
            return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
    }
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Payments', href: '#' },
    { title: 'Dashboard', href: '#' },
];
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Payment Dashboard" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        title="Payment Dashboard"
                        description="Overview of your payment activity"
                        variant="small"
                    />
                    <div class="flex items-center gap-2">
                        <select
                            v-model="selectedPeriod"
                            class="rounded-md border bg-background px-3 py-2 text-sm"
                            @change="changePeriod(selectedPeriod)"
                        >
                            <option
                                v-for="period in periods"
                                :key="period.value"
                                :value="period.value"
                            >
                                {{ period.label }}
                            </option>
                        </select>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="router.reload()"
                        >
                            <RefreshCw class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium"
                                >Total Revenue</CardTitle
                            >
                            <DollarSign class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ stats.revenue.formatted }}
                            </div>
                            <div class="flex items-center text-xs">
                                <component
                                    :is="
                                        stats.revenue.change >= 0
                                            ? ArrowUpRight
                                            : ArrowDownRight
                                    "
                                    :class="[
                                        'h-4 w-4',
                                        stats.revenue.change >= 0
                                            ? 'text-green-500'
                                            : 'text-red-500',
                                    ]"
                                />
                                <span
                                    :class="
                                        stats.revenue.change >= 0
                                            ? 'text-green-500'
                                            : 'text-red-500'
                                    "
                                >
                                    {{ Math.abs(stats.revenue.change) }}%
                                </span>
                                <span class="ml-1 text-muted-foreground"
                                    >from last period</span
                                >
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium"
                                >MRR</CardTitle
                            >
                            <TrendingUp class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ stats.mrr.formatted }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Monthly recurring revenue
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium"
                                >Active Subscriptions</CardTitle
                            >
                            <CreditCard class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ stats.subscriptions.active }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                +{{ stats.subscriptions.new }} new this period
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium"
                                >Customers</CardTitle
                            >
                            <Users class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ stats.customers.total }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                +{{ stats.customers.new }} new this period
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Transaction Stats -->
                <div class="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium"
                                >Successful</CardTitle
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-green-600">
                                {{ stats.transactions.successful }}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium"
                                >Failed</CardTitle
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-red-600">
                                {{ stats.transactions.failed }}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium"
                                >Success Rate</CardTitle
                            >
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{
                                    stats.transactions.total > 0
                                        ? Math.round(
                                              (stats.transactions.successful /
                                                  stats.transactions.total) *
                                                  100,
                                          )
                                        : 0
                                }}%
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Revenue Chart -->
                <Card v-if="revenueChart.length > 0">
                    <CardHeader>
                        <CardTitle>Revenue Over Time</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex h-64 items-end gap-1">
                            <div
                                v-for="(item, idx) in revenueChart"
                                :key="idx"
                                class="flex flex-1 flex-col items-center gap-1"
                            >
                                <div
                                    class="w-full rounded-t bg-primary/80 transition-all hover:bg-primary"
                                    :style="{
                                        height: `${Math.max(4, (item.total / Math.max(...revenueChart.map((c) => c.total))) * 200)}px`,
                                    }"
                                    :title="`${item.label}: ${item.formatted}`"
                                />
                                <span
                                    class="w-full truncate text-center text-xs text-muted-foreground"
                                >
                                    {{ item.label }}
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Links & Recent Transactions -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Quick Links -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Links</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-3">
                                <Link
                                    href="/admin/payments/transactions"
                                    class="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent"
                                >
                                    <Receipt class="h-5 w-5 text-primary" />
                                    <span class="text-sm font-medium"
                                        >Transactions</span
                                    >
                                </Link>
                                <Link
                                    href="/admin/payments/subscriptions"
                                    class="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent"
                                >
                                    <CreditCard class="h-5 w-5 text-primary" />
                                    <span class="text-sm font-medium"
                                        >Subscriptions</span
                                    >
                                </Link>
                                <Link
                                    href="/admin/payments/plans"
                                    class="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent"
                                >
                                    <FileText class="h-5 w-5 text-primary" />
                                    <span class="text-sm font-medium"
                                        >Plans</span
                                    >
                                </Link>
                                <Link
                                    href="/admin/payments/customers"
                                    class="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent"
                                >
                                    <Users class="h-5 w-5 text-primary" />
                                    <span class="text-sm font-medium"
                                        >Customers</span
                                    >
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Transactions -->
                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between"
                        >
                            <CardTitle>Recent Transactions</CardTitle>
                            <Link
                                href="/admin/payments/transactions"
                                class="text-sm text-primary hover:underline"
                            >
                                View all
                            </Link>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="recentTransactions.length === 0"
                                class="py-6 text-center text-muted-foreground"
                            >
                                No transactions yet
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="tx in recentTransactions.slice(0, 5)"
                                    :key="tx.id"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10"
                                        >
                                            <DollarSign
                                                class="h-4 w-4 text-primary"
                                            />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium">
                                                {{
                                                    tx.user?.name ||
                                                    tx.description ||
                                                    'Payment'
                                                }}
                                            </p>
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                {{
                                                    new Date(
                                                        tx.created_at,
                                                    ).toLocaleDateString()
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Badge
                                            :class="getStatusColor(tx.status)"
                                        >
                                            {{ tx.status }}
                                        </Badge>
                                        <span class="text-sm font-medium">
                                            {{ tx.formatted_amount }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
