<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { Mail, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useDateFormat } from '@/composables/useDateFormat';
import { useOrganizationNav } from '@/composables/useOrganizationNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import {
    type BreadcrumbItem,
    type Organization,
    type OrganizationInvitation,
} from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useOrganizationNav();

interface EditOrganizationPageProps {
    organization: Organization & {
        owner?: { id: number; name: string; email: string };
        members?: Array<{
            id: number;
            name: string;
            email: string;
            pivot: { role: string; joined_at: string };
        }>;
        invitations?: OrganizationInvitation[];
    };
}

const props = defineProps<EditOrganizationPageProps>();
const { formatDate } = useDateFormat();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Organizations', href: '/admin/organizations' },
    { title: props.organization.name, href: '#' },
];

const inviteEmail = ref('');
const inviteRole = ref('member');

function removeMember(userId: number) {
    if (confirm('Are you sure you want to remove this member?')) {
        router.delete(
            `/admin/organizations/${props.organization.slug}/members/${userId}`,
        );
    }
}

function changeRole(userId: number, role: string) {
    router.patch(
        `/admin/organizations/${props.organization.slug}/members/${userId}/role`,
        { role },
    );
}

function sendInvitation() {
    router.post(
        `/organizations/${props.organization.slug}/invitations`,
        {
            email: inviteEmail.value,
            role: inviteRole.value,
        },
        {
            onSuccess: () => {
                inviteEmail.value = '';
                inviteRole.value = 'member';
            },
        },
    );
}

function cancelInvitation(invitationId: number) {
    router.delete(
        `/organizations/${props.organization.slug}/invitations/${invitationId}`,
    );
}

const roleVariant = (role: string) => {
    switch (role) {
        case 'owner':
            return 'purple';
        case 'admin':
            return 'info';
        default:
            return 'default';
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
        <Head :title="`Edit ${organization.name}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-8">
                <Heading
                    variant="small"
                    :title="`Edit ${organization.name}`"
                    description="Update organization details and manage members"
                />

                <!-- Organization Details Form -->
                <div class="rounded-lg border p-6">
                    <h3 class="mb-4 text-lg font-medium">
                        Organization Details
                    </h3>
                    <Form
                        :action="`/admin/organizations/${organization.slug}`"
                        method="put"
                        class="space-y-4"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <FormField
                            label="Name"
                            id="name"
                            :error="errors.name"
                            required
                        >
                            <Input
                                id="name"
                                name="name"
                                type="text"
                                :value="organization.name"
                                required
                            />
                        </FormField>

                        <FormField label="Slug" id="slug" :error="errors.slug">
                            <Input
                                id="slug"
                                name="slug"
                                type="text"
                                :value="organization.slug"
                            />
                        </FormField>

                        <FormField
                            label="Description"
                            id="description"
                            :error="errors.description"
                        >
                            <Textarea
                                id="description"
                                name="description"
                                :value="organization.description ?? ''"
                                rows="3"
                            />
                        </FormField>

                        <div class="flex items-center gap-4">
                            <Button :disabled="processing" type="submit">
                                Update Organization
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
                                    Updated.
                                </p>
                            </Transition>
                        </div>
                    </Form>
                </div>

                <!-- Members Section -->
                <div class="rounded-lg border p-6">
                    <h3 class="mb-4 text-lg font-medium">Members</h3>

                    <div
                        v-if="
                            organization.members &&
                            organization.members.length > 0
                        "
                        class="space-y-3"
                    >
                        <div
                            v-for="member in organization.members"
                            :key="member.id"
                            class="flex items-center justify-between rounded-lg border p-3"
                        >
                            <div>
                                <p class="font-medium">{{ member.name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ member.email }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <StatusBadge
                                    :label="member.pivot.role"
                                    :variant="roleVariant(member.pivot.role)"
                                />
                                <select
                                    v-if="member.pivot.role !== 'owner'"
                                    :value="member.pivot.role"
                                    class="rounded border px-2 py-1 text-sm"
                                    @change="
                                        changeRole(
                                            member.id,
                                            ($event.target as HTMLSelectElement)
                                                .value,
                                        )
                                    "
                                >
                                    <option value="admin">Admin</option>
                                    <option value="member">Member</option>
                                </select>
                                <Button
                                    v-if="member.pivot.role !== 'owner'"
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive hover:text-destructive"
                                    @click="removeMember(member.id)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">
                        No members yet.
                    </p>
                </div>

                <!-- Invitations Section -->
                <div class="rounded-lg border p-6">
                    <h3 class="mb-4 text-lg font-medium">Invite Member</h3>

                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label class="mb-1 block text-sm font-medium"
                                >Email</label
                            >
                            <Input
                                v-model="inviteEmail"
                                type="email"
                                placeholder="user@example.com"
                            />
                        </div>
                        <div class="w-32">
                            <label class="mb-1 block text-sm font-medium"
                                >Role</label
                            >
                            <select
                                v-model="inviteRole"
                                class="w-full rounded border px-3 py-2 text-sm"
                            >
                                <option value="admin">Admin</option>
                                <option value="member">Member</option>
                            </select>
                        </div>
                        <Button
                            @click="sendInvitation"
                            :disabled="!inviteEmail"
                        >
                            <Mail class="mr-2 h-4 w-4" />
                            Send Invite
                        </Button>
                    </div>

                    <div
                        v-if="
                            organization.invitations &&
                            organization.invitations.length > 0
                        "
                        class="mt-4 space-y-2"
                    >
                        <h4 class="text-sm font-medium text-muted-foreground">
                            Pending Invitations
                        </h4>
                        <div
                            v-for="invitation in organization.invitations"
                            :key="invitation.id"
                            class="flex items-center justify-between rounded border p-3"
                        >
                            <div>
                                <p class="text-sm font-medium">
                                    {{ invitation.email }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Expires
                                    {{ formatDate(invitation.expires_at) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <StatusBadge
                                    :label="invitation.role"
                                    variant="default"
                                />
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive hover:text-destructive"
                                    @click="cancelInvitation(invitation.id)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/organizations"
                        class="text-sm text-muted-foreground hover:underline"
                    >
                        Back to Organizations
                    </Link>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
