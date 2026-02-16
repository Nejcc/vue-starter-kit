<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    MapPin,
    Package,
    ShoppingBag,
    User,
} from 'lucide-vue-next';

import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useDateFormat } from '@/composables/useDateFormat';
import { useEcommerceNav } from '@ecommerce/composables/useEcommerceNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

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

interface OrderItemProduct {
    id: number;
    name: string;
    slug: string;
}

interface OrderItem {
    id: number;
    product_id: number | null;
    product_variant_id: number | null;
    name: string;
    sku: string | null;
    quantity: number;
    unit_price: number;
    total: number;
    options: Record<string, unknown> | null;
    product: OrderItemProduct | null;
    product_variant: object | null;
}

interface Address {
    name?: string;
    address?: string;
    city?: string;
    state?: string;
    zip?: string;
    country?: string;
}

interface Order {
    id: number;
    uuid: string;
    order_number: string;
    status: string;
    subtotal: number;
    tax: number;
    discount: number;
    shipping_cost: number;
    total: number;
    currency: string;
    shipping_address: Address | null;
    billing_address: Address | null;
    notes: string | null;
    user: OrderUser | null;
    items: OrderItem[];
    placed_at: string | null;
    completed_at: string | null;
    cancelled_at: string | null;
    created_at: string;
}

interface StatusTransition {
    value: string;
    label: string;
    color: string;
}

interface Props {
    order: Order;
    availableTransitions: StatusTransition[];
}

const props = defineProps<Props>();
const { formatDate } = useDateFormat();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Orders', href: '/admin/ecommerce/orders' },
    { title: props.order.order_number, href: '#' },
];

function formatPrice(cents: number): string {
    const amount = cents / 100;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.order.currency,
    }).format(amount);
}

