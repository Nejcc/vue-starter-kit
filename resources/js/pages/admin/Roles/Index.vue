<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import FormErrors from '@/components/FormErrors.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useDateFormat } from '@/composables/useDateFormat';
import { useSearch } from '@/composables/useSearch';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { create, destroy, edit, index } from '@/routes/admin/roles';
import { type BreadcrumbItem, type PaginatedResponse } from '@/types';

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
    roles: PaginatedResponse<Role>;
    status?: string;
    filters?: {
        search?: string;
    };
}

const props = defineProps<RolesPageProps>();
const { formatDate } = useDateFormat();

const { searchQuery, handleSearch, clearSearch } = useSearch({
    url: index().url,
});
searchQuery.value = props.filters?.search ?? '';

const showDeleteDialog = ref(false);
const roleToDelete = ref<{ name: string } | null>(null);

const deleteRole = (roleName: string, isSuperAdmin: boolean): void => {
    if (isSuperAdmin) {
        alert('The super-admin role cannot be deleted. It is a system role with all permissions.');
        return;
    }

    roleToDelete.value = { name: roleName };
    showDeleteDialog.value = true;
};

const confirmDelete = (): void => {
    if (roleToDelete.value) {
        router.delete(destroy(roleToDelete.value.name).url);
        showDeleteDialog.value = false;
        roleToDelete.value = null;
    }
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
                    <Heading variant="small" title="Roles" description="Manage application roles" />
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

                <FormErrors :errors="page.props.errors as Record<string, string>" />

                <SearchInput
                    v-model="searchQuery"
                    placeholder="Search roles by name..."
                    :show-clear="!!filters?.search"
                    @search="handleSearch"
                    @clear="clearSearch"
                />

                <div class="overflow-x-auto rounded-lg border">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-semibold">Name</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Permissions</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Users</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Created</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="role in roles.data" :key="role.id" class="border-b last:border-b-0">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ role.name }}</span>
                                        <StatusBadge v-if="role.is_super_admin" label="System" variant="danger" />
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span v-if="role.is_super_admin" class="text-sm text-muted-foreground italic">
                                        All (auto-granted)
                                    </span>
                                    <div v-else-if="role.permissions.length > 0" class="flex flex-wrap gap-1">
                                        <StatusBadge
                                            v-for="permission in role.permissions"
                                            :key="permission"
                                            :label="permission"
                                            variant="purple"
                                        />
                                    </div>
                                    <span v-else class="text-sm text-muted-foreground">None</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ role.users_count }}
                                </td>
                                <td class="px-4 py-3 text-sm text-muted-foreground">
                                    {{ formatDate(role.created_at) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <Link
                                            v-if="!role.is_super_admin"
                                            :href="edit(role.name).url"
                                            class="text-sm text-primary hover:underline"
                                        >
                                            Edit
                                        </Link>
                                        <span v-else class="text-sm text-muted-foreground" title="Super-admin role cannot be edited">
                                            Edit
                                        </span>
                                        <button
                                            type="button"
                                            :disabled="role.is_super_admin"
                                            :class="{
                                                'text-sm text-destructive hover:underline': !role.is_super_admin,
                                                'cursor-not-allowed text-sm text-muted-foreground': role.is_super_admin,
                                            }"
                                            @click="deleteRole(role.name, role.is_super_admin ?? false)"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="roles.data.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">No roles found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :pagination="roles" />
            </div>
        </div>

        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Are you sure?</DialogTitle>
                    <DialogDescription>
                        This will permanently delete the role "{{ roleToDelete?.name }}". This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Delete</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
