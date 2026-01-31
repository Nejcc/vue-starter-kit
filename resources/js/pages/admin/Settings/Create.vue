<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Settings',
        href: '/admin/settings',
    },
    {
        title: 'Create',
        href: '#',
    },
];

const fieldType = ref<'input' | 'checkbox' | 'multioptions'>('input');
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Setting" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Create New Setting"
                    description="Add a new application setting"
                />

                <Form
                    action="/admin/settings"
                    method="post"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="key">Setting Key</Label>
                        <Input
                            id="key"
                            name="key"
                            type="text"
                            required
                            placeholder="e.g., maintenance_mode"
                        />
                        <InputError :message="errors.key" />
                        <p class="text-sm text-muted-foreground">
                            Unique identifier for this setting (lowercase,
                            underscores)
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="label">Label</Label>
                        <Input
                            id="label"
                            name="label"
                            type="text"
                            placeholder="e.g., Maintenance Mode"
                        />
                        <InputError :message="errors.label" />
                        <p class="text-sm text-muted-foreground">
                            Display name for this setting
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="Describe what this setting controls"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="field_type">Field Type</Label>
                        <select
                            id="field_type"
                            v-model="fieldType"
                            name="field_type"
                            required
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="input">Input (Text)</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="multioptions">
                                Multi-options (Select)
                            </option>
                        </select>
                        <InputError :message="errors.field_type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role">Role</Label>
                        <select
                            id="role"
                            name="role"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="user">User</option>
                            <option value="plugin">Plugin</option>
                        </select>
                        <InputError :message="errors.role" />
                        <p class="text-sm text-muted-foreground">
                            System settings can only be created programmatically
                        </p>
                    </div>

                    <div v-if="fieldType === 'multioptions'" class="grid gap-2">
                        <Label for="options">Options (comma-separated)</Label>
                        <Input
                            id="options"
                            name="options"
                            type="text"
                            placeholder="e.g., option1, option2, option3"
                        />
                        <InputError :message="errors.options" />
                        <p class="text-sm text-muted-foreground">
                            Enter options separated by commas
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="value">Default Value</Label>
                        <Input
                            v-if="
                                fieldType === 'input' ||
                                fieldType === 'multioptions'
                            "
                            id="value"
                            name="value"
                            type="text"
                            placeholder="Default value"
                        />
                        <div
                            v-else-if="fieldType === 'checkbox'"
                            class="flex items-center space-x-2"
                        >
                            <input
                                id="value"
                                type="hidden"
                                name="value"
                                value="0"
                            />
                            <input
                                id="value-checkbox"
                                type="checkbox"
                                name="value"
                                value="1"
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <Label
                                for="value-checkbox"
                                class="text-sm font-medium"
                            >
                                Enabled by default
                            </Label>
                        </div>
                        <InputError :message="errors.value" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Create Setting
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
