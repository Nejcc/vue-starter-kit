<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import DataCard from '@/components/DataCard.vue';
import Heading from '@/components/Heading.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import { useSearch } from '@/composables/useSearch';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { create, edit, index } from '@/routes/admin/permissions';
import { type BreadcrumbItem } from '@/types';

interface Permission {
    id: number;
    name: string;
    group_name: string | null;
    roles: string[];
    roles_count: number;
    created_at: string;
}

interface PermissionsPageProps {
    permissions: Permission[];
    groupedPermissions?: Record<string, Permission[]>;
    status?: string;
    filters?: {
        search?: string;
    };
}

const props = defineProps<PermissionsPageProps>();
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

                <SearchInput
                    v-model="searchQuery"
                    placeholder="Search permissions by name or group..."
                    :show-clear="!!filters?.search"
                    @search="handleSearch"
                    @clear="clearSearch"
                />

                <div v-if="groupedPermissions" class="space-y-6">
                    <div
                        v-for="(group, groupName) in groupedPermissions"
                        :key="groupName || 'ungrouped'"
                        class="space-y-4"
                    >
                        <h2 class="text-lg font-semibold">
                            {{ groupName || 'Ungrouped' }}
                        </h2>
                        <div class="space-y-4">
                            <DataCard
                                v-for="permission in group"
                                :key="permission.id"
                            >
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-medium">
                                        {{ permission.name }}
                                    </h3>
                                    <Link
                                        :href="edit(permission.name).url"
                                        class="text-sm text-primary hover:underline"
                                    >
                                        Edit
                                    </Link>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-muted-foreground"
                                        >Roles:</span
                                    >
                                    <span class="text-sm">{{
                                        permission.roles_count
                                    }}</span>
                                </div>
                                <div
                                    v-if="permission.roles.length > 0"
                                    class="space-y-1"
                                >
                                    <span
                                        class="text-sm font-medium text-muted-foreground"
                                        >Assigned to roles:</span
                                    >
                                    <div class="flex flex-wrap gap-2">
                                        <StatusBadge
                                            v-for="role in permission.roles"
                                            :key="role"
                                            :label="role"
                                            variant="info"
                                        />
                                    </div>
                                </div>
                                <p
                                    class="text-xs text-muted-foreground"
                                >
                                    Created: {{ formatDate(permission.created_at) }}
                                </p>
                            </DataCard>
                        </div>
                    </div>
                </div>
                <div v-else class="space-y-4">
                    <DataCard
                        v-for="permission in permissions"
                        :key="permission.id"
                    >
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-medium">
                                {{ permission.name }}
                            </h3>
                            <StatusBadge
                                v-if="permission.group_name"
                                :label="permission.group_name"
                                variant="default"
                            />
                            <Link
                                :href="edit(permission.name).url"
                                class="text-sm text-primary hover:underline"
                            >
                                Edit
                            </Link>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-sm font-medium text-muted-foreground"
                                >Roles:</span
                            >
                            <span class="text-sm">{{
                                permission.roles_count
                            }}</span>
                        </div>
                        <div
                            v-if="permission.roles.length > 0"
                            class="space-y-1"
                        >
                            <span
                                class="text-sm font-medium text-muted-foreground"
                                >Assigned to roles:</span
                            >
                            <div class="flex flex-wrap gap-2">
                                <StatusBadge
                                    v-for="role in permission.roles"
                                    :key="role"
                                    :label="role"
                                    variant="info"
                                />
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Created: {{ formatDate(permission.created_at) }}
                        </p>
                    </DataCard>
                </div>

                <div
                    v-if="permissions.length === 0"
                    class="rounded-lg border p-8 text-center"
                >
                    <p class="text-muted-foreground">No permissions found.</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
