<script setup lang="ts">
import type { Column } from './types';

interface Props {
    columns: Column[];
}

defineProps<Props>();
</script>

<template>
    <div id="structure" class="scroll-mt-4 rounded-lg border">
        <div class="border-b p-4">
            <h2 class="text-lg font-semibold">Columns</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="px-4 py-2 text-left font-semibold">Name</th>
                        <th class="px-4 py-2 text-left font-semibold">Type</th>
                        <th class="px-4 py-2 text-left font-semibold">
                            Nullable
                        </th>
                        <th class="px-4 py-2 text-left font-semibold">
                            Default
                        </th>
                        <th class="px-4 py-2 text-left font-semibold">
                            Primary
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="column in columns"
                        :key="column.name"
                        class="border-b"
                    >
                        <td class="px-4 py-2 font-medium">
                            {{ column.name }}
                        </td>
                        <td class="px-4 py-2">
                            <code class="rounded bg-muted px-1 py-0.5 text-sm">
                                {{ column.type }}
                            </code>
                        </td>
                        <td class="px-4 py-2">
                            <span
                                :class="{
                                    'text-green-600 dark:text-green-400':
                                        column.nullable,
                                    'text-red-600 dark:text-red-400':
                                        !column.nullable,
                                }"
                            >
                                {{ column.nullable ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            <code
                                v-if="column.default"
                                class="rounded bg-muted px-1 py-0.5 text-sm"
                            >
                                {{ column.default }}
                            </code>
                            <span v-else class="text-muted-foreground">
                                NULL
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            <span
                                v-if="column.primary"
                                class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                            >
                                Primary
                            </span>
                            <span v-else class="text-muted-foreground">
                                -
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
