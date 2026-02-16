<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronRight, FolderTree } from 'lucide-vue-next';

import Heading from '@/components/Heading.vue';
import { useEcommerceNav } from '@ecommerce/composables/useEcommerceNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useEcommerceNav();

interface TreeCategory {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
    sort_order: number;
    children?: TreeCategory[];
}

interface TreePageProps {
    tree: TreeCategory[];
}

defineProps<TreePageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Categories', href: '/admin/ecommerce/categories' },
    { title: 'Tree', href: '#' },
];
</script>

<template>
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head title="Category Tree" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Category Tree"
                    description="Visual hierarchy of product categories"
                />

                <div v-if="tree.length > 0" class="rounded-lg border p-6">
                    <ul class="space-y-1">
                        <template v-for="category in tree" :key="category.id">
                            <li>
                                <div
                                    class="flex items-center gap-2 rounded-md px-3 py-2 hover:bg-muted"
                                >
                                    <FolderTree
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <Link
                                        :href="`/admin/ecommerce/categories/${category.id}/edit`"
                                        class="font-medium hover:underline"
                                    >
                                        {{ category.name }}
                                    </Link>
                                    <span
                                        v-if="!category.is_active"
                                        class="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                                    >
                                        Inactive
                                    </span>
                                </div>

                                <!-- Children level 1 -->
                                <ul
                                    v-if="
                                        category.children &&
                                        category.children.length > 0
                                    "
                                    class="ml-6 space-y-1"
                                >
                                    <li
                                        v-for="child in category.children"
                                        :key="child.id"
                                    >
                                        <div
                                            class="flex items-center gap-2 rounded-md px-3 py-2 hover:bg-muted"
                                        >
                                            <ChevronRight
                                                class="h-4 w-4 text-muted-foreground"
                                            />
                                            <Link
                                                :href="`/admin/ecommerce/categories/${child.id}/edit`"
                                                class="text-sm font-medium hover:underline"
                                            >
                                                {{ child.name }}
                                            </Link>
                                            <span
                                                v-if="!child.is_active"
                                                class="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                                            >
                                                Inactive
                                            </span>
                                        </div>

                                        <!-- Children level 2 -->
                                        <ul
                                            v-if="
                                                child.children &&
                                                child.children.length > 0
                                            "
                                            class="ml-6 space-y-1"
                                        >
                                            <li
                                                v-for="grandchild in child.children"
                                                :key="grandchild.id"
                                            >
                                                <div
                                                    class="flex items-center gap-2 rounded-md px-3 py-2 hover:bg-muted"
                                                >
                                                    <ChevronRight
                                                        class="h-4 w-4 text-muted-foreground"
                                                    />
                                                    <Link
                                                        :href="`/admin/ecommerce/categories/${grandchild.id}/edit`"
                                                        class="text-sm hover:underline"
                                                    >
                                                        {{ grandchild.name }}
                                                    </Link>
                                                    <span
                                                        v-if="
                                                            !grandchild.is_active
                                                        "
                                                        class="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground"
                                                    >
                                                        Inactive
                                                    </span>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </template>
                    </ul>
                </div>

                <div
                    v-else
                    class="rounded-lg border p-8 text-center text-muted-foreground"
                >
                    <FolderTree class="mx-auto mb-4 h-12 w-12 opacity-50" />
                    <p class="text-lg font-medium">No categories yet</p>
                    <p class="mt-1 text-sm">
                        Create your first category to see the tree.
                    </p>
                    <Link
                        href="/admin/ecommerce/categories/create"
                        class="mt-4 inline-block text-sm font-medium text-primary hover:underline"
                    >
                        Create Category
                    </Link>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
