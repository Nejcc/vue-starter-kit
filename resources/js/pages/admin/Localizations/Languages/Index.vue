<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Globe, Plus, Zap } from 'lucide-vue-next';
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
import { useLocalizationNav } from '@/composables/useLocalizationNav';
import { useSearch } from '@/composables/useSearch';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem, type PaginatedResponse } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useLocalizationNav();

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
    fallbackLocale: string;
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

const seedCommon = (): void => {
    router.post('/admin/localizations/languages/seed-common');
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Localization', href: '#' },
    { title: 'Languages', href: '/admin/localizations/languages' },
];
</script>

<template>
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head title="Languages" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Languages"
                        description="Manage available languages"
                    />
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="languages.data.length === 0"
                            variant="outline"
                            @click="seedCommon"
                        >
                            <Zap class="mr-2 h-4 w-4" />
                            Quick Setup
                        </Button>
                        <Link href="/admin/localizations/languages/create">
                            <Button>
                                <Plus class="mr-2 h-4 w-4" />
                                Add Language
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Fallback locale info -->
                <div
                    class="flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800 dark:border-blue-900/50 dark:bg-blue-900/20 dark:text-blue-400"
                >
                    <Globe class="h-4 w-4 shrink-0" />
                    <span>
                        Fallback locale: <strong>{{ fallbackLocale }}</strong>
                        <span class="text-blue-600 dark:text-blue-500">
                            &mdash; used when a translation is missing in the
                            active language.</span
                        >
                    </span>
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
                                v-if="
                                    language.code === fallbackLocale &&
                                    !language.is_default
                                "
                                label="Fallback"
                                variant="purple"
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
                    v-if="languages.data.length === 0 && !filters?.search"
                    class="rounded-lg border p-8 text-center"
                >
                    <div class="mx-auto flex flex-col items-center gap-3">
                        <Globe class="h-10 w-10 text-muted-foreground" />
                        <p class="text-muted-foreground">
                            No languages configured yet.
                        </p>
                        <Button variant="outline" @click="seedCommon">
                            <Zap class="mr-2 h-4 w-4" />
                            Add Common Languages
                        </Button>
                    </div>
                </div>

                <div
                    v-else-if="languages.data.length === 0 && filters?.search"
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
