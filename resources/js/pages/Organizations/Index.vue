<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Building2, Plus, Users } from 'lucide-vue-next';

import DataCard from '@/components/DataCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { useDateFormat } from '@/composables/useDateFormat';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Organization } from '@/types';

interface OrganizationsPageProps {
    organizations: Array<
        Organization & {
            owner?: { id: number; name: string };
            members_count?: number;
            pivot?: { role: string; joined_at: string };
        }
    >;
}

defineProps<OrganizationsPageProps>();
const { formatDate } = useDateFormat();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Organizations', href: '/organizations' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Organizations" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading variant="small" title="Organizations" description="Manage your organizations" />
                    <Link href="/organizations/create">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create Organization
                        </Button>
                    </Link>
                </div>

                <template v-if="organizations.length > 0">
                    <div class="grid gap-4">
                        <DataCard
                            v-for="org in organizations"
                            :key="org.id"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                        <Building2 class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <Link
                                            :href="`/organizations/${org.slug}`"
                                            class="font-medium hover:underline"
                                        >
                                            {{ org.name }}
                                        </Link>
                                        <p class="text-sm text-muted-foreground">
                                            {{ org.slug }}
                                        </p>
                                        <p v-if="org.description" class="mt-1 text-sm text-muted-foreground">
                                            {{ org.description }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <StatusBadge v-if="org.is_personal" label="Personal" variant="info" />
                                    <StatusBadge v-else label="Team" variant="default" />
                                    <StatusBadge v-if="org.pivot?.role" :label="org.pivot.role" variant="purple" />
                                </div>
                            </div>

                            <template #footer>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                        <span class="flex items-center gap-1">
                                            <Users class="h-4 w-4" />
                                            {{ org.members_count ?? 0 }} member(s)
                                        </span>
                                        <span>Created {{ formatDate(org.created_at) }}</span>
                                    </div>
                                    <Link :href="`/organizations/${org.slug}`">
                                        <Button variant="outline" size="sm">View</Button>
                                    </Link>
                                </div>
                            </template>
                        </DataCard>
                    </div>
                </template>

                <EmptyState
                    v-else
                    title="No organizations yet"
                    description="Create your first organization to get started."
                />
            </div>
        </div>
    </AppLayout>
</template>
