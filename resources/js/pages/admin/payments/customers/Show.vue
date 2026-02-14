<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CreditCard, Receipt } from 'lucide-vue-next';

import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePaymentNav } from '@/composables/usePaymentNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = usePaymentNav();

interface Transaction {
    id: number;
    uuid: string;
    amount: number;
    formatted_amount: string;
    status: string;
    driver: string;
    created_at: string;
}

interface Subscription {
    id: number;
    uuid: string;
    status: string;
    billing_description: string;
    plan: {
        id: number;
        name: string;
    } | null;
    current_period_end: string | null;
    created_at: string;
}

interface PaymentMethod {
    id: number;
    type: string;
    display_name: string;
    is_default: boolean;
    is_expired: boolean;
    expiry_string: string | null;
    driver: string;
}

interface Customer {
    id: number;
    email: string;
    name: string | null;
    phone: string | null;
    stripe_id: string | null;
    paypal_id: string | null;
    crypto_id: string | null;
    preferred_locale: string | null;
    tax_id: string | null;
    vat_number: string | null;
    is_business: boolean;
    is_primary: boolean;
    company: string | null;
    billing_address: Record<string, any> | null;
    shipping_address: Record<string, any> | null;
    invoice_address: Record<string, any> | null;
    metadata: Record<string, any> | null;
    total_spent: number;
    formatted_total_spent: string;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    transactions: Transaction[];
    subscriptions: Subscription[];
    payment_methods: PaymentMethod[];
    created_at: string;
    updated_at: string;
}

interface Props {
    customer: Customer;
}

const props = defineProps<Props>();

const getStatusColor = (status: string): string => {
    switch (status) {
        case 'succeeded':
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
        case 'pending':
        case 'trialing':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
        case 'failed':
        case 'canceled':
            return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
    }
};

const formatAddress = (address: Record<string, any> | null): string => {
    if (!address) return '';
    const parts = [
        address.line1,
        address.line2,
        address.city,
        address.state,
        address.postal_code,
        address.country,
    ].filter(Boolean);
    return parts.join(', ');
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Payments', href: '/admin/payments' },
    { title: 'Customers', href: '/admin/payments/customers' },
    { title: props.customer.name || props.customer.email, href: '#' },
];
</script>

