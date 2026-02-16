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

interface CreateCategoryPageProps {
    parentCategories: Array<{ id: number; name: string }>;
}

defineProps<CreateCategoryPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Categories', href: '/admin/ecommerce/categories' },
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
        <Head title="Create Category" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Create Category"
                    description="Add a new product category"
                />

                <Form
                    action="/admin/ecommerce/categories"
                    method="post"
                    class="space-y-6"
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
                            required
                            placeholder="Category name"
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
                            placeholder="category-slug"
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
                            placeholder="Category description..."
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
                            value="0"
                        />
                    </FormField>

                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="is_active"
                            name="is_active"
                            :default-checked="true"
                            value="1"
                        />
                        <Label for="is_active">Active</Label>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Create Category
                        </Button>
                        <Link
                            href="/admin/ecommerce/categories"
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
