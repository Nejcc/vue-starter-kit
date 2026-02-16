<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, SlidersHorizontal, Trash2 } from 'lucide-vue-next';

import DataCard from '@/components/DataCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchEmptyState from '@/components/SearchEmptyState.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { useEcommerceNav } from '@ecommerce/composables/useEcommerceNav';
import { useSearch } from '@/composables/useSearch';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem, type PaginatedResponse } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useEcommerceNav();

interface AttributeGroup {
    id: number;
    name: string;
    slug: string;
    sort_order: number;
    is_active: boolean;
    created_at: string;
    attributes_count?: number;
}

interface AttributesPageProps {
    attributeGroups: PaginatedResponse<AttributeGroup>;
    filters?: {
        search?: string;
    };
    status?: string;
}

const props = defineProps<AttributesPageProps>();
const { searchQuery, handleSearch, clearSearch } = useSearch({
    url: '/admin/ecommerce/attributes',
});

if (props.filters?.search) {
    searchQuery.value = props.filters.search;
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Attributes', href: '#' },
];

function deleteGroup(slug: string) {
    if (confirm('Are you sure you want to delete this attribute group and all its attributes?')) {
        router.delete(`/admin/ecommerce/attributes/${slug}`);
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
        <Head title="Attributes" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Attribute Groups"
                        description="Manage product attribute groups and their attributes"
                    />
                    <Link href="/admin/ecommerce/attributes/create">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Group
                        </Button>
                    </Link>
                </div>

                <SearchInput
                    v-model="searchQuery"
                    placeholder="Search attribute groups..."
                    show-clear
                    @search="handleSearch"
                    @clear="clearSearch"
                />

                <template v-if="attributeGroups.data.length > 0">
                    <div class="grid gap-4">
                        <DataCard
                            v-for="group in attributeGroups.data"
                            :key="group.id"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                                    >
                                        <SlidersHorizontal class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <Link
                                            :href="`/admin/ecommerce/attributes/${group.slug}/edit`"
                                            class="font-medium hover:underline"
                                        >
                                            {{ group.name }}
                                        </Link>
                                        <p class="text-sm text-muted-foreground">
                                            {{ group.slug }}
                                            &middot; Order: {{ group.sort_order }}
                                        </p>
                                    </div>
                                </div>
                                <StatusBadge
                                    :label="group.is_active ? 'Active' : 'Inactive'"
                                    :variant="group.is_active ? 'success' : 'default'"
                                />
                            </div>

                            <template #footer>
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="`/admin/ecommerce/attributes/${group.slug}/edit`"
                                    >
                                        <Button variant="outline" size="sm">
                                            Edit
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="text-destructive hover:text-destructive"
                                        @click="deleteGroup(group.slug)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </template>
                        </DataCard>
                    </div>

                    <Pagination :pagination="attributeGroups" />
                </template>

                <SearchEmptyState
                    v-else-if="filters?.search"
                    title="No attribute groups found"
                    :search-query="filters.search"
                    @clear="clearSearch"
                />

                <EmptyState
                    v-else
                    title="No attribute groups yet"
                    description="Create your first attribute group to get started."
                />
            </div>
        </div>
    </ModuleLayout>
</template>
