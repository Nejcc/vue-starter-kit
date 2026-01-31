<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Database } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import connection from '@/routes/admin/database/connection';
import { view } from '@/routes/admin/database/connection/show';
import { index as databasesIndex } from '@/routes/admin/databases';

import { type BreadcrumbItem } from '@/types';

import Actions from './show/Actions.vue';
import Data from './show/Data.vue';
import Indexes from './show/Indexes.vue';
import Structure from './show/Structure.vue';
import type { TableInfo } from './show/types';

interface DatabaseShowPageProps {
    table: TableInfo;
    connections: string[];
    currentConnection: string;
    driver: string;
    view?: string | null;
}

const props = defineProps<DatabaseShowPageProps>();

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
    {
        title: props.table.name,
        href: props.view
            ? view({
                  connection: props.currentConnection,
                  table: props.table.name,
                  view: props.view,
              }).url
            : connection.show({
                  connection: props.currentConnection,
                  table: props.table.name,
              }).url,
    },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Database: ${table.name}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="
                                connection.index(props.currentConnection).url
                            "
                            class="text-muted-foreground hover:text-foreground"
                        >
                            <ArrowLeft class="h-5 w-5" />
                        </Link>
                        <Heading
                            variant="small"
                            :title="table.name"
                            :description="`Table details and structure`"
                        />
                    </div>
                    <div
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <Database class="h-4 w-4" />
                        <span
                            >{{ props.driver.toUpperCase() }} -
                            {{ props.currentConnection }}</span
                        >
                    </div>
                </div>

                <!-- Data View (when view is 'data' or no view) -->
                <Data
                    v-if="props.view === 'data' || !props.view"
                    :columns="table.columns"
                    :data="table.data"
                    :pagination="table.pagination"
                    :row-count="table.rowCount"
                    :table-name="table.name"
                    :current-connection="props.currentConnection"
                />

                <!-- Summary Stats (when view is not 'data') -->
                <div v-else class="rounded-lg border p-4">
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-muted-foreground">
                            <strong>Rows:</strong>
                            {{ table.rowCount.toLocaleString() }}
                        </span>
                        <span class="text-muted-foreground">
                            <strong>Columns:</strong> {{ table.columns.length }}
                        </span>
                        <span class="text-muted-foreground">
                            <strong>Indexes:</strong> {{ table.indexes.length }}
                        </span>
                        <span class="text-muted-foreground">
                            <strong>Foreign Keys:</strong>
                            {{ table.foreignKeys.length }}
                        </span>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Structure View -->
                    <Structure
                        v-if="!props.view || props.view === 'structure'"
                        :columns="table.columns"
                    />

                    <!-- Indexes View -->
                    <Indexes
                        v-if="!props.view || props.view === 'indexes'"
                        :indexes="table.indexes"
                        :foreign-keys="table.foreignKeys"
                        :current-connection="props.currentConnection"
                    />

                    <!-- Actions View -->
                    <Actions
                        v-if="!props.view || props.view === 'actions'"
                        :table-name="table.name"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
