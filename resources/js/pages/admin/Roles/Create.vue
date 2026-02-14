<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import CheckboxGroup from '@/components/CheckboxGroup.vue';
import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { index, store } from '@/routes/admin/roles';
import { type BreadcrumbItem } from '@/types';

interface CreateRolePageProps {
    permissions: string[];
}

defineProps<CreateRolePageProps>();

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
        title: 'Create',
        href: '#',
    },
];

const selectedPermissions = ref<string[]>([]);
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Role" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Create New Role"
                    description="Add a new role to the system"
                />

                <Form
                    :action="store().url"
                    method="post"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <FormField
                        label="Role Name"
                        id="name"
                        :error="errors.name"
                        description="Use lowercase with hyphens (e.g., content-editor)"
                        required
                    >
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            required
                            placeholder="e.g., editor, moderator"
                        />
                    </FormField>

                    <CheckboxGroup
                        v-if="permissions.length > 0"
                        v-model="selectedPermissions"
                        :options="permissions"
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
                            Create Role
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
                                Created.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </div>
    </AdminLayout>
</template>
