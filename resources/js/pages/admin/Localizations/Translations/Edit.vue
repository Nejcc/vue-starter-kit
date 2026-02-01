<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { useLocalizationNav } from '@/composables/useLocalizationNav';
import { type BreadcrumbItem } from '@/types';

const { title: moduleTitle, icon: moduleIcon, items: moduleItems } = useLocalizationNav();

interface Language {
    id: number;
    code: string;
    name: string;
}

interface Translation {
    id: number;
    language_id: number;
    group: string;
    key: string;
    value: string;
    language: Language;
}

interface EditTranslationPageProps {
    translation: Translation;
    languages: Language[];
    groups: string[];
}

const props = defineProps<EditTranslationPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Localization', href: '#' },
    { title: 'Translations', href: '/admin/localizations/translations' },
    { title: 'Edit', href: '#' },
];

const formData = ref({
    language_id: props.translation.language_id,
    group: props.translation.group,
    key: props.translation.key,
    value: props.translation.value,
});

const deleteTranslation = (): void => {
    if (
        confirm(
            `Are you sure you want to delete the translation "${props.translation.key}"?`,
        )
    ) {
        router.delete(
            `/admin/localizations/translations/${props.translation.id}`,
        );
    }
};
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Edit Translation" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <button
                    @click="() => window.history.back()"
                    class="flex w-fit cursor-pointer items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Translations
                </button>
                <div class="flex items-center justify-between">
                    <div>
                        <Heading
                            variant="small"
                            title="Edit Translation"
                            description="Update translation details"
                        />
                        <div class="mt-2 flex gap-2">
                            <StatusBadge
                                :label="translation.language.code"
                                variant="purple"
                            />
                            <StatusBadge
                                :label="translation.group"
                                variant="info"
                            />
                        </div>
                    </div>
                    <Button variant="destructive" @click="deleteTranslation">
                        Delete Translation
                    </Button>
                </div>

                <Form
                    :action="`/admin/localizations/translations/${translation.id}`"
                    method="patch"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                    :data="formData"
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
                        required
                    >
                        <Input
                            id="group"
                            v-model="formData.group"
                            name="group"
                            type="text"
                            required
                        />
                    </FormField>

                    <FormField
                        label="Key"
                        id="key"
                        :error="errors.key"
                        required
                    >
                        <Input
                            id="key"
                            v-model="formData.key"
                            name="key"
                            type="text"
                            required
                        />
                    </FormField>

                    <FormField
                        label="Value"
                        id="value"
                        :error="errors.value"
                        required
                    >
                        <textarea
                            id="value"
                            v-model="formData.value"
                            name="value"
                            required
                            rows="4"
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm ring-offset-background transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        />
                    </FormField>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Update Translation
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
                                Updated.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </div>
    </ModuleLayout>
</template>
