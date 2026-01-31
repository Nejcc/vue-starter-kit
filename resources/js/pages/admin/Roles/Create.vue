<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { index, store } from '@/routes/admin/roles';
import { type BreadcrumbItem } from '@/types';

interface CreateRolePageProps {
    permissions: string[];
}

const props = defineProps<CreateRolePageProps>();

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
                    <div class="grid gap-2">
                        <Label for="name">Role Name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            required
                            placeholder="e.g., editor, moderator"
                        />
                        <InputError :message="errors.name" />
                        <p class="text-sm text-muted-foreground">
                            Use lowercase with hyphens (e.g., content-editor)
                        </p>
                    </div>

                    <div v-if="permissions.length > 0" class="grid gap-2">
                        <Label>Permissions</Label>
                        <div
                            class="max-h-60 space-y-2 overflow-y-auto rounded-md border p-4"
                        >
                            <div
                                v-for="permission in permissions"
                                :key="permission"
                                class="flex items-center space-x-2"
                            >
                                <input
                                    :id="`permission-${permission}`"
                                    v-model="selectedPermissions"
                                    type="checkbox"
                                    :value="permission"
                                    name="permissions[]"
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <Label
                                    :for="`permission-${permission}`"
                                    class="text-sm font-medium"
                                >
                                    {{ permission }}
                                </Label>
                            </div>
                        </div>
                        <InputError :message="errors.permissions" />
                    </div>

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
