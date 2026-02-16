<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Tags as TagsIcon, Trash2 } from 'lucide-vue-next';

import DataCard from '@/components/DataCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchEmptyState from '@/components/SearchEmptyState.vue';
import SearchInput from '@/components/SearchInput.vue';
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

interface Tag {
    id: number;
    name: string;
    slug: string;
    type: string | null;
    sort_order: number;
    created_at: string;
}

interface TagsPageProps {
    tags: PaginatedResponse<Tag>;
    filters?: {
        search?: string;
    };
    status?: string;
}

const props = defineProps<TagsPageProps>();
const { searchQuery, handleSearch, clearSearch } = useSearch({
    url: '/admin/ecommerce/tags',
});

if (props.filters?.search) {
    searchQuery.value = props.filters.search;
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Tags', href: '#' },
];

function deleteTag(slug: string) {
    if (confirm('Are you sure you want to delete this tag?')) {
        router.delete(`/admin/ecommerce/tags/${slug}`);
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
        <Head title="Tags" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Tags"
                        description="Manage product tags"
                    />
                    <Link href="/admin/ecommerce/tags/create">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add Tag
                        </Button>
                    </Link>
                </div>

                <SearchInput
                    v-model="searchQuery"
                    placeholder="Search tags..."
                    show-clear
                    @search="handleSearch"
                    @clear="clearSearch"
                />

                <template v-if="tags.data.length > 0">
                    <div class="grid gap-4">
                        <DataCard
                            v-for="tag in tags.data"
                            :key="tag.id"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                                    >
                                        <TagsIcon class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <Link
                                            :href="`/admin/ecommerce/tags/${tag.slug}/edit`"
                                            class="font-medium hover:underline"
                                        >
                                            {{ tag.name }}
                                        </Link>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ tag.slug }}
                                            <span v-if="tag.type">
                                                &middot; Type: {{ tag.type }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm text-muted-foreground"
                                    >
                                        Order: {{ tag.sort_order }}
                                    </span>
                                </div>
                            </div>

                            <template #footer>
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="`/admin/ecommerce/tags/${tag.slug}/edit`"
                                    >
                                        <Button variant="outline" size="sm">
                                            Edit
                                        </Button>
                                    </Link>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="text-destructive hover:text-destructive"
                                        @click="deleteTag(tag.slug)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </template>
                        </DataCard>
                    </div>

                    <Pagination :pagination="tags" />
                </template>

                <SearchEmptyState
                    v-else-if="filters?.search"
                    title="No tags found"
                    :search-query="filters.search"
                    @clear="clearSearch"
                />

                <EmptyState
                    v-else
                    title="No tags yet"
                    description="Create your first tag to get started."
                />
            </div>
        </div>
    </ModuleLayout>
</template>
