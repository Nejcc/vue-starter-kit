<script setup lang="ts">
import { create, destroy, edit, index } from '@/routes/admin/roles';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ref } from 'vue';

import FormErrors from '@/components/FormErrors.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

const page = usePage();

interface Role {
    id: number;
    name: string;
    is_super_admin?: boolean;
    permissions: string[];
    users_count: number;
    created_at: string;
}

interface RolesPageProps {
    roles: Role[];
    status?: string;
    filters?: {
        search?: string;
    };
}

const props = defineProps<RolesPageProps>();

const searchQuery = ref(props.filters?.search ?? '');
const showDeleteDialog = ref(false);
const roleToDelete = ref<{ id: number; name: string } | null>(null);

const deleteRole = (
    roleId: number,
    roleName: string,
    isSuperAdmin: boolean,
): void => {
    if (isSuperAdmin) {
        alert(
            'The super-admin role cannot be deleted. It is a system role with all permissions.',
        );
        return;
    }

    roleToDelete.value = { id: roleId, name: roleName };
    showDeleteDialog.value = true;
};

const confirmDelete = (): void => {
    if (roleToDelete.value) {
        router.delete(destroy(roleToDelete.value.id).url);
        showDeleteDialog.value = false;
        roleToDelete.value = null;
    }
};

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
        title: 'Roles',
        href: index().url,
    },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Roles" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <HeadingSmall
                        title="Roles"
                        description="Manage application roles"
                    />
                    <Link
                        :href="create().url"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        Create New Role
                    </Link>
                </div>

                <div
                    v-if="status"
                    class="rounded-md bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-400"
                >
                    {{ status }}
                </div>

                <FormErrors
                    :errors="page.props.errors as Record<string, string>"
                />

                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <Input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search roles by name..."
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

                <div class="space-y-4">
                    <div
                        v-for="role in roles"
                        :key="role.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-medium">
                                        {{ role.name }}
                                    </h3>
                                    <span
                                        v-if="role.is_super_admin"
                                        class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/20 dark:text-red-400"
                                        title="This role has all permissions automatically granted"
                                    >
                                        System Role
                                    </span>
                                </div>
                                <p
                                    v-if="role.is_super_admin"
                                    class="text-sm text-muted-foreground italic"
                                >
                                    This role has all permissions automatically
                                    granted. No permissions need to be assigned.
                                </p>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-muted-foreground"
                                        >Users:</span
                                    >
                                    <span class="text-sm">{{
                                        role.users_count
                                    }}</span>
                                </div>
                                <div
                                    v-if="role.is_super_admin"
                                    class="space-y-1"
                                >
                                    <span
                                        class="text-sm font-medium text-muted-foreground"
                                        >Permissions:</span
                                    >
                                    <span
                                        class="text-sm text-muted-foreground italic"
                                        >All permissions (automatically
                                        granted)</span
                                    >
                                </div>
                                <div
                                    v-else-if="role.permissions.length > 0"
                                    class="space-y-1"
                                >
                                    <span
                                        class="text-sm font-medium text-muted-foreground"
                                        >Permissions:</span
                                    >
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="permission in role.permissions"
                                            :key="permission"
                                            class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900/20 dark:text-purple-400"
                                        >
                                            {{ permission }}
                                        </span>
                                    </div>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Created:
                                    {{
                                        new Date(
                                            role.created_at,
                                        ).toLocaleDateString()
                                    }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="!role.is_super_admin"
                                    :href="edit(role.id).url"
                                    class="text-sm text-primary hover:underline"
                                >
                                    Edit
                                </Link>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground"
                                    title="Super-admin role cannot be edited"
                                >
                                    Edit
                                </span>
                                <button
                                    type="button"
                                    :disabled="role.is_super_admin"
                                    @click="
                                        deleteRole(
                                            role.id,
                                            role.name,
                                            role.is_super_admin ?? false,
                                        )
                                    "
                                    :class="{
                                        'text-sm text-destructive hover:underline':
                                            !role.is_super_admin,
                                        'cursor-not-allowed text-sm text-muted-foreground':
                                            role.is_super_admin,
                                    }"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="roles.length === 0"
                    class="rounded-lg border p-8 text-center"
                >
                    <p class="text-muted-foreground">No roles found.</p>
                </div>
            </div>
        </div>

        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Are you sure?</DialogTitle>
                    <DialogDescription>
                        This will permanently delete the role "{{
                            roleToDelete?.name
                        }}". This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="showDeleteDialog = false">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