<template>
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head :title="`Customer: ${customer.name || customer.email}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/payments/customers"
                        class="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back
                    </Link>
                </div>

                <div class="flex items-center justify-between">
                    <Heading
                        :title="customer.name || customer.email"
                        :description="customer.email"
                        variant="small"
                    />
                    <div class="flex items-center gap-2">
                        <Badge v-if="customer.is_business" variant="outline">
                            Business
                        </Badge>
                        <Badge v-if="customer.is_primary" variant="outline">
                            Primary
                        </Badge>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">
                                Total Spent
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ customer.formatted_total_spent }}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">
                                Transactions
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ customer.transactions.length }}
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">
                                Subscriptions
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ customer.subscriptions.length }}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Customer Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Customer Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Email
                                    </p>
                                    <p class="font-medium">
                                        {{ customer.email }}
                                    </p>
                                </div>
                                <div v-if="customer.name">
                                    <p class="text-sm text-muted-foreground">
                                        Name
                                    </p>
                                    <p class="font-medium">
                                        {{ customer.name }}
                                    </p>
                                </div>
                                <div v-if="customer.phone">
                                    <p class="text-sm text-muted-foreground">
                                        Phone
                                    </p>
                                    <p class="font-medium">
                                        {{ customer.phone }}
                                    </p>
                                </div>
                                <div v-if="customer.company">
                                    <p class="text-sm text-muted-foreground">
                                        Company
                                    </p>
                                    <p class="font-medium">
                                        {{ customer.company }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="customer.stripe_id">
                                <p class="text-sm text-muted-foreground">
                                    Stripe ID
                                </p>
                                <p class="font-mono text-sm">
                                    {{ customer.stripe_id }}
                                </p>
                            </div>
                            <div v-if="customer.paypal_id">
                                <p class="text-sm text-muted-foreground">
                                    PayPal ID
                                </p>
                                <p class="font-mono text-sm">
                                    {{ customer.paypal_id }}
                                </p>
                            </div>
                            <div v-if="customer.tax_id || customer.vat_number">
                                <div class="grid grid-cols-2 gap-4">
                                    <div v-if="customer.tax_id">
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Tax ID
                                        </p>
                                        <p class="font-medium">
                                            {{ customer.tax_id }}
                                        </p>
                                    </div>
                                    <div v-if="customer.vat_number">
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            VAT Number
                                        </p>
                                        <p class="font-medium">
                                            {{ customer.vat_number }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Linked User -->
                    <Card v-if="customer.user">
                        <CardHeader>
                            <CardTitle>Linked User</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Name
                                </p>
                                <p class="font-medium">
                                    {{ customer.user.name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Email
                                </p>
                                <p class="font-medium">
                                    {{ customer.user.email }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Billing Address -->
                    <Card v-if="customer.billing_address">
                        <CardHeader>
                            <CardTitle>Billing Address</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm">
                                {{ formatAddress(customer.billing_address) }}
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Payment Methods -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Payment Methods</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="customer.payment_methods.length === 0"
                                class="text-sm text-muted-foreground"
                            >
                                No payment methods
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="pm in customer.payment_methods"
                                    :key="pm.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div class="flex items-center gap-3">
                                        <CreditCard
                                            class="h-5 w-5 text-muted-foreground"
                                        />
                                        <div>
                                            <p class="font-medium">
                                                {{ pm.display_name }}
                                            </p>
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                {{ pm.type }} - {{ pm.driver }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Badge
                                            v-if="pm.is_default"
                                            variant="outline"
                                        >
                                            Default
                                        </Badge>
                                        <Badge
                                            v-if="pm.is_expired"
                                            class="bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400"
                                        >
                                            Expired
                                        </Badge>
                                        <span
                                            v-if="pm.expiry_string"
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ pm.expiry_string }}
                                        </span>
                                    </div>
                                </div>
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
                                v-if="customer.transactions.length === 0"
                                class="text-sm text-muted-foreground"
                            >
                                No transactions
                            </div>
                            <div v-else class="space-y-3">
                                <Link
                                    v-for="tx in customer.transactions"
                                    :key="tx.id"
                                    :href="`/admin/payments/transactions/${tx.id}`"
                                    class="flex items-center justify-between rounded-lg border p-3 hover:bg-accent"
                                >
                                    <div class="flex items-center gap-3">
                                        <Receipt
                                            class="h-5 w-5 text-muted-foreground"
                                        />
                                        <div>
                                            <p class="font-medium">
                                                {{ tx.formatted_amount }}
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
                                    <Badge :class="getStatusColor(tx.status)">
                                        {{ tx.status }}
                                    </Badge>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Subscriptions -->
                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between"
                        >
                            <CardTitle>Subscriptions</CardTitle>
                            <Link
                                href="/admin/payments/subscriptions"
                                class="text-sm text-primary hover:underline"
                            >
                                View all
                            </Link>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="customer.subscriptions.length === 0"
                                class="text-sm text-muted-foreground"
                            >
                                No subscriptions
                            </div>
                            <div v-else class="space-y-3">
                                <Link
                                    v-for="sub in customer.subscriptions"
                                    :key="sub.id"
                                    :href="`/admin/payments/subscriptions/${sub.id}`"
                                    class="flex items-center justify-between rounded-lg border p-3 hover:bg-accent"
                                >
                                    <div>
                                        <p class="font-medium">
                                            {{
                                                sub.plan?.name || 'Custom Plan'
                                            }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ sub.billing_description }}
                                        </p>
                                    </div>
                                    <Badge :class="getStatusColor(sub.status)">
                                        {{ sub.status }}
                                    </Badge>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
