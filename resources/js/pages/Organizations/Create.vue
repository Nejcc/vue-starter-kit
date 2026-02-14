<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';

import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Organizations', href: '/organizations' },
    { title: 'Create', href: '#' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Organization" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Create Organization"
                    description="Create a new organization to collaborate with others"
                />

                <Form
                    action="/organizations"
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
                            placeholder="Acme Corporation"
                        />
                    </FormField>

                    <FormField
                        label="Slug"
                        id="slug"
                        :error="errors.slug"
                        description="Leave blank to auto-generate from name."
                    >
                        <Input
                            id="slug"
                            name="slug"
                            type="text"
                            placeholder="acme-corporation"
                        />
                    </FormField>

                    <FormField
                        label="Description"
                        id="description"
                        :error="errors.description"
                    >
                        <Textarea
                            id="description"
                            name="description"
                            placeholder="A brief description of the organization..."
                            rows="3"
                        />
                    </FormField>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Create Organization
                        </Button>
                        <Link
                            href="/organizations"
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
    </AppLayout>
</template>
