<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import {
    Activity,
    Database,
    Key,
    Settings,
    Shield,
    UserCheck,
    Users,
} from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import StatCard from '@/components/StatCard.vue';
import { useDateFormat } from '@/composables/useDateFormat';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { index as databasesIndex } from '@/routes/admin/databases';
import { index as permissionsIndex } from '@/routes/admin/permissions';
import { index as rolesIndex } from '@/routes/admin/roles';
import { index as usersIndex } from '@/routes/admin/users';
import { type BreadcrumbItem } from '@/types';

interface RecentUser {
    id: number;
    name: string;
    email: string;
    created_at: string;
}

interface AuditEntry {
    id: number;
    user_id: number | null;
    event: string;
    auditable_type: string | null;
    auditable_id: number | null;
    created_at: string;
    user?: {
        id: number;
        name: string;
        email: string;
    } | null;
}

interface DashboardProps {
    stats: {
        totalUsers: number;
        verifiedUsers: number;
        totalRoles: number;
        totalPermissions: number;
    };
    recentUsers: RecentUser[];
    recentActivity: AuditEntry[];
}

const props = defineProps<DashboardProps>();
const { formatShortDate } = useDateFormat();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Dashboard',
        href: '#',
    },
];

const quickLinks = [
    {
        title: 'Users',
        description: 'Manage application users',
        href: usersIndex().url,
        icon: Users,
        color: 'bg-blue-500 dark:bg-blue-600',
    },
    {
        title: 'Roles',
        description: 'Manage user roles',
        href: rolesIndex().url,
        icon: Shield,
        color: 'bg-green-500 dark:bg-green-600',
    },
    {
        title: 'Permissions',
        description: 'Manage permissions',
        href: permissionsIndex().url,
        icon: Key,
        color: 'bg-purple-500 dark:bg-purple-600',
    },
    {
        title: 'Databases',
        description: 'View all database connections',
        href: databasesIndex().url,
        icon: Database,
        color: 'bg-orange-500 dark:bg-orange-600',
    },
    {
        title: 'Settings',
        description: 'Application settings',
        href: '/admin/settings',
        icon: Settings,
        color: 'bg-gray-500 dark:bg-gray-600',
    },
];

const statCards = [
    {
        label: 'Total Users',
        value: props.stats.totalUsers,
        icon: Users,
        iconColor: 'text-blue-600 dark:text-blue-400',
        iconBg: 'bg-blue-50 dark:bg-blue-950/50',
    },
    {
        label: 'Verified Users',
        value: props.stats.verifiedUsers,
        icon: UserCheck,
        iconColor: 'text-green-600 dark:text-green-400',
        iconBg: 'bg-green-50 dark:bg-green-950/50',
    },
    {
        label: 'Roles',
        value: props.stats.totalRoles,
        icon: Shield,
        iconColor: 'text-purple-600 dark:text-purple-400',
        iconBg: 'bg-purple-50 dark:bg-purple-950/50',
    },
    {
        label: 'Permissions',
        value: props.stats.totalPermissions,
        icon: Key,
        iconColor: 'text-orange-600 dark:text-orange-400',
        iconBg: 'bg-orange-50 dark:bg-orange-950/50',
    },
];

function formatEventName(event: string): string {
    return event.replace(/[._]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Admin Dashboard" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col gap-8">
                <Heading
                    title="Admin Dashboard"
                    description="Manage your application"
                    variant="small"
                />

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <StatCard
                        v-for="stat in statCards"
                        :key="stat.label"
                        :label="stat.label"
                        :value="stat.value"
                        :icon="stat.icon"
                        :icon-color="stat.iconColor"
                        :icon-bg="stat.iconBg"
                    />
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Recent Users -->
                    <div class="rounded-lg border">
                        <div
                            class="flex items-center justify-between border-b px-4 py-3"
                        >
                            <h3 class="font-semibold">Recent Users</h3>
                            <Link
                                :href="usersIndex().url"
                                class="text-sm text-primary hover:underline"
                            >
                                View all
                            </Link>
                        </div>
                        <div v-if="recentUsers.length > 0" class="divide-y">
                            <div
                                v-for="user in recentUsers"
                                :key="user.id"
                                class="flex items-center justify-between px-4 py-3"
                            >
                                <div>
                                    <p class="text-sm font-medium">
                                        {{ user.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ user.email }}
                                    </p>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatShortDate(user.created_at) }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-else
                            class="px-4 py-8 text-center text-sm text-muted-foreground"
                        >
                            No users yet.
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="rounded-lg border">
                        <div
                            class="flex items-center justify-between border-b px-4 py-3"
                        >
                            <h3 class="font-semibold">Recent Activity</h3>
                        </div>
                        <div v-if="recentActivity.length > 0" class="divide-y">
                            <div
                                v-for="entry in recentActivity"
                                :key="entry.id"
                                class="flex items-start gap-3 px-4 py-3"
                            >
                                <div class="mt-0.5 rounded-lg bg-muted p-1.5">
                                    <Activity
                                        class="h-3.5 w-3.5 text-muted-foreground"
                                    />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm">
                                        <span class="font-medium">{{
                                            entry.user?.name ?? 'System'
                                        }}</span>
                                        <span class="text-muted-foreground">
                                            &mdash;
                                            {{
                                                formatEventName(entry.event)
                                            }}</span
                                        >
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatShortDate(entry.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="px-4 py-8 text-center text-sm text-muted-foreground"
                        >
                            No activity recorded yet.
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="mb-4 font-semibold">Quick Links</h3>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="link in quickLinks"
                            :key="link.title"
                            :href="link.href"
                            class="group rounded-lg border p-6 transition-colors hover:bg-accent/50"
                        >
                            <div class="flex items-start gap-4">
                                <div
                                    :class="[
                                        link.color,
                                        'rounded-lg p-3 text-white transition-transform group-hover:scale-110',
                                    ]"
                                >
                                    <component
                                        :is="link.icon"
                                        class="h-6 w-6"
                                    />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold">
                                        {{ link.title }}
                                    </h3>
                                    <p
                                        class="mt-1 text-sm text-muted-foreground"
                                    >
                                        {{ link.description }}
                                    </p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
