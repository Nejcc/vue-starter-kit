<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

import CheckboxGroup from '@/components/CheckboxGroup.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { edit, index } from '@/routes/admin/users';
import { update } from '@/routes/admin/users/permissions';
import { type BreadcrumbItem } from '@/types';

interface UserPermissions {
    id: number;
    slug: string;
    name: string;
    email: string;
    roles: string[];
    direct_permissions: string[];
    role_permissions: string[];
}

interface PermissionsPageProps {
    user: UserPermissions;
    allPermissions: string[];
}

const props = defineProps<PermissionsPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Users',
        href: index().url,
    },
    {
        title: props.user.name,
        href: edit(props.user.slug).url,
    },
    {
        title: 'Permissions',
        href: '#',
    },
];

const selectedPermissions = ref<string[]>(props.user.direct_permissions);
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Permissions - ${user.name}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Link
                    :href="edit(user.slug).url"
                    class="flex w-fit items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Edit User
                </Link>

                <Heading
                    variant="small"
                    title="Manage Permissions"
                    :description="`Direct permissions for ${user.name}`"
                />

                <div
                    v-if="user.role_permissions.length > 0"
                    class="rounded-lg border border-muted bg-muted/50 p-4"
                >
                    <h4 class="text-sm font-medium">
                        Permissions inherited via roles ({{
                            user.roles.join(', ')
                        }})
                    </h4>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <StatusBadge
                            v-for="perm in user.role_permissions"
                            :key="perm"
                            :label="perm"
                            variant="info"
                        />
                    </div>
                    <p class="mt-2 text-xs text-muted-foreground">
                        These permissions are granted through role assignments
                        and cannot be changed here.
                    </p>
                </div>

                <div
                    v-else
                    class="rounded-md border p-4 text-sm text-muted-foreground"
                >
                    No permissions inherited via roles.
                </div>

                <Form
                    :action="update(user.slug).url"
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
                        label="Direct Permissions"
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
                            :href="edit(user.slug).url"
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
            </div>
        </div>
    </AdminLayout>
</template>
