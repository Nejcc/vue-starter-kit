<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Settings2 } from 'lucide-vue-next';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchInput from '@/components/SearchInput.vue';
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
import { useSearch } from '@/composables/useSearch';
import { useSettingsNav } from '@/composables/useSettingsNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem, type PaginatedResponse } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useSettingsNav();

interface Setting {
    id: number;
    key: string;
    value: string | null;
    field_type: 'input' | 'checkbox' | 'multioptions';
    options: string[] | string | null;
    label: string;
    description: string | null;
    role: 'system' | 'user' | 'plugin';
    group: string | null;
}

interface GroupOption {
    value: string;
    label: string;
}

interface AdminSettingsPageProps {
    settings: PaginatedResponse<Setting>;
    groups: GroupOption[];
    currentGroup?: GroupOption;
    status?: string;
    filters?: {
        search?: string;
    };
}

const props = defineProps<AdminSettingsPageProps>();

const pageTitle = props.currentGroup
    ? `${props.currentGroup.label} Settings`
    : 'Application Settings';

const pageDescription = props.currentGroup
    ? `Manage ${props.currentGroup.label.toLowerCase()} settings`
    : 'Manage all application settings';

const searchUrl = props.currentGroup
    ? `/admin/settings/group/${props.currentGroup.value}`
    : '/admin/settings';

const { searchQuery, handleSearch, clearSearch } = useSearch({
    url: searchUrl,
});
searchQuery.value = props.filters?.search ?? '';

// Modal state for inline edit
const editModalOpen = ref(false);
const editingSetting = ref<Setting | null>(null);
const editFormValue = ref<string | null>('');
const isSavingModal = ref(false);

const openEditModal = (setting: Setting): void => {
    editingSetting.value = setting;
    editFormValue.value = setting.value;
    editModalOpen.value = true;
};

const closeEditModal = (): void => {
    editModalOpen.value = false;
    editingSetting.value = null;
};

const saveEditModal = (): void => {
    if (!editingSetting.value) return;

    isSavingModal.value = true;
    router.patch(
        '/admin/settings/bulk',
        { settings: { [editingSetting.value.key]: editFormValue.value } },
        {
            preserveScroll: true,
            onFinish: () => {
                isSavingModal.value = false;
                closeEditModal();
            },
        },
    );
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Settings', href: '/admin/settings' },
    ...(props.currentGroup
        ? [{ title: props.currentGroup.label, href: '#' }]
        : []),
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

const isChecked = (value: string | null): boolean => {
    return value === '1' || value === 'true';
};

const truncateValue = (value: string | null, maxLength = 40): string => {
    if (!value) return 'N/A';
    return value.length > maxLength
        ? value.substring(0, maxLength) + '...'
        : value;
};

const deleteSetting = (settingKey: string, role: string): void => {
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
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head :title="pageTitle" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        :title="pageTitle"
                        :description="pageDescription"
                    />
                    <Link
                        href="/admin/settings/create"
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

                <SearchInput
                    v-model="searchQuery"
                    placeholder="Search settings by key, label, description, or value..."
                    :show-clear="!!filters?.search"
                    @search="handleSearch"
                    @clear="clearSearch"
                />

                <div class="overflow-x-auto rounded-lg border">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Label
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Key
                                </th>
                                <th
                                    v-if="!currentGroup"
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Group
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-semibold"
                                >
                                    Value
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-sm font-semibold"
                                >
                                    Role
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-sm font-semibold"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="setting in settings.data"
                                :key="setting.id"
                                class="border-b last:border-b-0"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ setting.label }}
                                </td>
                                <td class="px-4 py-3">
                                    <code
                                        class="rounded bg-muted px-1.5 py-0.5 text-xs"
                                        >{{ setting.key }}</code
                                    >
                                </td>
                                <td
                                    v-if="!currentGroup"
                                    class="px-4 py-3 text-sm text-muted-foreground"
                                >
                                    <Link
                                        v-if="setting.group"
                                        :href="`/admin/settings/group/${setting.group}`"
                                        class="capitalize hover:underline"
                                    >
                                        {{ setting.group }}
                                    </Link>
                                    <span v-else>&mdash;</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        v-if="setting.field_type === 'checkbox'"
                                        :class="[
                                            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                            isChecked(setting.value)
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
                                        ]"
                                    >
                                        {{
                                            isChecked(setting.value)
                                                ? 'Enabled'
                                                : 'Disabled'
                                        }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-sm text-foreground"
                                    >
                                        {{ truncateValue(setting.value) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
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
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <button
                                                        type="button"
                                                        @click="
                                                            openEditModal(
                                                                setting,
                                                            )
                                                        "
                                                        class="inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                                    >
                                                        <Pencil
                                                            class="h-3.5 w-3.5"
                                                        />
                                                    </button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <p>Quick edit value</p>
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
                                                        <Settings2
                                                            class="h-3.5 w-3.5"
                                                        />
                                                    </Link>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <p>Configure setting</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
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
                                                'text-xs text-destructive hover:underline':
                                                    setting.role !== 'system',
                                                'cursor-not-allowed text-xs text-muted-foreground/50':
                                                    setting.role === 'system',
                                            }"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="settings.data.length === 0">
                                <td
                                    :colspan="currentGroup ? 5 : 6"
                                    class="px-4 py-8 text-center text-muted-foreground"
                                >
                                    No settings found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination :pagination="settings" />
            </div>
        </div>

        <!-- Edit Setting Modal -->
        <Dialog v-model:open="editModalOpen">
            <DialogContent v-if="editingSetting" class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{ editingSetting.label }}</DialogTitle>
                    <DialogDescription>
                        <span v-if="editingSetting.description">
                            {{ editingSetting.description }}
                        </span>
                        <span v-else> Update the value for this setting </span>
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-4">
                    <div
                        class="flex items-center gap-2 text-xs text-muted-foreground"
                    >
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
                    <Button @click="saveEditModal" :disabled="isSavingModal">
                        {{ isSavingModal ? 'Saving...' : 'Save' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ModuleLayout>
</template>
