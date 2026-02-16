<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';

import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useEcommerceNav } from '@ecommerce/composables/useEcommerceNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useEcommerceNav();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Attributes', href: '/admin/ecommerce/attributes' },
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
        <Head title="Create Attribute Group" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Create Attribute Group"
                    description="Add a new attribute group"
                />

                <Form
                    action="/admin/ecommerce/attributes"
                    method="post"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="rounded-lg border p-6">
                        <h3 class="mb-4 text-lg font-medium">
                            Group Information
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
                                    placeholder="e.g. Physical Properties"
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
                                    placeholder="physical-properties"
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
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Create Group
                        </Button>
                        <Link
                            href="/admin/ecommerce/attributes"
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
