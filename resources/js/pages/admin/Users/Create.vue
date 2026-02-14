<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import CheckboxGroup from '@/components/CheckboxGroup.vue';
import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { index, store } from '@/routes/admin/users';
import { type BreadcrumbItem } from '@/types';

interface CreateUserPageProps {
    roles: string[];
}

defineProps<CreateUserPageProps>();

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
        title: 'Create',
        href: '#',
    },
];

const selectedRoles = ref<string[]>([]);
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create User" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Create New User"
                    description="Add a new user to the system"
                />

                <Form
                    :action="store().url"
                    method="post"
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
                            placeholder="user@example.com"
                        />
                    </FormField>

                    <FormField
                        label="Password"
                        id="password"
                        :error="errors.password"
                        required
                    >
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            required
                            placeholder="Minimum 8 characters"
                        />
                    </FormField>

                    <FormField
                        label="Confirm Password"
                        id="password_confirmation"
                        :error="errors.password_confirmation"
                        required
                    >
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            placeholder="Confirm password"
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

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Create User
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
