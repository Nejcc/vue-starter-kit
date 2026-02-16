<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Package, Plus, Trash2 } from 'lucide-vue-next';

import DataCard from '@/components/DataCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchEmptyState from '@/components/SearchEmptyState.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { useDateFormat } from '@/composables/useDateFormat';
import { useEcommerceNav } from '@ecommerce/composables/useEcommerceNav';
import { useSearch } from '@/composables/useSearch';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem, type PaginatedResponse } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useEcommerceNav();

interface Product {
    id: number;
    name: string;
    slug: string;
    sku: string | null;
    price: number;
    currency: string;
    status: string;
    stock_quantity: number;
    is_active: boolean;
    is_featured: boolean;
    has_variants: boolean;
    created_at: string;
    categories?: Array<{ id: number; name: string }>;
}

interface ProductsPageProps {
    products: PaginatedResponse<Product>;
    categories: Array<{ id: number; name: string }>;
    filters?: {
        search?: string;
        category?: number | null;
    };
    status?: string;
}

const props = defineProps<ProductsPageProps>();
const { formatDate } = useDateFormat();
const { searchQuery, handleSearch, clearSearch } = useSearch({
    url: '/admin/ecommerce/products',
});

if (props.filters?.search) {
    searchQuery.value = props.filters.search;
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Products', href: '#' },
];

function formatPrice(cents: number, currency: string): string {
    const amount = cents / 100;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
    }).format(amount);
}

function statusVariant(
    status: string,
): 'success' | 'warning' | 'default' | 'info' {
    switch (status) {
        case 'active':
            return 'success';
        case 'draft':
            return 'warning';
        case 'archived':
            return 'default';
        default:
            return 'info';
    }
}

function deleteProduct(id: number) {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(`/admin/ecommerce/products/${id}`);
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
        <Head title="Products" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Products"
                        description="Manage your product catalog"
                    />
                    <Link href="/admin/ecommerce/products/create">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Product
                        </Button>
                    </Link>
                </div>

                <SearchInput
                    v-model="searchQuery"
                    placeholder="Search products..."
                    show-clear
                    @search="handleSearch"
                    @clear="clearSearch"
                />

                <template v-if="products.data.length > 0">
                    <div class="grid gap-4">
                        <DataCard
                            v-for="product in products.data"
                            :key="product.id"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                                    >
                                        <Package class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <Link
                                            :href="`/admin/ecommerce/products/${product.id}/edit`"
                                            class="font-medium hover:underline"
                                        >
                                            {{ product.name }}
                                        </Link>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{
                                                formatPrice(
                                                    product.price,
                                                    product.currency,
                                                )
                                            }}
                                            <span v-if="product.sku">
                                                &middot; SKU:
                                                {{ product.sku }}
                                            </span>
                                        </p>
                                        <div
                                            v-if="
                                                product.categories &&
                                                product.categories.length > 0
                                            "
                                            class="mt-1 flex flex-wrap gap-1"
                                        >
                                            <span
                                                v-for="cat in product.categories"
                                                :key="cat.id"
                                                class="inline-flex items-center rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                                            >
                                                {{ cat.name }}
                                            </span>
                                        </div>
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
                                        :variant="statusVariant(product.status)"
                                    />
                                </div>
                            </div>

                            <template #footer>
                                <div class="flex items-center justify-between">
                                    <div
                                        class="flex items-center gap-4 text-sm text-muted-foreground"
                                    >
                                        <span>
                                            Stock:
                                            {{ product.stock_quantity }}
                                        </span>
                                        <span>
                                            Created
                                            {{ formatDate(product.created_at) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/admin/ecommerce/products/${product.id}/edit`"
                                        >
                                            <Button variant="outline" size="sm">
                                                Edit
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="text-destructive hover:text-destructive"
                                            @click="deleteProduct(product.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </template>
                        </DataCard>
                    </div>

                    <Pagination :pagination="products" />
                </template>

                <SearchEmptyState
                    v-else-if="filters?.search"
                    title="No products found"
                    :search-query="filters.search"
                    @clear="clearSearch"
                />

                <EmptyState
                    v-else
                    title="No products yet"
                    description="Create your first product to get started."
                />
            </div>
        </div>
    </ModuleLayout>
</template>
