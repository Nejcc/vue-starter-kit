<script setup lang="ts">
import { index, update } from '@/routes/admin/permissions';
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ArrowLeft } from 'lucide-vue-next';

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
    // TODO: Implement destroy route and method in PermissionsController
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
                    <Heading variant="small"
                        title="Edit Permission"
                        description="Update permission details"
                    />
                    <Button variant="destructive" @click="deletePermission">
                        Delete Permission
                    </Button>
                </div>

                <Form
                    :action="update.patch(props.permission.id).url"
                    method="patch"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                    :data="formData"
                >
                    <div class="grid gap-2">
                        <Label for="name">Permission Name</Label>
                        <Input
                            id="name"
                            v-model="formData.name"
                            name="name"
                            type="text"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="group_name">Group Name</Label>
                        <Input
                            id="group_name"
                            v-model="formData.group_name"
                            name="group_name"
                            type="text"
                            placeholder="e.g., users, posts, settings"
                        />
                        <InputError :message="errors.group_name" />
                        <p class="text-sm text-muted-foreground">
                            Optional: Group permissions together
                        </p>
                    </div>

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
