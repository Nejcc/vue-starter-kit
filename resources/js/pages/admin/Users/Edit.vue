<script setup lang="ts">
import { destroy, index, update } from '@/routes/admin/users';
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
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
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(destroy(props.user.id).url);
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
                    :action="update(user.id).url"
                    method="patch"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            required
                            :default-value="user.name"
                            placeholder="John Doe"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            required
                            :default-value="user.email"
                            placeholder="user@example.com"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">New Password</Label>
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Leave blank to keep current password"
                        />
                        <p class="text-xs text-muted-foreground">
                            Leave blank to keep the current password
                        </p>
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm New Password</Label>
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="Confirm new password"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div v-if="roles.length > 0" class="grid gap-2">
                        <Label>Roles</Label>
                        <div class="space-y-2">
                            <div
                                v-for="role in roles"
                                :key="role"
                                class="flex items-center space-x-2"
                            >
                                <input
                                    :id="`role-${role}`"
                                    v-model="selectedRoles"
                                    type="checkbox"
                                    :value="role"
                                    name="roles[]"
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <Label
                                    :for="`role-${role}`"
                                    class="text-sm font-medium"
                                >
                                    {{ role }}
                                </Label>
                            </div>
                        </div>
                        <InputError :message="errors.roles" />
                    </div>

                    <div class="rounded-lg border border-muted bg-muted/50 p-4">
                        <h4 class="text-sm font-medium">User Information</h4>
                        <dl class="mt-2 space-y-1 text-sm text-muted-foreground">
                            <div class="flex gap-2">
                                <dt>Created:</dt>
                                <dd>{{ new Date(user.created_at).toLocaleDateString() }}</dd>
                            </div>
                            <div class="flex gap-2">
                                <dt>Email verified:</dt>
                                <dd>
                                    <span
                                        v-if="user.email_verified_at"
                                        class="text-green-600 dark:text-green-400"
                                    >
                                        {{ new Date(user.email_verified_at).toLocaleDateString() }}
                                    </span>
                                    <span v-else class="text-yellow-600 dark:text-yellow-400">
                                        Not verified
                                    </span>
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
