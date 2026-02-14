<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Database } from 'lucide-vue-next';
import { decodePaginationLabel } from '@/lib/utils';
import { view } from '@/routes/admin/database/connection/show';
import type { Column, Pagination } from './types';

interface Props {
    columns: Column[];
    data: Record<string, any>[];
    pagination: Pagination | null;
    rowCount: number;
    tableName: string;
    currentConnection: string;
}

defineProps<Props>();
</script>

<template>
    <div id="data" class="scroll-mt-4 rounded-lg border">
        <div class="border-b p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Data</h2>
                    <p
                        v-if="pagination"
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        Showing {{ pagination.from }} to {{ pagination.to }} of
                        {{ pagination.total.toLocaleString() }} rows
                    </p>
                    <p v-else class="mt-1 text-sm text-muted-foreground">
                        {{ rowCount.toLocaleString() }} total rows
                    </p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th
                            v-for="column in columns"
                            :key="column.name"
                            class="px-4 py-2 text-left font-semibold"
                        >
                            {{ column.name }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, rowIndex) in data"
                        :key="rowIndex"
                        class="border-b"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.name"
                            class="px-4 py-2"
                        >
                            <span
                                v-if="
                                    row[column.name] !== null &&
                                    row[column.name] !== undefined
                                "
                                class="text-sm"
                            >
                                {{
                                    typeof row[column.name] === 'object'
                                        ? JSON.stringify(row[column.name])
                                        : String(row[column.name])
                                }}
                            </span>
                            <span v-else class="text-sm text-muted-foreground">
                                NULL
                            </span>
                        </td>
                    </tr>
                    <tr v-if="data.length === 0">
                        <td
                            :colspan="columns.length"
                            class="px-4 py-16 text-center"
                        >
                            <div
                                class="flex flex-col items-center justify-center space-y-3"
                            >
                                <Database
                                    class="h-12 w-12 text-muted-foreground/40"
                                />
                                <div>
                                    <p
                                        class="text-sm font-medium text-muted-foreground"
                                    >
                                        No data in this table
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground/60"
                                    >
                                        The table "{{ tableName }}" is empty
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="pagination && pagination.last_page > 1" class="border-t p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-muted-foreground">
                        Page {{ pagination.current_page }} of
                        {{ pagination.last_page }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="pagination.current_page > 1"
                        :href="
                            view({
                                connection: currentConnection,
                                table: tableName,
                                view: 'data',
                            }).url +
                            `?page=${pagination.current_page - 1}&per_page=${pagination.per_page}`
                        "
                        class="rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                    >
                        Previous
                    </Link>
                    <template
                        v-for="link in pagination.links"
                        :key="link.label"
                    >
                        <Link
                            v-if="link.url && !link.active"
                            :href="link.url"
                            class="rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                        >
                            {{ decodePaginationLabel(link.label) }}
                        </Link>
                        <span
                            v-else-if="link.active"
                            class="rounded-md border border-primary bg-primary px-3 py-1.5 text-sm text-primary-foreground"
                        >
                            {{ decodePaginationLabel(link.label) }}
                        </span>
                        <span
                            v-else
                            class="px-3 py-1.5 text-sm text-muted-foreground"
                        >
                            {{ decodePaginationLabel(link.label) }}
                        </span>
                    </template>
                    <Link
                        v-if="pagination.current_page < pagination.last_page"
                        :href="
                            view({
                                connection: currentConnection,
                                table: tableName,
                                view: 'data',
                            }).url +
                            `?page=${pagination.current_page + 1}&per_page=${pagination.per_page}`
                        "
                        class="rounded-md border px-3 py-1.5 text-sm hover:bg-accent"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
