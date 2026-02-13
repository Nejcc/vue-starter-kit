<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';

import DataCard from '@/components/DataCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import SearchEmptyState from '@/components/SearchEmptyState.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { useSearch } from '@/composables/useSearch';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { create, edit, index } from '@/routes/admin/users';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    slug: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    roles: string[];
    created_at: string;
}

interface UsersPageProps {
    users: {
        data: User[];
        links: any[];
        meta: any;
    };
    status?: string;
    filters?: {
        search?: string;
    };
}

const props = defineProps<UsersPageProps>();
const { formatDate } = useDateFormat();

const { searchQuery, handleSearch, clearSearch } = useSearch({
    url: index().url,
});
searchQuery.value = props.filters?.search ?? '';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Users',
        href: index().url,
    },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Users" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Users"
                        description="Manage application users"
                    />
                    <Link
                        :href="create().url"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        Create New User
                    </Link>
                </div>

                <div
                    v-if="status"
                    class="rounded-md bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-400"
                >
                    {{ status }}
                </div>

                <SearchInput
                    v-model="searchQuery"
                    placeholder="Search users by name or email..."
                    :show-clear="!!filters?.search"
                    @search="handleSearch"
                    @clear="clearSearch"
                />

                <div v-if="users.data.length === 0">
                    <SearchEmptyState
                        v-if="filters?.search"
                        :search-query="filters.search"
                        @clear-search="clearSearch"
                    />
                    <EmptyState
                        v-else
                        :icon="Users"
                        title="No users yet"
                        description="Get started by creating your first user."
                        action-text="Create User"
                        :action-href="create().url"
                    />
                </div>

                <div v-else class="space-y-4">
                    <DataCard
                        v-for="user in users.data"
                        :key="user.id"
                    >
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-medium">
                                {{ user.name }}
                            </h3>
                            <StatusBadge
                                v-if="user.email_verified_at"
                                label="Verified"
                                variant="success"
                            />
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ user.email }}
                        </p>
                        <div
                            v-if="user.roles.length > 0"
                            class="flex items-center gap-2"
                        >
                            <span
                                class="text-sm font-medium text-muted-foreground"
                                >Roles:</span
                            >
                            <StatusBadge
                                v-for="role in user.roles"
                                :key="role"
                                :label="role"
                                variant="info"
                            />
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Joined: {{ formatDate(user.created_at) }}
                        </p>
                        <template #actions>
                            <Link
                                :href="edit(user.slug).url"
                                class="text-sm text-primary hover:underline"
                            >
                                Edit
                            </Link>
                        </template>
                    </DataCard>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
