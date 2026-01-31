<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
                    <div class="grid gap-2">
                        <Label for="name">Permission Name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            required
                            placeholder="e.g., edit posts, delete users"
                        />
                        <InputError :message="errors.name" />
                        <p class="text-sm text-muted-foreground">
                            Use lowercase with spaces (e.g., edit posts, delete
                            users)
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="group_name">Group Name</Label>
                        <Input
                            id="group_name"
                            name="group_name"
                            type="text"
                            placeholder="e.g., users, posts, settings"
                        />
                        <InputError :message="errors.group_name" />
                        <p class="text-sm text-muted-foreground">
                            Optional: Group permissions together (e.g., "users"
                            for view users, create users, etc.)
                        </p>
                    </div>

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
