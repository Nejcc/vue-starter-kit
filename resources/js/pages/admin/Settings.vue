<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Pencil, Save, Settings2 } from 'lucide-vue-next';
import { ref, reactive, computed } from 'vue';

import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { useSettingsNav } from '@/composables/useSettingsNav';
import { type BreadcrumbItem } from '@/types';

const { title: moduleTitle, icon: moduleIcon, items: moduleItems } = useSettingsNav();

interface Setting {
    id: number;
    key: string;
    value: string | null;
    field_type: 'input' | 'checkbox' | 'multioptions';
    options: string[] | string | null;
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
const isSaving = ref(false);

// Track modified values for bulk update
const modifiedValues = reactive<Record<string, string | null>>({});

// Initialize with current values
props.settings.forEach((setting) => {
    modifiedValues[setting.key] = setting.value;
});

const hasChanges = computed(() => {
    return props.settings.some(
        (setting) => modifiedValues[setting.key] !== setting.value,
    );
});

// Modal state
const editModalOpen = ref(false);
const editingSetting = ref<Setting | null>(null);
const editFormValue = ref<string | null>('');

const openEditModal = (setting: Setting): void => {
    editingSetting.value = setting;
    editFormValue.value = modifiedValues[setting.key];
    editModalOpen.value = true;
};

const closeEditModal = (): void => {
    editModalOpen.value = false;
    editingSetting.value = null;
};

const saveEditModal = (): void => {
    if (!editingSetting.value) return;

    // Apply value change to inline tracking
    modifiedValues[editingSetting.value.key] = editFormValue.value;
    closeEditModal();
};

const debouncedSearch = useDebounceFn((query: string) => {
    router.get(
        '/admin/settings',
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

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Settings',
        href: '/admin/settings',
    },
];

const getOptions = (setting: Setting): string[] => {
    if (!setting.options) return [];
    if (Array.isArray(setting.options)) return setting.options;
    try {
        const parsed = JSON.parse(setting.options);
        if (Array.isArray(parsed)) return parsed;
    } catch {
        // not JSON, try comma-separated
    }
    return setting.options
        .split(',')
        .map((opt: string) => opt.trim())
        .filter(Boolean);
};

const isChecked = (key: string): boolean => {
    return modifiedValues[key] === '1' || modifiedValues[key] === 'true';
};

const saveChanges = (): void => {
    const changes: Record<string, string | null> = {};
    props.settings.forEach((setting) => {
        if (modifiedValues[setting.key] !== setting.value) {
            changes[setting.key] = modifiedValues[setting.key];
        }
    });

    if (Object.keys(changes).length === 0) return;

    isSaving.value = true;
    router.patch(
        '/admin/settings/bulk',
        { settings: changes },
        {
            preserveScroll: true,
            onFinish: () => {
                isSaving.value = false;
            },
        },
    );
};

const deleteSetting = (
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
        router.delete(`/admin/settings/${settingKey}`);
    }
};
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Admin Settings" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Application Settings"
                        description="Manage all application settings"
                    />
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="hasChanges"
                            @click="saveChanges"
                            :disabled="isSaving"
                            class="gap-2"
                        >
                            <Save class="h-4 w-4" />
                            {{ isSaving ? 'Saving...' : 'Save Changes' }}
                        </Button>
                        <Link
                            href="/admin/settings/create"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                        >
                            Create New Setting
                        </Link>
                    </div>
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
                                '/admin/settings',
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
                        :class="{
                            'border-primary/30 bg-primary/5':
                                modifiedValues[setting.key] !== setting.value,
                        }"
                    >
                        <div class="flex items-start justify-between gap-4">
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

                                <!-- Current value display -->
                                <div class="pt-1">
                                    <span
                                        v-if="setting.field_type === 'checkbox'"
                                        :class="[
                                            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                            isChecked(setting.key)
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
                                        ]"
                                    >
                                        {{ isChecked(setting.key) ? 'Enabled' : 'Disabled' }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-sm text-foreground"
                                    >
                                        {{ modifiedValues[setting.key] ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <button
                                                type="button"
                                                @click="openEditModal(setting)"
                                                class="inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                            >
                                                <Pencil class="h-4 w-4" />
                                                Edit
                                            </button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>
                                                Edit label, description, and
                                                value
                                            </p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <Link
                                                :href="`/admin/settings/${setting.key}/edit`"
                                                class="inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                            >
                                                <Settings2 class="h-4 w-4" />
                                                Configure
                                            </Link>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>
                                                Edit key, label, field type,
                                                role, and other configuration
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

                <!-- Floating save bar -->
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="translate-y-4 opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="translate-y-4 opacity-0"
                >
                    <div
                        v-if="hasChanges"
                        class="fixed right-0 bottom-0 left-0 z-50 border-t bg-background/95 px-6 py-3 shadow-lg backdrop-blur supports-[backdrop-filter]:bg-background/60"
                    >
                        <div
                            class="container mx-auto flex items-center justify-between"
                        >
                            <p class="text-sm text-muted-foreground">
                                You have unsaved changes
                            </p>
                            <div class="flex items-center gap-3">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="
                                        settings.forEach(
                                            (s) =>
                                                (modifiedValues[s.key] =
                                                    s.value),
                                        )
                                    "
                                >
                                    Discard
                                </Button>
                                <Button
                                    size="sm"
                                    @click="saveChanges"
                                    :disabled="isSaving"
                                    class="gap-2"
                                >
                                    <Save class="h-4 w-4" />
                                    {{ isSaving ? 'Saving...' : 'Save Changes' }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Edit Setting Modal -->
        <Dialog v-model:open="editModalOpen">
            <DialogContent
                v-if="editingSetting"
                class="sm:max-w-lg"
            >
                <DialogHeader>
                    <DialogTitle>{{ editingSetting.label }}</DialogTitle>
                    <DialogDescription>
                        <span v-if="editingSetting.description">
                            {{ editingSetting.description }}
                        </span>
                        <span v-else>
                            Update the value for this setting
                        </span>
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                        <code class="rounded bg-muted px-1.5 py-0.5">{{
                            editingSetting.key
                        }}</code>
                        <span
                            :class="{
                                'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400':
                                    editingSetting.role === 'system',
                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400':
                                    editingSetting.role === 'user',
                                'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400':
                                    editingSetting.role === 'plugin',
                            }"
                            class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                        >
                            {{ editingSetting.role }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        <Label for="edit-value">Value</Label>

                        <!-- Checkbox toggle in modal -->
                        <div
                            v-if="editingSetting.field_type === 'checkbox'"
                            class="flex items-center gap-3 pt-1"
                        >
                            <button
                                type="button"
                                @click="
                                    editFormValue =
                                        editFormValue === '1' ||
                                        editFormValue === 'true'
                                            ? '0'
                                            : '1'
                                "
                                :class="[
                                    'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:outline-none',
                                    editFormValue === '1' ||
                                    editFormValue === 'true'
                                        ? 'bg-primary'
                                        : 'bg-muted',
                                ]"
                                role="switch"
                                :aria-checked="
                                    editFormValue === '1' ||
                                    editFormValue === 'true'
                                "
                            >
                                <span
                                    :class="[
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                        editFormValue === '1' ||
                                        editFormValue === 'true'
                                            ? 'translate-x-5'
                                            : 'translate-x-0',
                                    ]"
                                />
                            </button>
                            <span class="text-sm">
                                {{
                                    editFormValue === '1' ||
                                    editFormValue === 'true'
                                        ? 'Enabled'
                                        : 'Disabled'
                                }}
                            </span>
                        </div>

                        <!-- Multi-options select in modal -->
                        <select
                            v-else-if="
                                editingSetting.field_type === 'multioptions'
                            "
                            v-model="editFormValue"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            <option
                                v-for="option in getOptions(editingSetting)"
                                :key="option"
                                :value="option"
                            >
                                {{ option }}
                            </option>
                        </select>

                        <!-- Text input in modal -->
                        <Input
                            v-else
                            id="edit-value"
                            v-model="editFormValue"
                            type="text"
                            placeholder="Setting value"
                        />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="closeEditModal">
                        Cancel
                    </Button>
                    <Button @click="saveEditModal"> Apply </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ModuleLayout>
</template>
