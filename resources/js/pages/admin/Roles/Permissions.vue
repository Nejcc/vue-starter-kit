<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

import CheckboxGroup from '@/components/CheckboxGroup.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { edit, index } from '@/routes/admin/roles';
import { update } from '@/routes/admin/roles/permissions';
import { type BreadcrumbItem } from '@/types';

interface RolePermissions {
    id: number;
    name: string;
    is_super_admin: boolean;
    permissions: string[];
    users_count: number;
}

interface PermissionsPageProps {
    role: RolePermissions;
    allPermissions: string[];
}

const props = defineProps<PermissionsPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Roles',
        href: index().url,
    },
    {
        title: props.role.name,
        href: edit(props.role.name).url,
    },
    {
        title: 'Permissions',
        href: '#',
    },
];

const selectedPermissions = ref<string[]>(props.role.permissions);
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Permissions - ${role.name}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Link
                    :href="edit(role.name).url"
                    class="flex w-fit items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Edit Role
                </Link>

                <div>
                    <Heading
                        variant="small"
                        title="Manage Permissions"
                        :description="`Permissions for role: ${role.name}`"
                    />
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ role.users_count }} user(s) assigned to this role.
                    </p>
                </div>

                <div
                    v-if="role.is_super_admin"
                    class="rounded-lg border border-muted bg-muted/50 p-4"
                >
                    <div class="flex items-center gap-2">
                        <StatusBadge label="System Role" variant="danger" />
                    </div>
                    <p class="mt-2 text-sm text-muted-foreground italic">
                        The super-admin role has all permissions automatically granted. Permissions cannot be modified.
                    </p>
                </div>

                <template v-else>
                    <Form
                        :action="update(role.name).url"
                        method="patch"
                        class="space-y-6"
                        v-slot="{ errors, processing, recentlySuccessful }"
                        :data="{ permissions: selectedPermissions }"
                    >
                        <CheckboxGroup
                            v-if="allPermissions.length > 0"
                            v-model="selectedPermissions"
                            :options="allPermissions"
                            name="permissions"
                            label="Permissions"
                            :error="errors.permissions"
                        />

                        <div
                            v-else
                            class="rounded-md border p-4 text-sm text-muted-foreground"
                        >
                            No permissions available. Create permissions first.
                        </div>

                        <div class="flex items-center gap-4">
                            <Button :disabled="processing" type="submit">
                                Update Permissions
                            </Button>
                            <Link
                                :href="edit(role.name).url"
                                class="text-sm text-muted-foreground hover:underline"
                            >
                                Cancel
                            </Link>
                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-show="recentlySuccessful"
                                    class="text-sm text-neutral-600 dark:text-neutral-400"
                                >
                                    Updated.
                                </p>
                            </Transition>
                        </div>
                    </Form>
                </template>
            </div>
        </div>
    </AdminLayout>
</template>
