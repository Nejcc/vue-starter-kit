<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, RefreshCw } from 'lucide-vue-next';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Refund {
    id: number;
    provider_id: string | null;
    amount: number;
    formatted_amount: string;
    status: string;
    reason: string | null;
    created_at: string;
}

interface Transaction {
    id: number;
    uuid: string;
    provider_id: string | null;
    amount: number;
    amount_refunded: number;
    refundable_amount: number;
    formatted_amount: string;
    currency: string;
    status: string;
    driver: string;
    type: string | null;
    description: string | null;
    failure_code: string | null;
    failure_message: string | null;
    receipt_url: string | null;
    metadata: Record<string, any> | null;
    provider_response: Record<string, any> | null;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    customer: {
        id: number;
        email: string;
        name: string | null;
    } | null;
    refunds: Refund[];
    can_refund: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    transaction: Transaction;
}

const props = defineProps<Props>();

const showRefundForm = ref(false);

const refundForm = useForm({
    amount: props.transaction.refundable_amount,
    reason: '',
});

const submitRefund = (): void => {
    refundForm.post(`/admin/payments/transactions/${props.transaction.id}/refund`, {
        onSuccess: () => {
            showRefundForm.value = false;
            refundForm.reset();
        },
    });
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

const formatMoney = (cents: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.transaction.currency,
    }).format(cents / 100);
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Payments', href: '/admin/payments' },
    { title: 'Transactions', href: '/admin/payments/transactions' },
    { title: `#${props.transaction.id}`, href: '#' },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Transaction #${transaction.id}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/payments/transactions"
                        class="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back
                    </Link>
                </div>

                <div class="flex items-center justify-between">
                    <Heading
                        :title="`Transaction ${transaction.formatted_amount}`"
                        :description="`ID: ${transaction.uuid}`"
                        variant="small"
                    />
                    <div class="flex items-center gap-2">
                        <Badge :class="getStatusColor(transaction.status)">
                            {{ transaction.status }}
                        </Badge>
                        <Badge variant="outline">{{ transaction.driver }}</Badge>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Transaction Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Transaction Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Amount
                                    </p>
                                    <p class="font-medium">
                                        {{ transaction.formatted_amount }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Currency
                                    </p>
                                    <p class="font-medium">
                                        {{ transaction.currency }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Status
                                    </p>
                                    <Badge :class="getStatusColor(transaction.status)">
                                        {{ transaction.status }}
                                    </Badge>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Driver
                                    </p>
                                    <p class="font-medium">
                                        {{ transaction.driver }}
                                    </p>
                                </div>
                                <div v-if="transaction.type">
                                    <p class="text-sm text-muted-foreground">
                                        Type
                                    </p>
                                    <p class="font-medium">
                                        {{ transaction.type }}
                                    </p>
                                </div>
                                <div v-if="transaction.provider_id">
                                    <p class="text-sm text-muted-foreground">
                                        Provider ID
                                    </p>
                                    <p class="font-mono text-sm">
                                        {{ transaction.provider_id }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="transaction.description">
                                <p class="text-sm text-muted-foreground">
                                    Description
                                </p>
                                <p class="font-medium">
                                    {{ transaction.description }}
                                </p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Created
                                    </p>
                                    <p class="text-sm">
                                        {{
                                            new Date(
                                                transaction.created_at,
                                            ).toLocaleString()
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Updated
                                    </p>
                                    <p class="text-sm">
                                        {{
                                            new Date(
                                                transaction.updated_at,
                                            ).toLocaleString()
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="transaction.receipt_url">
                                <a
                                    :href="transaction.receipt_url"
                                    target="_blank"
                                    class="text-sm text-primary hover:underline"
                                >
                                    View Receipt
                                </a>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Customer/User Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Customer</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="transaction.user">
                                <p class="text-sm text-muted-foreground">User</p>
                                <p class="font-medium">
                                    {{ transaction.user.name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ transaction.user.email }}
                                </p>
                            </div>
                            <div v-if="transaction.customer">
                                <p class="text-sm text-muted-foreground">
                                    Payment Customer
                                </p>
                                <p class="font-medium">
                                    {{ transaction.customer.name || transaction.customer.email }}
                                </p>
                                <Link
                                    :href="`/admin/payments/customers/${transaction.customer.id}`"
                                    class="text-sm text-primary hover:underline"
                                >
                                    View Customer
                                </Link>
                            </div>
                            <div
                                v-if="!transaction.user && !transaction.customer"
                                class="text-sm text-muted-foreground"
                            >
                                No customer associated
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Failure Details -->
                    <Card
                        v-if="transaction.failure_code || transaction.failure_message"
                    >
                        <CardHeader>
                            <CardTitle class="text-red-600">
                                Failure Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="transaction.failure_code">
                                <p class="text-sm text-muted-foreground">Code</p>
                                <p class="font-mono text-sm">
                                    {{ transaction.failure_code }}
                                </p>
                            </div>
                            <div v-if="transaction.failure_message">
                                <p class="text-sm text-muted-foreground">
                                    Message
                                </p>
                                <p class="text-sm">
                                    {{ transaction.failure_message }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Refunds -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Refunds</CardTitle>
                            <Button
                                v-if="transaction.can_refund && !showRefundForm"
                                variant="outline"
                                size="sm"
                                @click="showRefundForm = true"
                            >
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Issue Refund
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <!-- Refund Form -->
                            <form
                                v-if="showRefundForm"
                                class="space-y-4 border-b pb-4 mb-4"
                                @submit.prevent="submitRefund"
                            >
                                <div class="space-y-2">
                                    <Label for="amount">
                                        Amount (cents, max:
                                        {{ formatMoney(transaction.refundable_amount) }})
                                    </Label>
                                    <Input
                                        id="amount"
                                        v-model.number="refundForm.amount"
                                        type="number"
                                        :max="transaction.refundable_amount"
                                        min="1"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label for="reason">Reason (optional)</Label>
                                    <Textarea
                                        id="reason"
                                        v-model="refundForm.reason"
                                        rows="2"
                                    />
                                </div>
                                <div class="flex gap-2">
                                    <Button
                                        type="submit"
                                        :disabled="refundForm.processing"
                                    >
                                        Process Refund
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="showRefundForm = false"
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </form>

                            <!-- Refund List -->
                            <div v-if="transaction.refunds.length === 0" class="text-sm text-muted-foreground">
                                No refunds issued
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="refund in transaction.refunds"
                                    :key="refund.id"
                                    class="flex items-center justify-between rounded-lg border p-3"
                                >
                                    <div>
                                        <p class="font-medium">
                                            {{ refund.formatted_amount }}
                                        </p>
                                        <p
                                            v-if="refund.reason"
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ refund.reason }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{
                                                new Date(
                                                    refund.created_at,
                                                ).toLocaleString()
                                            }}
                                        </p>
                                    </div>
                                    <Badge :class="getStatusColor(refund.status)">
                                        {{ refund.status }}
                                    </Badge>
                                </div>
                            </div>

                            <!-- Refund Summary -->
                            <div
                                v-if="transaction.amount_refunded > 0"
                                class="mt-4 pt-4 border-t"
                            >
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">
                                        Total Refunded
                                    </span>
                                    <span class="font-medium">
                                        {{ formatMoney(transaction.amount_refunded) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">
                                        Remaining
                                    </span>
                                    <span class="font-medium">
                                        {{ formatMoney(transaction.refundable_amount) }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Metadata -->
                    <Card v-if="transaction.metadata">
                        <CardHeader>
                            <CardTitle>Metadata</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <pre class="text-xs bg-muted p-3 rounded overflow-auto">{{ JSON.stringify(transaction.metadata, null, 2) }}</pre>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
