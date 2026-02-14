<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Plus, Trash2, Users } from 'lucide-vue-next';

import DataCard from '@/components/DataCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchEmptyState from '@/components/SearchEmptyState.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { useDateFormat } from '@/composables/useDateFormat';
import { useOrganizationNav } from '@/composables/useOrganizationNav';
import { useSearch } from '@/composables/useSearch';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import {
    type BreadcrumbItem,
    type Organization,
    type PaginatedResponse,
} from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useOrganizationNav();

interface OrganizationsPageProps {
    organizations: PaginatedResponse<
        Organization & {
            owner?: { id: number; name: string };
            members_count?: number;
        }
    >;
    filters?: {
        search?: string;
    };
}

const props = defineProps<OrganizationsPageProps>();
const { formatDate } = useDateFormat();
const { searchQuery, handleSearch, clearSearch } = useSearch({
    url: '/admin/organizations',
});

if (props.filters?.search) {
    searchQuery.value = props.filters.search;
}

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Organizations', href: '#' },
];

function deleteOrganization(slug: string) {
    if (confirm('Are you sure you want to delete this organization?')) {
        router.delete(`/admin/organizations/${slug}`);
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
        <Head title="Organizations" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Organizations"
                        description="Manage organizations and their members"
                    />
                    <Link href="/admin/organizations/create">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create Organization
                        </Button>
                    </Link>
                </div>

                <SearchInput
                    v-model="searchQuery"
                    placeholder="Search organizations..."
                    show-clear
                    @search="handleSearch"
                    @clear="clearSearch"
                />

                <template v-if="organizations.data.length > 0">
                    <div class="grid gap-4">
                        <DataCard
                            v-for="org in organizations.data"
                            :key="org.id"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                                    >
                                        <Building2
                                            class="h-5 w-5 text-primary"
                                        />
                                    </div>
                                    <div>
                                        <Link
                                            :href="`/admin/organizations/${org.slug}/edit`"
                                            class="font-medium hover:underline"
                                        >
                                            {{ org.name }}
                                        </Link>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ org.slug }}
                                        </p>
                                        <p
                                            v-if="org.description"
                                            class="mt-1 text-sm text-muted-foreground"
                                        >
                                            {{ org.description }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <StatusBadge
                                        v-if="org.is_personal"
                                        label="Personal"
                                        variant="info"
                                    />
                                    <StatusBadge
                                        v-if="org.owner"
                                        :label="org.owner.name"
                                        variant="purple"
                                    />
                                </div>
                            </div>

                            <template #footer>
                                <div class="flex items-center justify-between">
                                    <div
                                        class="flex items-center gap-4 text-sm text-muted-foreground"
                                    >
                                        <span class="flex items-center gap-1">
                                            <Users class="h-4 w-4" />
                                            {{
                                                org.members_count ?? 0
                                            }}
                                            member(s)
                                        </span>
                                        <span
                                            >Created
                                            {{
                                                formatDate(org.created_at)
                                            }}</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/admin/organizations/${org.slug}/edit`"
                                        >
                                            <Button variant="outline" size="sm"
                                                >Edit</Button
                                            >
                                        </Link>
                                        <Button
                                            v-if="!org.is_personal"
                                            variant="outline"
                                            size="sm"
                                            class="text-destructive hover:text-destructive"
                                            @click="
                                                deleteOrganization(org.slug)
                                            "
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </template>
                        </DataCard>
                    </div>

                    <Pagination :pagination="organizations" />
                </template>

                <SearchEmptyState
                    v-else-if="filters?.search"
                    title="No organizations found"
                    :search-query="filters.search"
                    @clear="clearSearch"
                />

                <EmptyState
                    v-else
                    title="No organizations yet"
                    description="Create your first organization to get started."
                />
            </div>
        </div>
    </ModuleLayout>
</template>
