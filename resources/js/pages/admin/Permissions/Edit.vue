<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { index, update } from '@/routes/admin/permissions';
import { type BreadcrumbItem } from '@/types';

interface Permission {
    id: number;
    name: string;
    group_name: string | null;
}

interface EditPermissionPageProps {
    permission: Permission;
}

const props = defineProps<EditPermissionPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Permissions',
        href: index().url,
    },
    {
        title: 'Edit',
        href: '#',
    },
];

const formData = ref({
    name: props.permission.name,
    group_name: props.permission.group_name ?? '',
});

const deletePermission = (): void => {
    alert('Delete functionality is not yet implemented.');
};
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Permission" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <button
                    @click="() => window.history.back()"
                    class="flex w-fit cursor-pointer items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Permissions
                </button>
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Edit Permission"
                        description="Update permission details"
                    />
                    <Button variant="destructive" @click="deletePermission">
                        Delete Permission
                    </Button>
                </div>

                <Form
                    :action="update.patch(props.permission.name).url"
                    method="patch"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                    :data="formData"
                >
                    <FormField
                        label="Permission Name"
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
                        label="Group Name"
                        id="group_name"
                        :error="errors.group_name"
                        description="Optional: Group permissions together"
                    >
                        <Input
                            id="group_name"
                            v-model="formData.group_name"
                            name="group_name"
                            type="text"
                            placeholder="e.g., users, posts, settings"
                        />
                    </FormField>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Update Permission
                        </Button>
                        <Link
                            :href="index().url"
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
    </AdminLayout>
</template>
