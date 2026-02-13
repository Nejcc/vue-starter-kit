<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import CheckboxGroup from '@/components/CheckboxGroup.vue';
import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useDateFormat } from '@/composables/useDateFormat';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { destroy, index, permissions, update } from '@/routes/admin/users';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    slug: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    roles: string[];
    created_at: string;
}

interface EditUserPageProps {
    user: User;
    roles: string[];
}

const props = defineProps<EditUserPageProps>();
const { formatDate } = useDateFormat();

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
        title: 'Edit',
        href: '#',
    },
];

const selectedRoles = ref<string[]>(props.user.roles);

const confirmDelete = () => {
    if (
        confirm(
            'Are you sure you want to delete this user? This action cannot be undone.',
        )
    ) {
        router.delete(destroy(props.user.slug).url);
    }
};
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Edit User - ${user.name}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Edit User"
                        :description="`Editing ${user.name}`"
                    />
                    <Button variant="destructive" @click="confirmDelete">
                        Delete User
                    </Button>
                </div>

                <Form
                    :action="update(user.slug).url"
                    method="patch"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <FormField
                        label="Name"
                        id="name"
                        :error="errors.name"
                        required
                    >
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            required
                            :default-value="user.name"
                            placeholder="John Doe"
                        />
                    </FormField>

                    <FormField
                        label="Email"
                        id="email"
                        :error="errors.email"
                        required
                    >
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            required
                            :default-value="user.email"
                            placeholder="user@example.com"
                        />
                    </FormField>

                    <FormField
                        label="New Password"
                        id="password"
                        :error="errors.password"
                        description="Leave blank to keep the current password"
                    >
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Leave blank to keep current password"
                        />
                    </FormField>

                    <FormField
                        label="Confirm New Password"
                        id="password_confirmation"
                        :error="errors.password_confirmation"
                    >
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="Confirm new password"
                        />
                    </FormField>

                    <CheckboxGroup
                        v-if="roles.length > 0"
                        v-model="selectedRoles"
                        :options="roles"
                        name="roles"
                        label="Roles"
                        :error="errors.roles"
                    />

                    <div>
                        <Link
                            :href="permissions(user.slug).url"
                            class="text-sm text-primary hover:underline"
                        >
                            Manage Direct Permissions
                        </Link>
                    </div>

                    <div class="rounded-lg border border-muted bg-muted/50 p-4">
                        <h4 class="text-sm font-medium">User Information</h4>
                        <dl
                            class="mt-2 space-y-1 text-sm text-muted-foreground"
                        >
                            <div class="flex gap-2">
                                <dt>Created:</dt>
                                <dd>{{ formatDate(user.created_at) }}</dd>
                            </div>
                            <div class="flex gap-2">
                                <dt>Email verified:</dt>
                                <dd>
                                    <StatusBadge
                                        v-if="user.email_verified_at"
                                        :label="formatDate(user.email_verified_at)"
                                        variant="success"
                                    />
                                    <StatusBadge
                                        v-else
                                        label="Not verified"
                                        variant="warning"
                                    />
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Update User
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
