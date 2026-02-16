<script setup lang="ts">
import { Form, Head, Link, router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useEcommerceNav } from '@ecommerce/composables/useEcommerceNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useEcommerceNav();

interface Variant {
    id: number;
    name: string;
    sku: string | null;
    price: number | null;
    compare_at_price: number | null;
    stock_quantity: number;
    options: Record<string, string>;
    is_active: boolean;
    sort_order: number;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    sku: string | null;
    description: string | null;
    short_description: string | null;
    price: number;
    compare_at_price: number | null;
    cost_price: number | null;
    currency: string;
    status: string;
    stock_quantity: number;
    low_stock_threshold: number;
    is_active: boolean;
    is_featured: boolean;
    is_digital: boolean;
    has_variants: boolean;
    weight: string | null;
    categories?: Array<{ id: number; name: string }>;
    variants?: Variant[];
}

interface EditProductPageProps {
    product: Product;
    categories: Array<{ id: number; name: string }>;
}

const props = defineProps<EditProductPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Products', href: '/admin/ecommerce/products' },
    { title: props.product.name, href: '#' },
];

const productCategoryIds = (props.product.categories ?? []).map((c) =>
    String(c.id),
);

// Variant form
const showVariantForm = ref(false);
const variantForm = useForm({
    name: '',
    sku: '',
    price: '',
    stock_quantity: '0',
    options: '{}',
});

function addVariant() {
    variantForm.post(`/admin/ecommerce/products/${props.product.id}/variants`, {
        onSuccess: () => {
            variantForm.reset();
            showVariantForm.value = false;
        },
    });
}

function deleteVariant(variantId: number) {
    if (confirm('Are you sure you want to delete this variant?')) {
        router.delete(
            `/admin/ecommerce/products/${props.product.id}/variants/${variantId}`,
        );
    }
}

function formatPrice(cents: number | null, currency: string): string {
    if (cents === null) return '-';
    const amount = cents / 100;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
    }).format(amount);
}
</script>

