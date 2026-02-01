<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import DataCard from '@/components/DataCard.vue';
import FormErrors from '@/components/FormErrors.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useLocalizationNav } from '@/composables/useLocalizationNav';
import { useSearch } from '@/composables/useSearch';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem, type PaginatedResponse } from '@/types';

const { title: moduleTitle, icon: moduleIcon, items: moduleItems } = useLocalizationNav();

const page = usePage();

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
    created_at: string;
}

interface TranslationsPageProps {
    translations: PaginatedResponse<Translation>;
    languages: Language[];
    groups: string[];
    status?: string;
    filters?: {
        search?: string;
        language_id?: string;
        group?: string;
    };
}

const props = defineProps<TranslationsPageProps>();

const selectedLanguageId = ref(props.filters?.language_id ?? '');
const selectedGroup = ref(props.filters?.group ?? '');

const { searchQuery, handleSearch, clearSearch } = useSearch({
    url: '/admin/localizations/translations',
    extraParams: () => ({
        language_id: selectedLanguageId.value || null,
        group: selectedGroup.value || null,
    }),
});
searchQuery.value = props.filters?.search ?? '';

const applyFilter = (): void => {
    router.get(
        '/admin/localizations/translations',
        {
            search: searchQuery.value || null,
            language_id: selectedLanguageId.value || null,
            group: selectedGroup.value || null,
        },
        { preserveState: true, preserveScroll: true },
    );
};

watch([selectedLanguageId, selectedGroup], () => {
    applyFilter();
});

const showDeleteDialog = ref(false);
const translationToDelete = ref<Translation | null>(null);

const deleteTranslation = (translation: Translation): void => {
    translationToDelete.value = translation;
    showDeleteDialog.value = true;
};

const confirmDelete = (): void => {
    if (translationToDelete.value) {
        router.delete(
            `/admin/localizations/translations/${translationToDelete.value.id}`,
        );
        showDeleteDialog.value = false;
        translationToDelete.value = null;
    }
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Localization', href: '#' },
    { title: 'Translations', href: '/admin/localizations/translations' },
];
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Translations" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Translations"
                        description="Manage translation strings"
                    />
                    <Link
                        href="/admin/localizations/translations/create"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        Add Translation
                    </Link>
                </div>

                <div
                    v-if="status"
                    class="rounded-md bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-400"
                >
                    {{ status }}
                </div>

                <FormErrors
                    :errors="page.props.errors as Record<string, string>"
                />

                <div class="flex flex-col gap-4 sm:flex-row">
                    <div class="flex-1">
                        <SearchInput
                            v-model="searchQuery"
                            placeholder="Search by key or value..."
                            :show-clear="!!filters?.search"
                            @search="handleSearch"
                            @clear="clearSearch"
                        />
                    </div>
                    <select
                        v-model="selectedLanguageId"
                        class="flex h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm ring-offset-background transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        <option value="">All Languages</option>
                        <option
                            v-for="lang in languages"
                            :key="lang.id"
                            :value="lang.id"
                        >
                            {{ lang.name }} ({{ lang.code }})
                        </option>
                    </select>
                    <select
                        v-model="selectedGroup"
                        class="flex h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm ring-offset-background transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        <option value="">All Groups</option>
                        <option
                            v-for="group in groups"
                            :key="group"
                            :value="group"
                        >
                            {{ group }}
                        </option>
                    </select>
                </div>

                <div class="space-y-4">
                    <DataCard
                        v-for="translation in translations.data"
                        :key="translation.id"
                    >
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-medium">
                                {{ translation.key }}
                            </h3>
                            <StatusBadge
                                :label="translation.group"
                                variant="info"
                            />
                            <StatusBadge
                                :label="translation.language.code"
                                variant="purple"
                            />
                        </div>
                        <p class="line-clamp-2 text-sm text-muted-foreground">
                            {{ translation.value }}
                        </p>
                        <template #actions>
                            <Link
                                :href="`/admin/localizations/translations/${translation.id}/edit`"
                                class="text-sm text-primary hover:underline"
                            >
                                Edit
                            </Link>
                            <button
                                type="button"
                                class="text-sm text-destructive hover:underline"
                                @click="deleteTranslation(translation)"
                            >
                                Delete
                            </button>
                        </template>
                    </DataCard>
                </div>

                <div
                    v-if="translations.data.length === 0"
                    class="rounded-lg border p-8 text-center"
                >
                    <p class="text-muted-foreground">No translations found.</p>
                </div>

                <Pagination :pagination="translations" />
            </div>
        </div>

        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Are you sure?</DialogTitle>
                    <DialogDescription>
                        This will permanently delete the translation "{{
                            translationToDelete?.key
                        }}". This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="showDeleteDialog = false">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ModuleLayout>
</template>
