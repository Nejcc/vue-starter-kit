<script setup lang="ts">
import { create, destroy, edit, index } from '@/routes/admin/settings';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Setting {
    id: number;
    key: string;
    value: string | null;
    field_type: 'input' | 'checkbox' | 'multioptions';
    options: string | null;
    label: string;
    description: string | null;
    role: 'system' | 'user' | 'plugin';
}

interface AdminSettingsPageProps {
    settings: Setting[];
    status?: string;
    filters?: {
        search?: string;
    };
}

const props = defineProps<AdminSettingsPageProps>();

const searchQuery = ref(props.filters?.search ?? '');

const debouncedSearch = useDebounceFn((query: string) => {
    router.get(
        index().url,
        { search: query || null },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}, 300);

const handleSearch = (): void => {
    debouncedSearch(searchQuery.value);
};

const performSearch = (): void => {
    debouncedSearch(searchQuery.value);
};

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Settings',
        href: index().url,
    },
];

const getDisplayValue = (setting: Setting): string => {
    if (setting.field_type === 'checkbox') {
        return setting.value === '1' ||
            setting.value === 'true' ||
            setting.value === true
            ? 'Enabled'
            : 'Disabled';
    }

    if (setting.field_type === 'multioptions' && setting.options) {
        const options = setting.options
            .split(',')
            .map((opt) => opt.trim())
            .filter(Boolean);
        const currentValue = setting.value?.toString() ?? '';
        return options.includes(currentValue)
            ? currentValue
            : (setting.value?.toString() ?? 'N/A');
    }

    return setting.value?.toString() ?? 'N/A';
};

const deleteSetting = (
    settingId: number,
    settingKey: string,
    role: string,
): void => {
    if (role === 'system') {
        alert('System settings cannot be deleted.');
        return;
    }

    if (
        confirm(`Are you sure you want to delete the setting "${settingKey}"?`)
    ) {
        router.delete(destroy(settingId).url);
    }
};
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Admin Settings" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading variant="small"
                        title="Application Settings"
                        description="Manage all application settings"
                    />
                    <Link
                        :href="create().url"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                    >
                        Create New Setting
                    </Link>
                </div>

                <div
                    v-if="status"
                    class="rounded-md bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-400"
                >
                    {{ status }}
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex-1">
                        <Input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search settings by key, label, description, or value..."
                            class="w-full"
                            @input="handleSearch"
                        />
                    </div>
                    <Button
                        v-if="filters?.search"
                        variant="outline"
                        @click="
                            router.get(
                                index().url,
                                {},
                                { preserveState: false },
                            )
                        "
                    >
                        Clear
                    </Button>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="setting in settings"
                        :key="setting.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-medium">
                                        {{ setting.label }}
                                    </h3>
                                    <span class="text-xs text-muted-foreground"
                                        >({{ setting.key }})</span
                                    >
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <span
                                                    :class="{
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400':
                                                            setting.role ===
                                                            'system',
                                                        'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400':
                                                            setting.role ===
                                                            'user',
                                                        'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400':
                                                            setting.role ===
                                                            'plugin',
                                                    }"
                                                    class="cursor-help rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                                >
                                                    {{ setting.role }}
                                                </span>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p
                                                    v-if="
                                                        setting.role ===
                                                        'system'
                                                    "
                                                >
                                                    Core application settings
                                                    (cannot be deleted)
                                                </p>
                                                <p
                                                    v-else-if="
                                                        setting.role === 'user'
                                                    "
                                                >
                                                    User-configurable settings
                                                </p>
                                                <p v-else>
                                                    Plugin-specific settings
                                                </p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </div>
                                <p
                                    v-if="setting.description"
                                    class="text-sm text-muted-foreground"
                                >
                                    {{ setting.description }}
                                </p>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-muted-foreground"
                                        >Value:</span
                                    >
                                    <span class="text-sm">{{
                                        getDisplayValue(setting)
                                    }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Link
                                                :href="edit(setting.id).url"
                                                class="text-sm text-primary hover:underline"
                                            >
                                                Edit
                                            </Link>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>
                                                Modify this setting's value and
                                                configuration
                                            </p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <button
                                                type="button"
                                                :disabled="
                                                    setting.role === 'system'
                                                "
                                                @click="
                                                    deleteSetting(
                                                        setting.id,
                                                        setting.key,
                                                        setting.role,
                                                    )
                                                "
                                                :class="{
                                                    'text-sm text-destructive hover:underline':
                                                        setting.role !==
                                                        'system',
                                                    'cursor-not-allowed text-sm text-muted-foreground':
                                                        setting.role ===
                                                        'system',
                                                }"
                                            >
                                                Delete
                                            </button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p v-if="setting.role === 'system'">
                                                System settings cannot be
                                                deleted
                                            </p>
                                            <p v-else>
                                                Permanently remove this setting
                                            </p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
