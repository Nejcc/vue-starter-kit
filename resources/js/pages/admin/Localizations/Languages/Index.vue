<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

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
import { useSearch } from '@/composables/useSearch';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { useLocalizationNav } from '@/composables/useLocalizationNav';
import { type BreadcrumbItem, type PaginatedResponse } from '@/types';

const { title: moduleTitle, icon: moduleIcon, items: moduleItems } = useLocalizationNav();

const page = usePage();

interface Language {
    id: number;
    code: string;
    name: string;
    native_name: string;
    direction: string;
    is_default: boolean;
    is_active: boolean;
    sort_order: number;
    created_at: string;
}

interface LanguagesPageProps {
    languages: PaginatedResponse<Language>;
    status?: string;
    filters?: {
        search?: string;
    };
}

const props = defineProps<LanguagesPageProps>();

const { searchQuery, handleSearch, clearSearch } = useSearch({
    url: '/admin/localizations/languages',
});
searchQuery.value = props.filters?.search ?? '';

const showDeleteDialog = ref(false);
const languageToDelete = ref<Language | null>(null);

const deleteLanguage = (language: Language): void => {
    if (language.is_default) {
        alert('The default language cannot be deleted.');
        return;
    }
    languageToDelete.value = language;
    showDeleteDialog.value = true;
};

const confirmDelete = (): void => {
    if (languageToDelete.value) {
        router.delete(
            `/admin/localizations/languages/${languageToDelete.value.id}`,
        );
        showDeleteDialog.value = false;
        languageToDelete.value = null;
    }
};

const setDefault = (language: Language): void => {
    router.post(`/admin/localizations/languages/${language.id}/set-default`);
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Localization', href: '#' },
    { title: 'Languages', href: '/admin/localizations/languages' },
];
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Languages" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Languages"
                        description="Manage available languages"
                    />
                    <Link
                        href="/admin/localizations/languages/create"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        Add Language
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

                <SearchInput
                    v-model="searchQuery"
                    placeholder="Search languages..."
                    :show-clear="!!filters?.search"
                    @search="handleSearch"
                    @clear="clearSearch"
                />

                <div class="space-y-4">
                    <DataCard
                        v-for="language in languages.data"
                        :key="language.id"
                    >
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-medium">
                                {{ language.name }}
                            </h3>
                            <StatusBadge
                                :label="language.code"
                                variant="info"
                            />
                            <StatusBadge
                                v-if="language.is_default"
                                label="Default"
                                variant="success"
                            />
                            <StatusBadge
                                v-if="!language.is_active"
                                label="Inactive"
                                variant="warning"
                            />
                            <StatusBadge
                                v-if="language.direction === 'rtl'"
                                label="RTL"
                                variant="purple"
                            />
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Native: {{ language.native_name }}
                        </p>
                        <template #actions>
                            <button
                                v-if="!language.is_default"
                                type="button"
                                class="text-sm text-primary hover:underline"
                                @click="setDefault(language)"
                            >
                                Set Default
                            </button>
                            <Link
                                :href="`/admin/localizations/languages/${language.id}/edit`"
                                class="text-sm text-primary hover:underline"
                            >
                                Edit
                            </Link>
                            <button
                                type="button"
                                :disabled="language.is_default"
                                @click="deleteLanguage(language)"
                                :class="{
                                    'text-sm text-destructive hover:underline':
                                        !language.is_default,
                                    'cursor-not-allowed text-sm text-muted-foreground':
                                        language.is_default,
                                }"
                            >
                                Delete
                            </button>
                        </template>
                    </DataCard>
                </div>

                <div
                    v-if="languages.data.length === 0"
                    class="rounded-lg border p-8 text-center"
                >
                    <p class="text-muted-foreground">No languages found.</p>
                </div>

                <Pagination :pagination="languages" />
            </div>
        </div>

        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Are you sure?</DialogTitle>
                    <DialogDescription>
                        This will permanently delete the language "{{
                            languageToDelete?.name
                        }}" and all its translations. This action cannot be
                        undone.
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
