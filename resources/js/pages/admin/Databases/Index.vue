<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Database } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import connection from '@/routes/admin/database/connection';
import { index as databasesIndex } from '@/routes/admin/databases';

import { type BreadcrumbItem } from '@/types';

interface ConnectionInfo {
    name: string;
    driver: string;
    database: string;
    host?: string | null;
    port?: string | number | null;
    isDefault: boolean;
}

interface DatabasesIndexPageProps {
    connections: ConnectionInfo[];
}

const props = defineProps<DatabasesIndexPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Databases',
        href: databasesIndex().url,
    },
];

const getDriverColor = (driver: string): string => {
    const colors: Record<string, string> = {
        sqlite: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
        mysql: 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400',
        mariadb:
            'bg-pink-100 text-pink-800 dark:bg-pink-900/20 dark:text-pink-400',
        pgsql: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400',
        sqlsrv: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    };

    return (
        colors[driver] ??
        'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
    );
};
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Databases" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Database Connections"
                    description="View and manage all database connections"
                />

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="conn in connections"
                        :key="conn.name"
                        :href="connection.index(conn.name).url"
                        class="cursor-pointer rounded-lg border p-4 transition-colors hover:bg-accent/50"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-medium">
                                        {{ conn.name }}
                                    </h3>
                                    <span
                                        v-if="conn.isDefault"
                                        class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400"
                                    >
                                        Default
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-0.5 text-xs font-medium capitalize',
                                            getDriverColor(conn.driver),
                                        ]"
                                    >
                                        {{ conn.driver }}
                                    </span>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    Database: {{ conn.database }}
                                </p>
                                <p
                                    v-if="conn.host"
                                    class="text-sm text-muted-foreground"
                                >
                                    Host: {{ conn.host
                                    }}{{ conn.port ? `:${conn.port}` : '' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Database
                                    class="h-5 w-5 text-muted-foreground"
                                />
                            </div>
                        </div>
                    </Link>
                </div>

                <div
                    v-if="connections.length === 0"
                    class="rounded-lg border p-8 text-center"
                >
                    <p class="text-muted-foreground">
                        No database connections found.
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
