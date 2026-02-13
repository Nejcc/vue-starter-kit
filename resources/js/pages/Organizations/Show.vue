<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Mail, Users } from 'lucide-vue-next';

import DataCard from '@/components/DataCard.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Organization, type OrganizationInvitation } from '@/types';

interface ShowOrganizationPageProps {
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

const props = defineProps<ShowOrganizationPageProps>();
const { formatDate, formatDateTime } = useDateFormat();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Organizations', href: '/organizations' },
    { title: props.organization.name, href: '#' },
];

const roleVariant = (role: string) => {
    switch (role) {
        case 'owner':
            return 'purple' as const;
        case 'admin':
            return 'info' as const;
        default:
            return 'default' as const;
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="organization.name" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    :title="organization.name"
                    :description="organization.description ?? undefined"
                />

                <!-- Details -->
                <DataCard>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Slug</p>
                            <p class="font-medium">{{ organization.slug }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Owner</p>
                            <p class="font-medium">{{ organization.owner?.name ?? 'None' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Type</p>
                            <StatusBadge :label="organization.is_personal ? 'Personal' : 'Team'" :variant="organization.is_personal ? 'info' : 'default'" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Created</p>
                            <p class="font-medium">{{ formatDateTime(organization.created_at) }}</p>
                        </div>
                    </div>
                </DataCard>

                <!-- Members -->
                <DataCard>
                    <template #actions>
                        <div class="flex items-center gap-2">
                            <Users class="h-5 w-5 text-muted-foreground" />
                            <h3 class="text-lg font-medium">Members ({{ organization.members?.length ?? 0 }})</h3>
                        </div>
                    </template>

                    <div v-if="organization.members && organization.members.length > 0" class="space-y-3">
                        <div
                            v-for="member in organization.members"
                            :key="member.id"
                            class="flex items-center justify-between rounded-lg border p-3"
                        >
                            <div>
                                <p class="font-medium">{{ member.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ member.email }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <StatusBadge :label="member.pivot.role" :variant="roleVariant(member.pivot.role)" />
                                <span class="text-xs text-muted-foreground">
                                    Joined {{ formatDate(member.pivot.joined_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">No members.</p>
                </DataCard>

                <!-- Pending Invitations -->
                <DataCard v-if="organization.invitations && organization.invitations.length > 0">
                    <template #actions>
                        <div class="flex items-center gap-2">
                            <Mail class="h-5 w-5 text-muted-foreground" />
                            <h3 class="text-lg font-medium">Pending Invitations</h3>
                        </div>
                    </template>

                    <div class="space-y-2">
                        <div
                            v-for="invitation in organization.invitations"
                            :key="invitation.id"
                            class="flex items-center justify-between rounded border p-3"
                        >
                            <div>
                                <p class="text-sm font-medium">{{ invitation.email }}</p>
                                <p class="text-xs text-muted-foreground">
                                    Expires {{ formatDate(invitation.expires_at) }}
                                </p>
                            </div>
                            <StatusBadge :label="invitation.role" variant="default" />
                        </div>
                    </div>
                </DataCard>

                <div>
                    <Link
                        href="/organizations"
                        class="text-sm text-muted-foreground hover:underline"
                    >
                        Back to Organizations
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
