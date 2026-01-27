<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Download, RefreshCw } from 'lucide-vue-next';

import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface LineItem {
    description: string;
    quantity: number;
    unit_price: number;
    formatted_unit_price: string;
    amount: number;
    formatted_amount: string;
}

interface Invoice {
    id: number;
    uuid: string;
    number: string;
    status: string;
    driver: string | null;
    provider_id: string | null;
    subtotal: number;
    formatted_subtotal: string;
    tax: number;
    formatted_tax: string;
    tax_rate: number | null;
    discount: number;
    formatted_discount: string;
    total: number;
    formatted_total: string;
    amount_paid: number;
    amount_due: number;
    formatted_amount_due: string;
    currency: string;
    billing_name: string | null;
    billing_email: string | null;
    billing_address: string | null;
    billing_city: string | null;
    billing_state: string | null;
    billing_postal_code: string | null;
    billing_country: string | null;
    billing_company: string | null;
    billing_address_full: string | null;
    tax_id: string | null;
    line_items: LineItem[];
    notes: string | null;
    footer: string | null;
    invoice_date: string;
    due_date: string | null;
    paid_at: string | null;
    voided_at: string | null;
    is_paid: boolean;
    is_overdue: boolean;
    is_voided: boolean;
    has_pdf: boolean;
    pdf_url: string | null;
    pdf_generated_at: string | null;
    metadata: Record<string, any> | null;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    transaction: {
        id: number;
        uuid: string;
        status: string;
    } | null;
    subscription: {
        id: number;
        uuid: string;
        status: string;
    } | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    invoice: Invoice;
}

const props = defineProps<Props>();

