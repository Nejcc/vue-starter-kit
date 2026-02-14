<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';

import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { index, store } from '@/routes/admin/permissions';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Permissions',
        href: index().url,
    },
    {
        title: 'Create',
        href: '#',
    },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Permission" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Create New Permission"
                    description="Add a new permission to the system"
                />

                <Form
                    :action="store().url"
                    method="post"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <FormField
                        label="Permission Name"
                        id="name"
                        :error="errors.name"
                        description="Use lowercase with spaces (e.g., edit posts, delete users)"
                        required
                    >
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            required
                            placeholder="e.g., edit posts, delete users"
                        />
                    </FormField>

                    <FormField
                        label="Group Name"
                        id="group_name"
                        :error="errors.group_name"
                        description='Optional: Group permissions together (e.g., "users" for view users, create users, etc.)'
                    >
                        <Input
                            id="group_name"
                            name="group_name"
                            type="text"
                            placeholder="e.g., users, posts, settings"
                        />
                    </FormField>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Create Permission
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
