<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { FolderTree, Plus, Trash2 } from 'lucide-vue-next';

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

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    parent_id: number | null;
    is_active: boolean;
    sort_order: number;
    created_at: string;
    parent?: { id: number; name: string } | null;
    products_count?: number;
}

interface CategoriesPageProps {
    categories: PaginatedResponse<Category>;
    filters?: {
        search?: string;
    };
    status?: string;
}

const props = defineProps<CategoriesPageProps>();
const { formatDate } = useDateFormat();
const { searchQuery, handleSearch, clearSearch } = useSearch({
    url: '/admin/ecommerce/categories',
});

if (props.filters?.search) {
    searchQuery.value = props.filters.search;
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Categories', href: '#' },
];

function deleteCategory(id: number) {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(`/admin/ecommerce/categories/${id}`);
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
        <Head title="Categories" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Categories"
                        description="Organize your products into categories"
                    />
                    <Link href="/admin/ecommerce/categories/create">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Category
                        </Button>
                    </Link>
                </div>

                <SearchInput
                    v-model="searchQuery"
                    placeholder="Search categories..."
                    show-clear
                    @search="handleSearch"
                    @clear="clearSearch"
                />

                <template v-if="categories.data.length > 0">
                    <div class="grid gap-4">
                        <DataCard
                            v-for="category in categories.data"
                            :key="category.id"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                                    >
                                        <FolderTree
                                            class="h-5 w-5 text-primary"
                                        />
                                    </div>
                                    <div>
                                        <Link
                                            :href="`/admin/ecommerce/categories/${category.id}/edit`"
                                            class="font-medium hover:underline"
                                        >
                                            {{ category.name }}
                                        </Link>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ category.slug }}
                                        </p>
                                        <p
                                            v-if="category.parent"
                                            class="mt-1 text-sm text-muted-foreground"
                                        >
                                            Parent: {{ category.parent.name }}
                                        </p>
                                    </div>
                                </div>
                                <StatusBadge
                                    :label="
                                        category.is_active
                                            ? 'Active'
                                            : 'Inactive'
                                    "
                                    :variant="
                                        category.is_active
                                            ? 'success'
                                            : 'default'
                                    "
                                />
                            </div>

                            <template #footer>
                                <div class="flex items-center justify-between">
                                    <div
                                        class="flex items-center gap-4 text-sm text-muted-foreground"
                                    >
                                        <span
                                            v-if="
                                                category.products_count !==
                                                undefined
                                            "
                                        >
                                            {{ category.products_count }}
                                            product(s)
                                        </span>
                                        <span>
                                            Created
                                            {{
                                                formatDate(category.created_at)
                                            }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/admin/ecommerce/categories/${category.id}/edit`"
                                        >
                                            <Button variant="outline" size="sm">
                                                Edit
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="text-destructive hover:text-destructive"
                                            @click="deleteCategory(category.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </template>
                        </DataCard>
                    </div>

                    <Pagination :pagination="categories" />
                </template>

                <SearchEmptyState
                    v-else-if="filters?.search"
                    title="No categories found"
                    :search-query="filters.search"
                    @clear="clearSearch"
                />

                <EmptyState
                    v-else
                    title="No categories yet"
                    description="Create your first category to organize products."
                />
            </div>
        </div>
    </ModuleLayout>
</template>
