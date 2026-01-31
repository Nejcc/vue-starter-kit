<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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

                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <Input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search permissions by name or group..."
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
                            <div
                                v-for="permission in group"
                                :key="permission.id"
                                class="rounded-lg border p-4"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 space-y-2">
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
                                                <span
                                                    v-for="role in permission.roles"
                                                    :key="role"
                                                    class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                                                >
                                                    {{ role }}
                                                </span>
                                            </div>
                                        </div>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Created:
                                            {{
                                                new Date(
                                                    permission.created_at,
                                                ).toLocaleDateString()
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="space-y-4">
                    <div
                        v-for="permission in permissions"
                        :key="permission.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-medium">
                                        {{ permission.name }}
                                    </h3>
                                    <span
                                        v-if="permission.group_name"
                                        class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-900/20 dark:text-gray-400"
                                    >
                                        {{ permission.group_name }}
                                    </span>
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
                                        <span
                                            v-for="role in permission.roles"
                                            :key="role"
                                            class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                                        >
                                            {{ role }}
                                        </span>
                                    </div>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Created:
                                    {{
                                        new Date(
                                            permission.created_at,
                                        ).toLocaleDateString()
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
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
