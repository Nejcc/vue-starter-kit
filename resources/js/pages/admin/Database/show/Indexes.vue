<script setup lang="ts">
import connection from '@/routes/admin/database/connection';
import { Link } from '@inertiajs/vue3';
import type { ForeignKey, Index } from './types';

interface Props {
    indexes: Index[];
    foreignKeys: ForeignKey[];
    currentConnection: string;
}

const props = defineProps<Props>();
</script>

<template>
    <div class="space-y-6">
        <!-- Indexes Section -->
        <div
            v-if="indexes.length > 0"
            id="indexes"
            class="scroll-mt-4 rounded-lg border"
        >
            <div class="border-b p-4">
                <h2 class="text-lg font-semibold">Indexes</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="px-4 py-2 text-left font-semibold">
                                Name
                            </th>
                            <th class="px-4 py-2 text-left font-semibold">
                                Unique
                            </th>
                            <th class="px-4 py-2 text-left font-semibold">
                                Columns
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="index in indexes"
                            :key="index.name"
                            class="border-b"
                        >
                            <td class="px-4 py-2 font-medium">
                                {{ index.name }}
                            </td>
                            <td class="px-4 py-2">
                                <span
                                    v-if="index.unique"
                                    class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400"
                                >
                                    Unique
                                </span>
                                <span v-else class="text-muted-foreground">
                                    No
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex flex-wrap gap-1">
                                    <code
                                        v-for="col in index.columns"
                                        :key="col"
                                        class="rounded bg-muted px-1 py-0.5 text-sm"
                                    >
                                        {{ col }}
                                    </code>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Foreign Keys Section -->
        <div v-if="foreignKeys.length > 0" class="rounded-lg border">
            <div class="border-b p-4">
                <h2 class="text-lg font-semibold">Foreign Keys</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="px-4 py-2 text-left font-semibold">
                                Name
                            </th>
                            <th class="px-4 py-2 text-left font-semibold">
                                Columns
                            </th>
                            <th class="px-4 py-2 text-left font-semibold">
                                References
                            </th>
                            <th class="px-4 py-2 text-left font-semibold">
                                On Delete
                            </th>
                            <th class="px-4 py-2 text-left font-semibold">
                                On Update
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="fk in foreignKeys"
                            :key="fk.name"
                            class="border-b"
                        >
                            <td class="px-4 py-2 font-medium">
                                {{ fk.name }}
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex flex-wrap gap-1">
                                    <code
                                        v-for="col in fk.columns"
                                        :key="col"
                                        class="rounded bg-muted px-1 py-0.5 text-sm"
                                    >
                                        {{ col }}
                                    </code>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex flex-col gap-1">
                                    <Link
                                        :href="
                                            connection.show(
                                                props.currentConnection,
                                                fk.referencedTable,
                                            ).url
                                        "
                                        class="font-medium text-primary hover:underline"
                                    >
                                        {{ fk.referencedTable }}
                                    </Link>
                                    <div class="flex flex-wrap gap-1">
                                        <code
                                            v-for="col in fk.referencedColumns"
                                            :key="col"
                                            class="rounded bg-muted px-1 py-0.5 text-sm"
                                        >
                                            {{ col }}
                                        </code>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <code
                                    v-if="fk.onDelete"
                                    class="rounded bg-muted px-1 py-0.5 text-sm"
                                >
                                    {{ fk.onDelete }}
                                </code>
                                <span v-else class="text-muted-foreground">
                                    -
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <code
                                    v-if="fk.onUpdate"
                                    class="rounded bg-muted px-1 py-0.5 text-sm"
                                >
                                    {{ fk.onUpdate }}
                                </code>
                                <span v-else class="text-muted-foreground">
                                    -
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
