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
    native_name: string;
    direction: string;
    is_default: boolean;
    is_active: boolean;
    sort_order: number;
}

interface EditLanguagePageProps {
    language: Language;
}

const props = defineProps<EditLanguagePageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Localization', href: '#' },
    { title: 'Languages', href: '/admin/localizations/languages' },
    { title: 'Edit', href: '#' },
];

const formData = ref({
    code: props.language.code,
    name: props.language.name,
    native_name: props.language.native_name,
    direction: props.language.direction,
    is_active: props.language.is_active,
    sort_order: props.language.sort_order,
});

const deleteLanguage = (): void => {
    if (props.language.is_default) {
        alert('The default language cannot be deleted.');
        return;
    }

    if (
        confirm(
            `Are you sure you want to delete the language "${props.language.name}"?`,
        )
    ) {
        router.delete(`/admin/localizations/languages/${props.language.id}`);
    }
};
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Edit Language" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <button
                    @click="() => window.history.back()"
                    class="flex w-fit cursor-pointer items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Languages
                </button>
                <div class="flex items-center justify-between">
                    <div>
                        <Heading
                            variant="small"
                            title="Edit Language"
                            description="Update language details"
                        />
                        <div v-if="language.is_default" class="mt-2">
                            <StatusBadge
                                label="Default Language"
                                variant="success"
                            />
                        </div>
                    </div>
                    <Button
                        v-if="!language.is_default"
                        variant="destructive"
                        @click="deleteLanguage"
                    >
                        Delete Language
                    </Button>
                </div>

                <Form
                    :action="`/admin/localizations/languages/${language.id}`"
                    method="patch"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                    :data="formData"
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
                            v-model="formData.code"
                            name="code"
                            type="text"
                            required
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
                            v-model="formData.name"
                            name="name"
                            type="text"
                            required
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
                            v-model="formData.native_name"
                            name="native_name"
                            type="text"
                            required
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
                            v-model="formData.direction"
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
                            v-model.number="formData.sort_order"
                            name="sort_order"
                            type="number"
                            min="0"
                        />
                    </FormField>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Update Language
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
                                Updated.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </div>
    </ModuleLayout>
</template>
