<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';

import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
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

interface CreateProductPageProps {
    categories: Array<{ id: number; name: string }>;
}

defineProps<CreateProductPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Products', href: '/admin/ecommerce/products' },
    { title: 'Create', href: '#' },
];
</script>

<template>
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head title="Create Product" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Create Product"
                    description="Add a new product to your catalog"
                />

                <Form
                    action="/admin/ecommerce/products"
                    method="post"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <!-- Basic Information -->
                    <div class="rounded-lg border p-6">
                        <h3 class="mb-4 text-lg font-medium">
                            Basic Information
                        </h3>
                        <div class="space-y-4">
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
                                    required
                                    placeholder="Product name"
                                />
                            </FormField>

                            <FormField
                                label="Slug"
                                id="slug"
                                :error="errors.slug"
                                description="Leave blank to auto-generate from name."
                            >
                                <Input
                                    id="slug"
                                    name="slug"
                                    type="text"
                                    placeholder="product-slug"
                                />
                            </FormField>

                            <FormField label="SKU" id="sku" :error="errors.sku">
                                <Input
                                    id="sku"
                                    name="sku"
                                    type="text"
                                    placeholder="SKU-001"
                                />
                            </FormField>

                            <FormField
                                label="Short Description"
                                id="short_description"
                                :error="errors.short_description"
                            >
                                <Input
                                    id="short_description"
                                    name="short_description"
                                    type="text"
                                    placeholder="Brief product summary"
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
                                    placeholder="Full product description..."
                                    rows="4"
                                />
                            </FormField>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="rounded-lg border p-6">
                        <h3 class="mb-4 text-lg font-medium">Pricing</h3>
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
                                    required
                                    placeholder="1999"
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
                                    placeholder="2499"
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
                                    placeholder="999"
                                />
                            </FormField>
                        </div>
                    </div>

                    <!-- Inventory -->
                    <div class="rounded-lg border p-6">
                        <h3 class="mb-4 text-lg font-medium">Inventory</h3>
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
                                    value="0"
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
                                    value="5"
                                />
                            </FormField>
                        </div>
                    </div>

                    <!-- Status & Options -->
                    <div class="rounded-lg border p-6">
                        <h3 class="mb-4 text-lg font-medium">
                            Status & Options
                        </h3>
                        <div class="space-y-4">
                            <FormField
                                label="Status"
                                id="status"
                                :error="errors.status"
                            >
                                <select
                                    id="status"
                                    name="status"
                                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </FormField>

                            <div class="flex flex-wrap gap-6">
                                <div class="flex items-center gap-2">
                                    <Checkbox
                                        id="is_active"
                                        name="is_active"
                                        :default-checked="true"
                                        value="1"
                                    />
                                    <Label for="is_active">Active</Label>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Checkbox
                                        id="is_featured"
                                        name="is_featured"
                                        value="1"
                                    />
                                    <Label for="is_featured">Featured</Label>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Checkbox
                                        id="is_digital"
                                        name="is_digital"
                                        value="1"
                                    />
                                    <Label for="is_digital">
                                        Digital Product
                                    </Label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="rounded-lg border p-6">
                        <h3 class="mb-4 text-lg font-medium">Categories</h3>
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
                                />
                                <Label :for="`category_${category.id}`">
                                    {{ category.name }}
                                </Label>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">
                            No categories available. Create one first.
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Create Product
                        </Button>
                        <Link
                            href="/admin/ecommerce/products"
                            class="text-sm text-muted-foreground hover:underline"
                        >
                            Cancel
                        </Link>
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
                                Created.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </div>
    </ModuleLayout>
</template>