<template>
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head :title="`Edit ${product.name}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-8">
                <Heading
                    variant="small"
                    :title="`Edit ${product.name}`"
                    description="Update product details, variants, and categories"
                />

                <!-- Product Details Form -->
                <div class="rounded-lg border p-6">
                    <h3 class="mb-4 text-lg font-medium">Product Details</h3>
                    <Form
                        :action="`/admin/ecommerce/products/${product.id}`"
                        method="put"
                        class="space-y-4"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <div class="grid gap-4 md:grid-cols-2">
                            <FormField
                                label="Name"
                                id="name"
                                :error="errors.name"
                                required
                            >
                                <Input
                                    id="name"
                                    name="name"
                                    type="text"
                                    :value="product.name"
                                    required
                                />
                            </FormField>

                            <FormField
                                label="Slug"
                                id="slug"
                                :error="errors.slug"
                            >
                                <Input
                                    id="slug"
                                    name="slug"
                                    type="text"
                                    :value="product.slug"
                                />
                            </FormField>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <FormField label="SKU" id="sku" :error="errors.sku">
                                <Input
                                    id="sku"
                                    name="sku"
                                    type="text"
                                    :value="product.sku ?? ''"
                                />
                            </FormField>

                            <FormField
                                label="Status"
                                id="status"
                                :error="errors.status"
                            >
                                <select
                                    id="status"
                                    name="status"
                                    :value="product.status"
                                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </FormField>
                        </div>

                        <FormField
                            label="Short Description"
                            id="short_description"
                            :error="errors.short_description"
                        >
                            <Input
                                id="short_description"
                                name="short_description"
                                type="text"
                                :value="product.short_description ?? ''"
                            />
                        </FormField>

                        <FormField
                            label="Description"
                            id="description"
                            :error="errors.description"
                        >
                            <Textarea
                                id="description"
                                name="description"
                                :value="product.description ?? ''"
                                rows="4"
                            />
                        </FormField>

                        <!-- Pricing -->
                        <div class="grid gap-4 md:grid-cols-3">
                            <FormField
                                label="Price (cents)"
                                id="price"
                                :error="errors.price"
                                required
                            >
                                <Input
                                    id="price"
                                    name="price"
                                    type="number"
                                    min="0"
                                    :value="String(product.price)"
                                    required
                                />
                            </FormField>

                            <FormField
                                label="Compare at Price (cents)"
                                id="compare_at_price"
                                :error="errors.compare_at_price"
                            >
                                <Input
                                    id="compare_at_price"
                                    name="compare_at_price"
                                    type="number"
                                    min="0"
                                    :value="
                                        product.compare_at_price
                                            ? String(product.compare_at_price)
                                            : ''
                                    "
                                />
                            </FormField>

                            <FormField
                                label="Cost Price (cents)"
                                id="cost_price"
                                :error="errors.cost_price"
                            >
                                <Input
                                    id="cost_price"
                                    name="cost_price"
                                    type="number"
                                    min="0"
                                    :value="
                                        product.cost_price
                                            ? String(product.cost_price)
                                            : ''
                                    "
                                />
                            </FormField>
                        </div>

                        <!-- Inventory -->
                        <div class="grid gap-4 md:grid-cols-2">
                            <FormField
                                label="Stock Quantity"
                                id="stock_quantity"
                                :error="errors.stock_quantity"
                            >
                                <Input
                                    id="stock_quantity"
                                    name="stock_quantity"
                                    type="number"
                                    min="0"
                                    :value="String(product.stock_quantity)"
                                />
                            </FormField>

                            <FormField
                                label="Low Stock Threshold"
                                id="low_stock_threshold"
                                :error="errors.low_stock_threshold"
                            >
                                <Input
                                    id="low_stock_threshold"
                                    name="low_stock_threshold"
                                    type="number"
                                    min="0"
                                    :value="String(product.low_stock_threshold)"
                                />
                            </FormField>
                        </div>

                        <!-- Options -->
                        <div class="flex flex-wrap gap-6">
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="is_active"
                                    name="is_active"
                                    :default-checked="product.is_active"
                                    value="1"
                                />
                                <Label for="is_active">Active</Label>
                            </div>
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="is_featured"
                                    name="is_featured"
                                    :default-checked="product.is_featured"
                                    value="1"
                                />
                                <Label for="is_featured">Featured</Label>
                            </div>
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="is_digital"
                                    name="is_digital"
                                    :default-checked="product.is_digital"
                                    value="1"
                                />
                                <Label for="is_digital">Digital Product</Label>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div>
                            <Label class="mb-2 block">Categories</Label>
                            <div
                                v-if="categories.length > 0"
                                class="flex flex-wrap gap-4"
                            >
                                <div
                                    v-for="category in categories"
                                    :key="category.id"
                                    class="flex items-center gap-2"
                                >
                                    <Checkbox
                                        :id="`category_${category.id}`"
                                        name="categories[]"
                                        :value="String(category.id)"
                                        :default-checked="
                                            productCategoryIds.includes(
                                                String(category.id),
                                            )
                                        "
                                    />
                                    <Label :for="`category_${category.id}`">
                                        {{ category.name }}
                                    </Label>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">
                                No categories available.
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button :disabled="processing" type="submit">
                                Update Product
                            </Button>
                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-show="recentlySuccessful"
                                    class="text-sm text-neutral-600 dark:text-neutral-400"
                                >
                                    Updated.
                                </p>
                            </Transition>
                        </div>
                    </Form>
                </div>

                <!-- Variants Section -->
                <div class="rounded-lg border p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium">Product Variants</h3>
                        <Button
                            size="sm"
                            @click="showVariantForm = !showVariantForm"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            Add Variant
                        </Button>
                    </div>

                    <!-- Add Variant Form -->
                    <div
                        v-if="showVariantForm"
                        class="mb-4 rounded-lg border bg-muted/50 p-4"
                    >
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="variant_name">Name</Label>
                                <Input
                                    id="variant_name"
                                    v-model="variantForm.name"
                                    type="text"
                                    placeholder="e.g. Large / Red"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="variant_sku">SKU</Label>
                                <Input
                                    id="variant_sku"
                                    v-model="variantForm.sku"
                                    type="text"
                                    placeholder="SKU-001-LG"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="variant_price">
                                    Price (cents, leave empty for product price)
                                </Label>
                                <Input
                                    id="variant_price"
                                    v-model="variantForm.price"
                                    type="number"
                                    min="0"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="variant_stock">
                                    Stock Quantity
                                </Label>
                                <Input
                                    id="variant_stock"
                                    v-model="variantForm.stock_quantity"
                                    type="number"
                                    min="0"
                                />
                            </div>
                        </div>
                        <div class="mt-4 grid gap-2">
                            <Label for="variant_options">
                                Options (JSON)
                            </Label>
                            <Textarea
                                id="variant_options"
                                v-model="variantForm.options"
                                rows="2"
                                placeholder='{"size": "Large", "color": "Red"}'
                            />
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <Button
                                size="sm"
                                :disabled="variantForm.processing"
                                @click="addVariant"
                            >
                                Save Variant
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="showVariantForm = false"
                            >
                                Cancel
                            </Button>
                        </div>
                    </div>

                    <!-- Variants List -->
                    <div
                        v-if="product.variants && product.variants.length > 0"
                        class="space-y-3"
                    >
                        <div
                            v-for="variant in product.variants"
                            :key="variant.id"
                            class="flex items-center justify-between rounded-lg border p-3"
                        >
                            <div>
                                <p class="font-medium">{{ variant.name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{
                                        formatPrice(
                                            variant.price,
                                            product.currency,
                                        )
                                    }}
                                    <span v-if="variant.sku">
                                        &middot; SKU: {{ variant.sku }}
                                    </span>
                                    &middot; Stock:
                                    {{ variant.stock_quantity }}
                                </p>
                                <div
                                    v-if="
                                        variant.options &&
                                        Object.keys(variant.options).length > 0
                                    "
                                    class="mt-1 flex flex-wrap gap-1"
                                >
                                    <span
                                        v-for="(value, key) in variant.options"
                                        :key="String(key)"
                                        class="inline-flex items-center rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                                    >
                                        {{ key }}: {{ value }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <StatusBadge
                                    :label="
                                        variant.is_active
                                            ? 'Active'
                                            : 'Inactive'
                                    "
                                    :variant="
                                        variant.is_active
                                            ? 'success'
                                            : 'default'
                                    "
                                />
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive hover:text-destructive"
                                    @click="deleteVariant(variant.id)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">
                        No variants yet. Add variants for different sizes,
                        colors, etc.
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/ecommerce/products"
                        class="text-sm text-muted-foreground hover:underline"
                    >
                        Back to Products
                    </Link>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
