<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Database, Settings, Users, Shield, Key } from 'lucide-vue-next';
import { index as usersIndex } from '@/routes/admin/users';
import { index as rolesIndex } from '@/routes/admin/roles';
import { index as permissionsIndex } from '@/routes/admin/permissions';
import { index as settingsIndex } from '@/routes/admin/settings';
import { index as databasesIndex } from '@/routes/admin/databases';

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
        color: 'bg-blue-500',
    },
    {
        title: 'Roles',
        description: 'Manage user roles',
        href: rolesIndex().url,
        icon: Shield,
        color: 'bg-green-500',
    },
    {
        title: 'Permissions',
        description: 'Manage permissions',
        href: permissionsIndex().url,
        icon: Key,
        color: 'bg-purple-500',
    },
    {
        title: 'Databases',
        description: 'View all database connections',
        href: databasesIndex().url,
        icon: Database,
        color: 'bg-orange-500',
    },
    {
        title: 'Settings',
        description: 'Application settings',
        href: settingsIndex().url,
        icon: Settings,
        color: 'bg-gray-500',
    },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Admin Dashboard" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Admin Dashboard"
                    description="Manage your application"
                />

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
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
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ link.description }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
