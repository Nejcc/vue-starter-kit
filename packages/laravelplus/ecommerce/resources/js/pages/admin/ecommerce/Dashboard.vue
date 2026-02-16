<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowRight,
    DollarSign,
    FolderTree,
    Package,
    PackageX,
    ShoppingBag,
    Star,
} from 'lucide-vue-next';

import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useDateFormat } from '@/composables/useDateFormat';
import { useEcommerceNav } from '@ecommerce/composables/useEcommerceNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useEcommerceNav();

interface RecentProduct {
    id: number;
    name: string;
    slug: string;
    price: number;
    currency: string;
    status: string;
    stock_quantity: number;
    is_featured: boolean;
    created_at: string;
    categories: Array<{ id: number; name: string }>;
}

interface Stats {
    totalProducts: number;
    activeProducts: number;
    draftProducts: number;
    featuredProducts: number;
    totalCategories: number;
    activeCategories: number;
    totalVariants: number;
    lowStockProducts: number;
    outOfStockProducts: number;
    currency: string;
    totalOrders: number;
    pendingOrders: number;
    completedOrders: number;
    revenue: number;
}

interface Props {
    stats: Stats;
    recentProducts: RecentProduct[];
}

defineProps<Props>();
const { formatDate } = useDateFormat();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
];

function formatPrice(cents: number, currency: string): string {
    const amount = cents / 100;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
    }).format(amount);
}

function statusVariant(status: string): 'success' | 'warning' | 'default' {
    switch (status) {
        case 'active':
            return 'success';
        case 'draft':
            return 'warning';
        default:
            return 'default';
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
        <Head title="Ecommerce Dashboard" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Ecommerce Dashboard"
                        description="Overview of your store"
                    />
                    <Link href="/admin/ecommerce/products/create">
                        <Button>
                            <Package class="mr-2 h-4 w-4" />
                            Add Product
                        </Button>
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium">
                                Total Products
                            </CardTitle>
                            <Package class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ stats.totalProducts }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ stats.activeProducts }} active,
                                {{ stats.draftProducts }} draft
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium">
                                Categories
                            </CardTitle>
                            <FolderTree
                                class="h-4 w-4 text-muted-foreground"
                            />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ stats.totalCategories }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ stats.activeCategories }} active
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium">
                                Featured
                            </CardTitle>
                            <Star class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ stats.featuredProducts }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ stats.totalVariants }} total variants
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium">
                                Stock Alerts
                            </CardTitle>
                            <AlertTriangle
                                class="h-4 w-4 text-muted-foreground"
                            />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ stats.lowStockProducts }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ stats.outOfStockProducts }} out of stock
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium">
                                Orders
                            </CardTitle>
                            <ShoppingBag
                                class="h-4 w-4 text-muted-foreground"
                            />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ stats.totalOrders }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ stats.pendingOrders }} pending,
                                {{ stats.completedOrders }} completed
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium">
                                Revenue
                            </CardTitle>
                            <DollarSign
                                class="h-4 w-4 text-muted-foreground"
                            />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{
                                    formatPrice(
                                        stats.revenue,
                                        stats.currency,
                                    )
                                }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                From completed orders
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Recent Products -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>Recent Products</CardTitle>
                            <Link
                                href="/admin/ecommerce/products"
                                class="inline-flex items-center text-sm font-medium text-primary hover:underline"
                            >
                                View all
                                <ArrowRight class="ml-1 h-4 w-4" />
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="recentProducts.length > 0"
                            class="space-y-4"
                        >
                            <div
                                v-for="product in recentProducts"
                                :key="product.id"
                                class="flex items-center justify-between"
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
                                            :href="`/admin/ecommerce/products/${product.id}/edit`"
                                            class="text-sm font-medium hover:underline"
                                        >
                                            {{ product.name }}
                                        </Link>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                formatPrice(
                                                    product.price,
                                                    product.currency,
                                                )
                                            }}
                                            &middot;
                                            {{ formatDate(product.created_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <StatusBadge
                                        v-if="product.is_featured"
                                        label="Featured"
                                        variant="purple"
                                    />
                                    <StatusBadge
                                        :label="product.status"
                                        :variant="
                                            statusVariant(product.status)
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex flex-col items-center py-8 text-center"
                        >
                            <PackageX
                                class="mb-3 h-10 w-10 text-muted-foreground/50"
                            />
                            <p class="text-sm text-muted-foreground">
                                No products yet. Create your first product to
                                get started.
                            </p>
                            <Link
                                href="/admin/ecommerce/products/create"
                                class="mt-3"
                            >
                                <Button variant="outline" size="sm">
                                    Add Product
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </ModuleLayout>
</template>