function formatAddress(address: Address | null): string {
    if (!address) {
        return 'N/A';
    }
    const parts = [
        address.name,
        address.address,
        [address.city, address.state, address.zip]
            .filter(Boolean)
            .join(', '),
        address.country,
    ].filter(Boolean);
    return parts.join('\n');
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

function updateStatus(newStatus: string) {
    router.patch(`/admin/ecommerce/orders/${props.order.uuid}/status`, {
        status: newStatus,
    });
}
</script>

<template>
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head :title="`Order ${order.order_number}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link href="/admin/ecommerce/orders">
                            <Button variant="outline" size="icon">
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                        </Link>
                        <Heading
                            variant="small"
                            :title="`Order ${order.order_number}`"
                            :description="`Placed ${order.placed_at ? formatDate(order.placed_at) : formatDate(order.created_at)}`"
                        />
                    </div>
                    <StatusBadge
                        :label="order.status"
                        :variant="statusVariant(order.status)"
                    />
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="space-y-6 lg:col-span-2">
                        <!-- Order Items -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <ShoppingBag class="h-5 w-5" />
                                    Order Items
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div
                                        v-for="item in order.items"
                                        :key="item.id"
                                        class="flex items-center justify-between border-b pb-4 last:border-0 last:pb-0"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10"
                                            >
                                                <Package
                                                    class="h-4 w-4 text-primary"
                                                />
                                            </div>
                                            <div>
                                                <Link
                                                    v-if="item.product"
                                                    :href="`/admin/ecommerce/products/${item.product.id}/edit`"
                                                    class="text-sm font-medium hover:underline"
                                                >
                                                    {{ item.name }}
                                                </Link>
                                                <p
                                                    v-else
                                                    class="text-sm font-medium"
                                                >
                                                    {{ item.name }}
                                                </p>
                                                <p
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    <span v-if="item.sku">
                                                        SKU: {{ item.sku }}
                                                        &middot;
                                                    </span>
                                                    Qty: {{ item.quantity }}
                                                    &middot;
                                                    {{
                                                        formatPrice(
                                                            item.unit_price,
                                                        )
                                                    }}
                                                    each
                                                </p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-medium">
                                            {{ formatPrice(item.total) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Totals -->
                                <div
                                    class="mt-6 space-y-2 border-t pt-4 text-sm"
                                >
                                    <div
                                        class="flex justify-between text-muted-foreground"
                                    >
                                        <span>Subtotal</span>
                                        <span>{{
                                            formatPrice(order.subtotal)
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="order.tax > 0"
                                        class="flex justify-between text-muted-foreground"
                                    >
                                        <span>Tax</span>
                                        <span>{{
                                            formatPrice(order.tax)
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="order.discount > 0"
                                        class="flex justify-between text-muted-foreground"
                                    >
                                        <span>Discount</span>
                                        <span>-{{
                                            formatPrice(order.discount)
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="order.shipping_cost > 0"
                                        class="flex justify-between text-muted-foreground"
                                    >
                                        <span>Shipping</span>
                                        <span>{{
                                            formatPrice(order.shipping_cost)
                                        }}</span>
                                    </div>
                                    <div
                                        class="flex justify-between border-t pt-2 font-medium"
                                    >
                                        <span>Total</span>
                                        <span>{{
                                            formatPrice(order.total)
                                        }}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Addresses -->
                        <div class="grid gap-6 sm:grid-cols-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle
                                        class="flex items-center gap-2 text-base"
                                    >
                                        <MapPin class="h-4 w-4" />
                                        Shipping Address
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p
                                        class="whitespace-pre-line text-sm text-muted-foreground"
                                    >
                                        {{
                                            formatAddress(
                                                order.shipping_address,
                                            )
                                        }}
                                    </p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle
                                        class="flex items-center gap-2 text-base"
                                    >
                                        <MapPin class="h-4 w-4" />
                                        Billing Address
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p
                                        class="whitespace-pre-line text-sm text-muted-foreground"
                                    >
                                        {{
                                            formatAddress(
                                                order.billing_address,
                                            )
                                        }}
                                    </p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Customer -->
                        <Card>
                            <CardHeader>
                                <CardTitle
                                    class="flex items-center gap-2 text-base"
                                >
                                    <User class="h-4 w-4" />
                                    Customer
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div v-if="order.user">
                                    <p class="text-sm font-medium">
                                        {{ order.user.name }}
                                    </p>
                                    <p
                                        class="text-sm text-muted-foreground"
                                    >
                                        {{ order.user.email }}
                                    </p>
                                </div>
                                <p
                                    v-else
                                    class="text-sm text-muted-foreground"
                                >
                                    Guest order
                                </p>
                            </CardContent>
                        </Card>

                        <!-- Status Update -->
                        <Card
                            v-if="availableTransitions.length > 0"
                        >
                            <CardHeader>
                                <CardTitle class="text-base">
                                    Update Status
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Select @update:model-value="updateStatus">
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Select new status"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="transition in availableTransitions"
                                            :key="transition.value"
                                            :value="transition.value"
                                        >
                                            {{ transition.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </CardContent>
                        </Card>

                        <!-- Notes -->
                        <Card v-if="order.notes">
                            <CardHeader>
                                <CardTitle class="text-base">
                                    Notes
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p
                                    class="text-sm text-muted-foreground"
                                >
                                    {{ order.notes }}
                                </p>
                            </CardContent>
                        </Card>

                        <!-- Timestamps -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base">
                                    Timeline
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div
                                    class="space-y-2 text-sm text-muted-foreground"
                                >
                                    <div class="flex justify-between">
                                        <span>Created</span>
                                        <span>{{
                                            formatDate(order.created_at)
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="order.placed_at"
                                        class="flex justify-between"
                                    >
                                        <span>Placed</span>
                                        <span>{{
                                            formatDate(order.placed_at)
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="order.completed_at"
                                        class="flex justify-between"
                                    >
                                        <span>Completed</span>
                                        <span>{{
                                            formatDate(order.completed_at)
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="order.cancelled_at"
                                        class="flex justify-between"
                                    >
                                        <span>Cancelled</span>
                                        <span>{{
                                            formatDate(order.cancelled_at)
                                        }}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
