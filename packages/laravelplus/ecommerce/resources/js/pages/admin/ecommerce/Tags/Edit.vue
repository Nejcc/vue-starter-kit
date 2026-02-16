<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';

import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useEcommerceNav } from '@ecommerce/composables/useEcommerceNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useEcommerceNav();

interface Tag {
    id: number;
    name: string;
    slug: string;
    type: string | null;
    sort_order: number;
}

interface EditTagPageProps {
    tag: Tag;
}

const props = defineProps<EditTagPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Tags', href: '/admin/ecommerce/tags' },
    { title: props.tag.name, href: '#' },
];
</script>

<template>
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head :title="`Edit ${tag.name}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    :title="`Edit ${tag.name}`"
                    description="Update tag details"
                />

                <Form
                    :action="`/admin/ecommerce/tags/${tag.slug}`"
                    method="put"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="rounded-lg border p-6">
                        <h3 class="mb-4 text-lg font-medium">
                            Tag Information
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
                                    :value="tag.name"
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
                                    :value="tag.slug"
                                />
                            </FormField>

                            <div class="grid gap-4 md:grid-cols-2">
                                <FormField
                                    label="Type"
                                    id="type"
                                    :error="errors.type"
                                >
                                    <Input
                                        id="type"
                                        name="type"
                                        type="text"
                                        :value="tag.type ?? ''"
                                        placeholder="e.g. product"
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
                                        :value="String(tag.sort_order)"
                                    />
                                </FormField>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Update Tag
                        </Button>
                        <Link
                            href="/admin/ecommerce/tags"
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
                                Updated.
                            </p>
                        </Transition>
                    </div>
                </Form>

                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/ecommerce/tags"
                        class="text-sm text-muted-foreground hover:underline"
                    >
                        Back to Tags
                    </Link>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
