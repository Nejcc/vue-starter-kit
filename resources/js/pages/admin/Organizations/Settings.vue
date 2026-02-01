<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useOrganizationNav } from '@/composables/useOrganizationNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface TenantSettings {
    entity_name: string;
    entity_name_plural: string;
    multi_org: boolean;
    personal_org: boolean;
    routing_mode: 'session' | 'url';
    url_prefix: string;
    invitation_expiry_hours: number;
    max_organizations_per_user: number;
    max_members_per_organization: number;
    default_member_role: string;
    member_roles: string[];
}

interface SettingsPageProps {
    settings: TenantSettings;
}

const props = defineProps<SettingsPageProps>();
const { title: moduleTitle, icon: moduleIcon, items: moduleItems } = useOrganizationNav();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Organizations', href: '/admin/organizations' },
    { title: 'Settings', href: '#' },
];

const formData = ref<TenantSettings>({
    entity_name: props.settings.entity_name,
    entity_name_plural: props.settings.entity_name_plural,
    multi_org: props.settings.multi_org,
    personal_org: props.settings.personal_org,
    routing_mode: props.settings.routing_mode,
    url_prefix: props.settings.url_prefix ?? 'org',
    invitation_expiry_hours: props.settings.invitation_expiry_hours,
    max_organizations_per_user: props.settings.max_organizations_per_user,
    max_members_per_organization: props.settings.max_members_per_organization,
    default_member_role: props.settings.default_member_role,
    member_roles: [...props.settings.member_roles],
});

const memberRolesInput = ref(props.settings.member_roles.join(', '));

watch(memberRolesInput, (value) => {
    formData.value.member_roles = value
        .split(',')
        .map((r) => r.trim())
        .filter((r) => r.length > 0);
});
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Organization Settings" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading variant="small" title="Organization Settings" description="Configure tenant and organization behavior" />

                <Form
                    action="/admin/organizations/settings"
                    method="patch"
                    class="space-y-8"
                    v-slot="{ errors, processing, recentlySuccessful }"
                    :data="formData"
                >
                    <!-- Entity Names -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h3 class="text-sm font-medium">Entity Names</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="entity_name">Singular Name</Label>
                                <Input
                                    id="entity_name"
                                    v-model="formData.entity_name"
                                    type="text"
                                    placeholder="Organization"
                                />
                                <InputError :message="errors.entity_name" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="entity_name_plural">Plural Name</Label>
                                <Input
                                    id="entity_name_plural"
                                    v-model="formData.entity_name_plural"
                                    type="text"
                                    placeholder="Organizations"
                                />
                                <InputError :message="errors.entity_name_plural" />
                            </div>
                        </div>
                    </div>

                    <!-- Organization Modes -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h3 class="text-sm font-medium">Organization Modes</h3>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    v-model="formData.multi_org"
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <div>
                                    <span class="text-sm font-medium">Multi-Organization Mode</span>
                                    <p class="text-xs text-muted-foreground">Allow users to belong to multiple organizations</p>
                                </div>
                            </label>
                            <InputError :message="errors.multi_org" />

                            <label class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    v-model="formData.personal_org"
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <div>
                                    <span class="text-sm font-medium">Personal Organization</span>
                                    <p class="text-xs text-muted-foreground">Auto-create a personal organization on user registration</p>
                                </div>
                            </label>
                            <InputError :message="errors.personal_org" />
                        </div>
                    </div>

                    <!-- Routing -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h3 class="text-sm font-medium">Routing</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="routing_mode">Routing Mode</Label>
                                <select
                                    id="routing_mode"
                                    v-model="formData.routing_mode"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                >
                                    <option value="session">Session</option>
                                    <option value="url">URL Prefix</option>
                                </select>
                                <InputError :message="errors.routing_mode" />
                            </div>
                            <div v-if="formData.routing_mode === 'url'" class="grid gap-2">
                                <Label for="url_prefix">URL Prefix</Label>
                                <Input
                                    id="url_prefix"
                                    v-model="formData.url_prefix"
                                    type="text"
                                    placeholder="org"
                                />
                                <InputError :message="errors.url_prefix" />
                            </div>
                        </div>
                    </div>

                    <!-- Invitations -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h3 class="text-sm font-medium">Invitations</h3>
                        <div class="grid gap-2 sm:max-w-xs">
                            <Label for="invitation_expiry_hours">Invitation Expiry (hours)</Label>
                            <Input
                                id="invitation_expiry_hours"
                                v-model.number="formData.invitation_expiry_hours"
                                type="number"
                                min="1"
                                max="720"
                            />
                            <p class="text-xs text-muted-foreground">How long an invitation link remains valid (1–720 hours)</p>
                            <InputError :message="errors.invitation_expiry_hours" />
                        </div>
                    </div>

                    <!-- Limits -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h3 class="text-sm font-medium">Limits</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="max_organizations_per_user">Max Organizations per User</Label>
                                <Input
                                    id="max_organizations_per_user"
                                    v-model.number="formData.max_organizations_per_user"
                                    type="number"
                                    min="0"
                                />
                                <p class="text-xs text-muted-foreground">Set to 0 for unlimited</p>
                                <InputError :message="errors.max_organizations_per_user" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="max_members_per_organization">Max Members per Organization</Label>
                                <Input
                                    id="max_members_per_organization"
                                    v-model.number="formData.max_members_per_organization"
                                    type="number"
                                    min="0"
                                />
                                <p class="text-xs text-muted-foreground">Set to 0 for unlimited</p>
                                <InputError :message="errors.max_members_per_organization" />
                            </div>
                        </div>
                    </div>

                    <!-- Member Roles -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h3 class="text-sm font-medium">Member Roles</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="default_member_role">Default Member Role</Label>
                                <select
                                    id="default_member_role"
                                    v-model="formData.default_member_role"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                >
                                    <option v-for="role in formData.member_roles" :key="role" :value="role">
                                        {{ role }}
                                    </option>
                                </select>
                                <InputError :message="errors.default_member_role" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="member_roles">Member Roles (comma-separated)</Label>
                                <Input
                                    id="member_roles"
                                    v-model="memberRolesInput"
                                    type="text"
                                    placeholder="owner, admin, member"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Current roles: {{ formData.member_roles.join(', ') }}
                                </p>
                                <InputError :message="errors.member_roles" />
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Save Settings
                        </Button>
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
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </div>
    </ModuleLayout>
</template>
