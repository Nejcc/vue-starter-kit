<script setup lang="ts">
import connection from '@/routes/admin/database/connection';
import { view } from '@/routes/admin/database/connection/show';
import { index as databasesIndex } from '@/routes/admin/databases';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Database, Search, Table2, List, Settings } from 'lucide-vue-next';

interface Table {
    name: string;
    rows?: number | null;
    size?: number | null;
}

interface DatabaseIndexPageProps {
    tables: Table[];
    connections: string[];
    currentConnection: string;
    driver: string;
}

const props = defineProps<DatabaseIndexPageProps>();

const searchQuery = ref('');

const formatBytes = (bytes: number | null | undefined): string => {
    if (!bytes || bytes === 0) {
        return 'N/A';
    }

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
};

const filteredTables = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.tables;
    }

    const query = searchQuery.value.toLowerCase();
    return props.tables.filter((table) => table.name.toLowerCase().includes(query));
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '#',
    },
    {
        title: 'Databases',
        href: databasesIndex().url,
    },
    {
        title: props.currentConnection,
        href: connection.index(props.currentConnection).url,
    },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Database" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <HeadingSmall
                        title="Database Tables"
                        :description="`${props.driver.toUpperCase()} - ${props.currentConnection}`"
                    />
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search tables..."
                            class="pl-9"
                        />
                    </div>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="table in filteredTables"
                        :key="table.name"
                        class="rounded-lg border p-4 transition-colors hover:bg-accent/50"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="view({ connection: props.currentConnection, table: table.name, view: 'data' }).url"
                                        class="text-base font-medium text-primary hover:underline"
                                    >
                                        {{ table.name }}
                                    </Link>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                    <span v-if="table.rows !== null && table.rows !== undefined">
                                        {{ table.rows.toLocaleString() }} rows
                                    </span>
                                    <span v-if="table.size">
                                        Size: {{ formatBytes(table.size) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="view({ connection: props.currentConnection, table: table.name, view: 'structure' }).url"
                                >
                                    <Button
                                        variant="outline"
                                        size="sm"
                                    >
                                        <Database class="mr-2 h-4 w-4" />
                                        Structure
                                    </Button>
                                </Link>
                                <Link
                                    :href="view({ connection: props.currentConnection, table: table.name, view: 'indexes' }).url"
                                >
                                    <Button
                                        variant="outline"
                                        size="sm"
                                    >
                                        <List class="mr-2 h-4 w-4" />
                                        Indexes
                                    </Button>
                                </Link>
                                <Link
                                    :href="view({ connection: props.currentConnection, table: table.name, view: 'data' }).url"
                                >
                                    <Button
                                        variant="outline"
                                        size="sm"
                                    >
                                        <Table2 class="mr-2 h-4 w-4" />
                                        Data
                                    </Button>
                                </Link>
                                <Link
                                    :href="view({ connection: props.currentConnection, table: table.name, view: 'actions' }).url"
                                >
                                    <Button
                                        variant="outline"
                                        size="sm"
                                    >
                                        <Settings class="mr-2 h-4 w-4" />
                                        Actions
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="filteredTables.length === 0"
                    class="rounded-lg border p-8 text-center"
                >
                    <p class="text-muted-foreground">
                        {{ searchQuery ? 'No tables found matching your search.' : 'No tables found.' }}
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
