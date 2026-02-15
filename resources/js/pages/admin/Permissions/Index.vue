<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { useSearch } from '@/composables/useSearch';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { create, edit, index } from '@/routes/admin/permissions';
import { type BreadcrumbItem, type PaginatedResponse } from '@/types';

interface Permission {
    id: number;
    name: string;
    group_name: string | null;
    roles: string[];
    roles_count: number;
    created_at: string;
}

interface PermissionsPageProps {
    permissions: PaginatedResponse<Permission>;
    groups: string[];
    status?: string;
    filters?: {
        search?: string;
        group?: string;
    };
}

const props = defineProps<PermissionsPageProps>();
const { formatDate } = useDateFormat();

const { searchQuery, handleSearch, clearSearch } = useSearch({
    url: index().url,
});
searchQuery.value = props.filters?.search ?? '';

function filterByGroup(group: string): void {
    router.get(
        index().url,
        {
            search: props.filters?.search || undefined,
            group: group || undefined,
        },
        { preserveState: true },
    );
}

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Permissions',
        href: index().url,
    },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Permissions" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Permissions"
                        description="Manage application permissions"
                    />
                    <Link
                        :href="create().url"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        Create New Permission
                    </Link>
                </div>

                <div
                    v-if="status"
                    class="rounded-md bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-400"
                >
                    {{ status }}
                </div>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <SearchInput
                            v-model="searchQuery"
                            placeholder="Search permissions by name or group..."
                            :show-clear="!!filters?.search"
                            @search="handleSearch"
                            @clear="clearSearch"
                        />
                    </div>
                    <select
                        v-if="groups.length > 0"
                        :value="filters?.group ?? ''"
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                        @change="
                            filterByGroup(
                                ($event.target as HTMLSelectElement).value,
                            )
                        "
                    >
                        <option value="">All Groups</option>
                        <option
                            v-for="group in groups"
                            :key="group"
                            :value="group"
                        >
                            {{ group }}
                        </option>
                    </select>
                </div>

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
                                    Group
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Roles
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Created
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
                                v-for="permission in permissions.data"
                                :key="permission.id"
                                class="border-b last:border-b-0"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ permission.name }}
                                </td>
                                <td class="px-4 py-3">
                                    <StatusBadge
                                        v-if="permission.group_name"
                                        :label="permission.group_name"
                                        variant="default"
                                    />
                                    <span
                                        v-else
                                        class="text-sm text-muted-foreground"
                                        >—</span
                                    >
                                </td>
                                <td class="px-4 py-3">
                                    <div
                                        v-if="permission.roles.length > 0"
                                        class="flex flex-wrap gap-1"
                                    >
                                        <StatusBadge
                                            v-for="role in permission.roles"
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
                                <td
                                    class="px-4 py-3 text-sm text-muted-foreground"
                                >
                                    {{ formatDate(permission.created_at) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="edit(permission.name).url"
                                        class="text-sm text-primary hover:underline"
                                    >
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="permissions.data.length === 0">
                                <td
                                    colspan="5"
                                    class="px-4 py-8 text-center text-muted-foreground"
                                >
                                    No permissions found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :pagination="permissions" />
            </div>
        </div>
    </AdminLayout>
</template>
