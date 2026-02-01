<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useLocalizationNav } from '@/composables/useLocalizationNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const { title: moduleTitle, icon: moduleIcon, items: moduleItems } = useLocalizationNav();

interface Language {
    id: number;
    code: string;
    name: string;
}

interface CreateTranslationPageProps {
    languages: Language[];
    groups: string[];
}

defineProps<CreateTranslationPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Localization', href: '#' },
    { title: 'Translations', href: '/admin/localizations/translations' },
    { title: 'Create', href: '#' },
];

const formData = ref({
    language_id: '',
    group: '',
    key: '',
    value: '',
});

const customGroup = ref('');
const useCustomGroup = ref(false);
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Add Translation" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Add Translation"
                    description="Add a new translation string"
                />

                <Form
                    action="/admin/localizations/translations"
                    method="post"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                    :data="{
                        language_id: formData.language_id,
                        group: useCustomGroup ? customGroup : formData.group,
                        key: formData.key,
                        value: formData.value,
                    }"
                >
                    <FormField
                        label="Language"
                        id="language_id"
                        :error="errors.language_id"
                        required
                    >
                        <select
                            id="language_id"
                            v-model="formData.language_id"
                            name="language_id"
                            required
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm ring-offset-background transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="" disabled>Select a language</option>
                            <option
                                v-for="lang in languages"
                                :key="lang.id"
                                :value="lang.id"
                            >
                                {{ lang.name }} ({{ lang.code }})
                            </option>
                        </select>
                    </FormField>

                    <FormField
                        label="Group"
                        id="group"
                        :error="errors.group"
                        description="Use * for JSON translations, or a group name like messages, validation, etc."
                        required
                    >
                        <div class="space-y-2">
                            <div v-if="groups.length > 0 && !useCustomGroup">
                                <select
                                    id="group"
                                    v-model="formData.group"
                                    name="group"
                                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm ring-offset-background transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="" disabled>
                                        Select a group
                                    </option>
                                    <option
                                        v-for="g in groups"
                                        :key="g"
                                        :value="g"
                                    >
                                        {{ g }}
                                    </option>
                                </select>
                            </div>
                            <div v-if="useCustomGroup">
                                <Input
                                    v-model="customGroup"
                                    placeholder="e.g., messages"
                                />
                            </div>
                            <button
                                type="button"
                                class="text-sm text-primary hover:underline"
                                @click="useCustomGroup = !useCustomGroup"
                            >
                                {{
                                    useCustomGroup
                                        ? 'Use existing group'
                                        : 'Create new group'
                                }}
                            </button>
                        </div>
                    </FormField>

                    <FormField
                        label="Key"
                        id="key"
                        :error="errors.key"
                        description="The translation key (e.g., welcome_message)"
                        required
                    >
                        <Input
                            id="key"
                            v-model="formData.key"
                            name="key"
                            type="text"
                            required
                            placeholder="e.g., welcome_message"
                        />
                    </FormField>

                    <FormField
                        label="Value"
                        id="value"
                        :error="errors.value"
                        description="The translated text. Use :placeholder for dynamic values."
                        required
                    >
                        <textarea
                            id="value"
                            v-model="formData.value"
                            name="value"
                            required
                            rows="4"
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm ring-offset-background transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="e.g., Welcome, :name!"
                        />
                    </FormField>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Add Translation
                        </Button>
                        <Link
                            href="/admin/localizations/translations"
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
