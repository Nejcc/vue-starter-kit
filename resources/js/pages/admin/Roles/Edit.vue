<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

import CheckboxGroup from '@/components/CheckboxGroup.vue';
import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { destroy, index, update } from '@/routes/admin/roles';
import { type BreadcrumbItem } from '@/types';

interface Role {
    id: number;
    name: string;
    is_super_admin?: boolean;
    permissions: string[];
}

interface EditRolePageProps {
    role: Role;
    permissions: string[];
}

const props = defineProps<EditRolePageProps>();

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
        title: 'Edit',
        href: '#',
    },
];

const selectedPermissions = ref<string[]>(props.role.permissions);
const formData = ref({
    name: props.role.name,
});

const deleteRole = (): void => {
    if (props.role.is_super_admin) {
        alert(
            'The super-admin role cannot be deleted. It is a system role with all permissions.',
        );
        return;
    }

    if (
        confirm(
            `Are you sure you want to delete the role "${props.role.name}"?`,
        )
    ) {
        router.delete(destroy(props.role.name).url);
    }
};
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Role" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <button
                    @click="() => window.history.back()"
                    class="flex w-fit cursor-pointer items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Roles
                </button>
                <div class="flex items-center justify-between">
                    <div>
                        <Heading
                            variant="small"
                            title="Edit Role"
                            description="Update role details"
                        />
                        <div v-if="role.is_super_admin" class="mt-2">
                            <StatusBadge label="System Role" variant="danger" />
                            <p
                                class="mt-1 text-sm text-muted-foreground italic"
                            >
                                This role has all permissions automatically
                                granted. No permissions need to be assigned.
                            </p>
                        </div>
                    </div>
                    <Button
                        v-if="!role.is_super_admin"
                        variant="destructive"
                        @click="deleteRole"
                    >
                        Delete Role
                    </Button>
                </div>

                <Form
                    :action="update.patch(role.name).url"
                    method="patch"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                    :data="{
                        name: formData.name,
                        permissions: selectedPermissions,
                    }"
                >
                    <FormField
                        label="Role Name"
                        id="name"
                        :error="errors.name"
                        required
                    >
                        <Input
                            id="name"
                            v-model="formData.name"
                            name="name"
                            type="text"
                            required
                            :disabled="role.is_super_admin"
                        />
                        <p
                            v-if="role.is_super_admin"
                            class="text-sm text-muted-foreground"
                        >
                            The super-admin role name cannot be changed
                        </p>
                    </FormField>

                    <CheckboxGroup
                        v-if="!role.is_super_admin && permissions.length > 0"
                        v-model="selectedPermissions"
                        :options="permissions"
                        name="permissions"
                        label="Permissions"
                        :error="errors.permissions"
                    />

                    <div
                        v-else-if="role.is_super_admin"
                        class="rounded-md border p-4 text-sm text-muted-foreground"
                    >
                        Super-admin role has all permissions automatically. No
                        need to assign specific permissions.
                    </div>

                    <div
                        v-else
                        class="rounded-md border p-4 text-sm text-muted-foreground"
                    >
                        No permissions available. Create permissions first.
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Update Role
                        </Button>
                        <Link
                            :href="index().url"
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