const regeneratePdf = (): void => {
    router.post(`/admin/payments/invoices/${props.invoice.id}/regenerate-pdf`);
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

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Payments', href: '/admin/payments' },
    { title: 'Invoices', href: '/admin/payments/invoices' },
    { title: props.invoice.number, href: '#' },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Invoice ${invoice.number}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/payments/invoices"
                        class="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back
                    </Link>
                </div>

                <div class="flex items-center justify-between">
                    <Heading
                        :title="`Invoice ${invoice.number}`"
                        :description="invoice.formatted_total"
                        variant="small"
                    />
                    <div class="flex items-center gap-2">
                        <Badge
                            :class="
                                getStatusColor(invoice.status, invoice.is_overdue)
                            "
                        >
                            {{
                                invoice.is_overdue ? 'Overdue' : invoice.status
                            }}
                        </Badge>
                        <a
                            v-if="invoice.has_pdf"
                            :href="`/admin/payments/invoices/${invoice.id}/download`"
                            class="inline-flex items-center gap-2 rounded-md border bg-background px-3 py-2 text-sm font-medium hover:bg-accent"
                        >
                            <Download class="h-4 w-4" />
                            Download PDF
                        </a>
                        <Button variant="outline" size="sm" @click="regeneratePdf">
                            <RefreshCw class="mr-2 h-4 w-4" />
                            Regenerate PDF
                        </Button>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Invoice Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Invoice Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Invoice Number
                                    </p>
                                    <p class="font-medium">{{ invoice.number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Status
                                    </p>
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
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Invoice Date
                                    </p>
                                    <p class="text-sm">
                                        {{
                                            new Date(
                                                invoice.invoice_date,
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                                <div v-if="invoice.due_date">
                                    <p class="text-sm text-muted-foreground">
                                        Due Date
                                    </p>
                                    <p class="text-sm">
                                        {{
                                            new Date(
                                                invoice.due_date,
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                                <div v-if="invoice.paid_at">
                                    <p class="text-sm text-muted-foreground">
                                        Paid At
                                    </p>
                                    <p class="text-sm">
                                        {{
                                            new Date(
                                                invoice.paid_at,
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                                <div v-if="invoice.driver">
                                    <p class="text-sm text-muted-foreground">
                                        Driver
                                    </p>
                                    <p class="font-medium">{{ invoice.driver }}</p>
                                </div>
                            </div>
                            <div v-if="invoice.provider_id">
                                <p class="text-sm text-muted-foreground">
                                    Provider ID
                                </p>
                                <p class="font-mono text-sm">
                                    {{ invoice.provider_id }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Billing Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Billing Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="invoice.billing_name">
                                <p class="text-sm text-muted-foreground">Name</p>
                                <p class="font-medium">{{ invoice.billing_name }}</p>
                            </div>
                            <div v-if="invoice.billing_company">
                                <p class="text-sm text-muted-foreground">
                                    Company
                                </p>
                                <p class="font-medium">
                                    {{ invoice.billing_company }}
                                </p>
                            </div>
                            <div v-if="invoice.billing_email">
                                <p class="text-sm text-muted-foreground">Email</p>
                                <p class="font-medium">
                                    {{ invoice.billing_email }}
                                </p>
                            </div>
                            <div v-if="invoice.billing_address_full">
                                <p class="text-sm text-muted-foreground">
                                    Address
                                </p>
                                <p class="text-sm whitespace-pre-line">
                                    {{ invoice.billing_address_full }}
                                </p>
                            </div>
                            <div v-if="invoice.tax_id">
                                <p class="text-sm text-muted-foreground">
                                    Tax ID
                                </p>
                                <p class="font-medium">{{ invoice.tax_id }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Line Items -->
                    <Card class="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Line Items</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b text-left">
                                            <th class="pb-3 font-medium">
                                                Description
                                            </th>
                                            <th class="pb-3 font-medium text-right">
                                                Qty
                                            </th>
                                            <th class="pb-3 font-medium text-right">
                                                Unit Price
                                            </th>
                                            <th class="pb-3 font-medium text-right">
                                                Amount
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(item, idx) in invoice.line_items"
                                            :key="idx"
                                            class="border-b"
                                        >
                                            <td class="py-3">
                                                {{ item.description }}
                                            </td>
                                            <td class="py-3 text-right">
                                                {{ item.quantity }}
                                            </td>
                                            <td class="py-3 text-right">
                                                {{ item.formatted_unit_price }}
                                            </td>
                                            <td class="py-3 text-right">
                                                {{ item.formatted_amount }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="pt-4 text-right font-medium">
                                                Subtotal
                                            </td>
                                            <td class="pt-4 text-right">
                                                {{ invoice.formatted_subtotal }}
                                            </td>
                                        </tr>
                                        <tr v-if="invoice.tax > 0">
                                            <td colspan="3" class="pt-2 text-right text-muted-foreground">
                                                Tax
                                                <span v-if="invoice.tax_rate">
                                                    ({{ invoice.tax_rate }}%)
                                                </span>
                                            </td>
                                            <td class="pt-2 text-right">
                                                {{ invoice.formatted_tax }}
                                            </td>
                                        </tr>
                                        <tr v-if="invoice.discount > 0">
                                            <td colspan="3" class="pt-2 text-right text-muted-foreground">
                                                Discount
                                            </td>
                                            <td class="pt-2 text-right text-green-600">
                                                -{{ invoice.formatted_discount }}
                                            </td>
                                        </tr>
                                        <tr class="text-lg font-bold">
                                            <td colspan="3" class="pt-4 text-right">
                                                Total
                                            </td>
                                            <td class="pt-4 text-right">
                                                {{ invoice.formatted_total }}
                                            </td>
                                        </tr>
                                        <tr v-if="invoice.amount_due > 0">
                                            <td colspan="3" class="pt-2 text-right text-muted-foreground">
                                                Amount Due
                                            </td>
                                            <td class="pt-2 text-right font-medium text-red-600">
                                                {{ invoice.formatted_amount_due }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Related -->
                    <Card v-if="invoice.user || invoice.transaction || invoice.subscription">
                        <CardHeader>
                            <CardTitle>Related</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="invoice.user">
                                <p class="text-sm text-muted-foreground">User</p>
                                <p class="font-medium">{{ invoice.user.name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ invoice.user.email }}
                                </p>
                            </div>
                            <div v-if="invoice.transaction">
                                <p class="text-sm text-muted-foreground">
                                    Transaction
                                </p>
                                <Link
                                    :href="`/admin/payments/transactions/${invoice.transaction.id}`"
                                    class="text-sm text-primary hover:underline"
                                >
                                    {{ invoice.transaction.uuid }}
                                </Link>
                            </div>
                            <div v-if="invoice.subscription">
                                <p class="text-sm text-muted-foreground">
                                    Subscription
                                </p>
                                <Link
                                    :href="`/admin/payments/subscriptions/${invoice.subscription.id}`"
                                    class="text-sm text-primary hover:underline"
                                >
                                    {{ invoice.subscription.uuid }}
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Notes -->
                    <Card v-if="invoice.notes || invoice.footer">
                        <CardHeader>
                            <CardTitle>Notes</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="invoice.notes">
                                <p class="text-sm text-muted-foreground">Notes</p>
                                <p class="text-sm whitespace-pre-line">
                                    {{ invoice.notes }}
                                </p>
                            </div>
                            <div v-if="invoice.footer">
                                <p class="text-sm text-muted-foreground">Footer</p>
                                <p class="text-sm">{{ invoice.footer }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Metadata -->
                    <Card v-if="invoice.metadata">
                        <CardHeader>
                            <CardTitle>Metadata</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <pre class="text-xs bg-muted p-3 rounded overflow-auto">{{ JSON.stringify(invoice.metadata, null, 2) }}</pre>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
