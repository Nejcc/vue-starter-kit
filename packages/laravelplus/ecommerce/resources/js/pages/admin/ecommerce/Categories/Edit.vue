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

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    parent_id: number | null;
    image: string | null;
    is_active: boolean;
    sort_order: number;
    parent?: { id: number; name: string } | null;
    children?: Array<{ id: number; name: string }>;
}

interface EditCategoryPageProps {
    category: Category;
    parentCategories: Array<{ id: number; name: string }>;
}

const props = defineProps<EditCategoryPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Categories', href: '/admin/ecommerce/categories' },
    { title: props.category.name, href: '#' },
];
</script>

<template>
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head :title="`Edit ${category.name}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-8">
                <Heading
                    variant="small"
                    :title="`Edit ${category.name}`"
                    description="Update category details"
                />

                <div class="rounded-lg border p-6">
                    <h3 class="mb-4 text-lg font-medium">Category Details</h3>
                    <Form
                        :action="`/admin/ecommerce/categories/${category.id}`"
                        method="put"
                        class="space-y-4"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
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
                                :value="category.name"
                                required
                            />
                        </FormField>

                        <FormField label="Slug" id="slug" :error="errors.slug">
                            <Input
                                id="slug"
                                name="slug"
                                type="text"
                                :value="category.slug"
                            />
                        </FormField>

                        <FormField
                            label="Parent Category"
                            id="parent_id"
                            :error="errors.parent_id"
                        >
                            <select
                                id="parent_id"
                                name="parent_id"
                                :value="category.parent_id ?? ''"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm"
                            >
                                <option value="">None (Root Category)</option>
                                <option
                                    v-for="parent in parentCategories"
                                    :key="parent.id"
                                    :value="parent.id"
                                >
                                    {{ parent.name }}
                                </option>
                            </select>
                        </FormField>

                        <FormField
                            label="Description"
                            id="description"
                            :error="errors.description"
                        >
                            <Textarea
                                id="description"
                                name="description"
                                :value="category.description ?? ''"
                                rows="3"
                            />
                        </FormField>

                        <FormField
                            label="Sort Order"
                            id="sort_order"
                            :error="errors.sort_order"
                        >
                            <Input
                                id="sort_order"
                                name="sort_order"
                                type="number"
                                min="0"
                                :value="String(category.sort_order)"
                            />
                        </FormField>

                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="is_active"
                                name="is_active"
                                :default-checked="category.is_active"
                                value="1"
                            />
                            <Label for="is_active">Active</Label>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button :disabled="processing" type="submit">
                                Update Category
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

                <!-- Children Section -->
                <div
                    v-if="category.children && category.children.length > 0"
                    class="rounded-lg border p-6"
                >
                    <h3 class="mb-4 text-lg font-medium">Child Categories</h3>
                    <div class="space-y-2">
                        <div
                            v-for="child in category.children"
                            :key="child.id"
                            class="flex items-center justify-between rounded-lg border p-3"
                        >
                            <Link
                                :href="`/admin/ecommerce/categories/${child.id}/edit`"
                                class="font-medium hover:underline"
                            >
                                {{ child.name }}
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/ecommerce/categories"
                        class="text-sm text-muted-foreground hover:underline"
                    >
                        Back to Categories
                    </Link>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
