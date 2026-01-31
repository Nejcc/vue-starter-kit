<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Users } from 'lucide-vue-next';
import { ref } from 'vue';

import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import SearchEmptyState from '@/components/SearchEmptyState.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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

const searchQuery = ref(props.filters?.search ?? '');

const debouncedSearch = useDebounceFn((query: string) => {
    router.get(
        index().url,
        { search: query || null },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}, 300);

const handleSearch = (): void => {
    debouncedSearch(searchQuery.value);
};

const performSearch = (): void => {
    debouncedSearch(searchQuery.value);
};

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

                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <Input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search users by name or email..."
                            class="w-full"
                            @input="handleSearch"
                        />
                    </div>
                    <Button
                        v-if="filters?.search"
                        variant="outline"
                        @click="
                            router.get(
                                index().url,
                                {},
                                { preserveState: false },
                            )
                        "
                    >
                        Clear
                    </Button>
                </div>

                <div v-if="users.data.length === 0">
                    <SearchEmptyState
                        v-if="filters?.search"
                        :search-query="filters.search"
                        @clear-search="
                            router.get(
                                index().url,
                                {},
                                { preserveState: false },
                            )
                        "
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
                    <div
                        v-for="user in users.data"
                        :key="user.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-medium">
                                        {{ user.name }}
                                    </h3>
                                    <span
                                        v-if="user.email_verified_at"
                                        class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400"
                                    >
                                        Verified
                                    </span>
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
                                    <span
                                        v-for="role in user.roles"
                                        :key="role"
                                        class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                                    >
                                        {{ role }}
                                    </span>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Joined:
                                    {{
                                        new Date(
                                            user.created_at,
                                        ).toLocaleDateString()
                                    }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="edit(user.slug).url"
                                    class="text-sm text-primary hover:underline"
                                >
                                    Edit
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
