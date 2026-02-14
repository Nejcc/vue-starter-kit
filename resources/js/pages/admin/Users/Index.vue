<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { useSearch } from '@/composables/useSearch';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { create, edit, exportMethod, index } from '@/routes/admin/users';
import { type BreadcrumbItem, type PaginatedResponse } from '@/types';

interface User {
    id: number;
    slug: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    roles: string[];
    suspended_at: string | null;
    created_at: string;
}

interface UsersPageProps {
    users: PaginatedResponse<User>;
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
                    <div class="flex items-center gap-2">
                        <a
                            :href="exportMethod().url"
                            class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            Export CSV
                        </a>
                        <Link
                            :href="create().url"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                        >
                            Create New User
                        </Link>
                    </div>
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

                <div class="overflow-x-auto rounded-lg border">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Name
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Email
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Roles
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-sm font-semibold"
                                >
                                    Verified
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Joined
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-sm font-semibold"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="border-b last:border-b-0"
                            >
                                <td class="px-4 py-3 font-medium">
                                    <div class="flex items-center gap-2">
                                        {{ user.name }}
                                        <StatusBadge
                                            v-if="user.suspended_at"
                                            label="Suspended"
                                            variant="danger"
                                        />
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3 text-sm text-muted-foreground"
                                >
                                    {{ user.email }}
                                </td>
                                <td class="px-4 py-3">
                                    <div
                                        v-if="user.roles.length > 0"
                                        class="flex flex-wrap gap-1"
                                    >
                                        <StatusBadge
                                            v-for="role in user.roles"
                                            :key="role"
                                            :label="role"
                                            variant="info"
                                        />
                                    </div>
                                    <span
                                        v-else
                                        class="text-sm text-muted-foreground"
                                        >None</span
                                    >
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <StatusBadge
                                        v-if="user.email_verified_at"
                                        label="Verified"
                                        variant="success"
                                    />
                                    <span
                                        v-else
                                        class="text-sm text-muted-foreground"
                                        >No</span
                                    >
                                </td>
                                <td
                                    class="px-4 py-3 text-sm text-muted-foreground"
                                >
                                    {{ formatDate(user.created_at) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="edit(user.slug).url"
                                        class="text-sm text-primary hover:underline"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-4 py-8 text-center text-muted-foreground"
                                >
                                    No users found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :pagination="users" />
            </div>
        </div>
    </AdminLayout>
</template>
