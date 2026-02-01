<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';

import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useLocalizationNav } from '@/composables/useLocalizationNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const { title: moduleTitle, icon: moduleIcon, items: moduleItems } = useLocalizationNav();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Localization', href: '#' },
    { title: 'Languages', href: '/admin/localizations/languages' },
    { title: 'Create', href: '#' },
];
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Add Language" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Add Language"
                    description="Add a new language to the system"
                />

                <Form
                    action="/admin/localizations/languages"
                    method="post"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <FormField
                        label="Language Code"
                        id="code"
                        :error="errors.code"
                        description="ISO 639-1 code (e.g., en, de, fr)"
                        required
                    >
                        <Input
                            id="code"
                            name="code"
                            type="text"
                            required
                            placeholder="e.g., en"
                            maxlength="10"
                        />
                    </FormField>

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
                            placeholder="e.g., English"
                        />
                    </FormField>

                    <FormField
                        label="Native Name"
                        id="native_name"
                        :error="errors.native_name"
                        required
                    >
                        <Input
                            id="native_name"
                            name="native_name"
                            type="text"
                            required
                            placeholder="e.g., English"
                        />
                    </FormField>

                    <FormField
                        label="Direction"
                        id="direction"
                        :error="errors.direction"
                        required
                    >
                        <select
                            id="direction"
                            name="direction"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm ring-offset-background transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="ltr">Left to Right (LTR)</option>
                            <option value="rtl">Right to Left (RTL)</option>
                        </select>
                    </FormField>

                    <FormField
                        label="Sort Order"
                        id="sort_order"
                        :error="errors.sort_order"
                    >
                        <Input
                            id="sort_order"
                            name="sort_order"
                            type="number"
                            min="0"
                            value="0"
                        />
                    </FormField>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Add Language
                        </Button>
                        <Link
                            href="/admin/localizations/languages"
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
    </ModuleLayout>
</template>
