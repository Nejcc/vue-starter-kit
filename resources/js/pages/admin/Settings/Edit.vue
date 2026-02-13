<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useSettingsNav } from '@/composables/useSettingsNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const { title: moduleTitle, icon: moduleIcon, items: moduleItems } = useSettingsNav();

interface Setting {
    id: number;
    key: string;
    value: string | null;
    field_type: 'input' | 'checkbox' | 'multioptions';
    options: string | null;
    label: string | null;
    description: string | null;
    role: 'system' | 'user' | 'plugin';
}

interface EditSettingPageProps {
    setting: Setting;
}

const props = defineProps<EditSettingPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Settings',
        href: '/admin/settings',
    },
    {
        title: 'Edit',
        href: '#',
    },
];

const fieldType = ref<'input' | 'checkbox' | 'multioptions'>(
    props.setting.field_type,
);
const formData = ref({
    key: props.setting.key,
    label: props.setting.label ?? '',
    description: props.setting.description ?? '',
    field_type: props.setting.field_type,
    options: props.setting.options ?? '',
    value:
        props.setting.field_type === 'checkbox'
            ? props.setting.value === '1' ||
              props.setting.value === 'true' ||
              props.setting.value === true
            : (props.setting.value ?? ''),
    role: props.setting.role,
});

const isSystemSetting = props.setting.role === 'system';

const deleteSetting = (): void => {
    if (isSystemSetting) {
        alert('System settings cannot be deleted.');
        return;
    }

    if (
        confirm(
            `Are you sure you want to delete the setting "${props.setting.key}"?`,
        )
    ) {
        router.delete(`/admin/settings/${props.setting.key}`);
    }
};
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Edit Setting" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <button
                    @click="() => window.history.back()"
                    class="flex w-fit cursor-pointer items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Settings
                </button>
                <div class="flex items-center justify-between">
                    <div>
                        <Heading
                            variant="small"
                            title="Edit Setting"
                            description="Update setting details"
                        />
                        <div class="mt-2">
                            <span
                                :class="{
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400':
                                        isSystemSetting,
                                    'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400':
                                        formData.role === 'user',
                                    'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400':
                                        formData.role === 'plugin',
                                }"
                                class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                            >
                                {{ formData.role }}
                            </span>
                        </div>
                    </div>
                    <Button
                        variant="destructive"
                        :disabled="isSystemSetting"
                        @click="deleteSetting"
                    >
                        Delete Setting
                    </Button>
                </div>

                <Form
                    :action="`/admin/settings/${setting.key}`"
                    method="put"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                    :data="formData"
                >
                    <div class="grid gap-2">
                        <Label for="key">Setting Key</Label>
                        <Input
                            id="key"
                            v-model="formData.key"
                            name="key"
                            type="text"
                            required
                        />
                        <InputError :message="errors.key" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="label">Label</Label>
                        <Input
                            id="label"
                            v-model="formData.label"
                            name="label"
                            type="text"
                        />
                        <InputError :message="errors.label" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            v-model="formData.description"
                            name="description"
                            rows="3"
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="field_type">Field Type</Label>
                        <select
                            id="field_type"
                            v-model="fieldType"
                            name="field_type"
                            required
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            @update:model-value="
                                formData.field_type = fieldType
                            "
                        >
                            <option value="input">Input (Text)</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="multioptions">
                                Multi-options (Select)
                            </option>
                        </select>
                        <InputError :message="errors.field_type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role">Role</Label>
                        <select
                            id="role"
                            v-model="formData.role"
                            name="role"
                            :disabled="isSystemSetting"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="system">System</option>
                            <option value="user">User</option>
                            <option value="plugin">Plugin</option>
                        </select>
                        <InputError :message="errors.role" />
                        <p
                            v-if="isSystemSetting"
                            class="text-sm text-muted-foreground"
                        >
                            System settings cannot have their role changed
                        </p>
                    </div>

                    <div v-if="fieldType === 'multioptions'" class="grid gap-2">
                        <Label for="options">Options (comma-separated)</Label>
                        <Input
                            id="options"
                            v-model="formData.options"
                            name="options"
                            type="text"
                            placeholder="e.g., option1, option2, option3"
                        />
                        <InputError :message="errors.options" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="value">Value</Label>
                        <Input
                            v-if="
                                fieldType === 'input' ||
                                fieldType === 'multioptions'
                            "
                            id="value"
                            v-model="formData.value"
                            name="value"
                            type="text"
                        />
                        <div
                            v-else-if="fieldType === 'checkbox'"
                            class="flex items-center space-x-2"
                        >
                            <input
                                id="value"
                                type="hidden"
                                name="value"
                                value="0"
                            />
                            <input
                                id="value-checkbox"
                                v-model="formData.value"
                                type="checkbox"
                                name="value"
                                :value="true"
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <Label
                                for="value-checkbox"
                                class="text-sm font-medium"
                            >
                                Enabled
                            </Label>
                        </div>
                        <InputError :message="errors.value" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Update Setting
                        </Button>
                        <Link
                            href="/admin/settings"
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
