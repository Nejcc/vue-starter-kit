<script setup lang="ts">
import { Button } from '@/components/ui/button';

interface Props {
    tableName: string;
}

const props = defineProps<Props>();

const confirmTruncate = (): void => {
    if (confirm(`Are you sure you want to truncate the table "${props.tableName}"? This will permanently delete all rows and cannot be undone.`)) {
        // TODO: Implement truncate action
        alert('Truncate functionality will be implemented');
    }
};

const confirmDrop = (): void => {
    if (confirm(`Are you sure you want to drop the table "${props.tableName}"? This will permanently delete the table and all its data and cannot be undone.`)) {
        // TODO: Implement drop action
        alert('Drop functionality will be implemented');
    }
};
</script>

<template>
    <div
        id="actions"
        class="rounded-lg border scroll-mt-4"
    >
        <div class="border-b p-4">
            <h2 class="text-lg font-semibold">Actions</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Manage table operations
            </p>
        </div>
        <div class="p-4">
            <div class="space-y-4">
                <div class="rounded-md border p-4">
                    <h3 class="text-sm font-medium mb-4">Dangerous Operations</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between rounded-md border border-red-200 bg-red-50 p-3 dark:border-red-900/50 dark:bg-red-900/10">
                            <div>
                                <h4 class="text-sm font-medium text-red-900 dark:text-red-400">
                                    Truncate Table
                                </h4>
                                <p class="mt-1 text-xs text-red-700 dark:text-red-500">
                                    Permanently delete all rows from this table. This action cannot be undone.
                                </p>
                            </div>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="confirmTruncate"
                            >
                                Truncate
                            </Button>
                        </div>
                        <div class="flex items-center justify-between rounded-md border border-red-200 bg-red-50 p-3 dark:border-red-900/50 dark:bg-red-900/10">
                            <div>
                                <h4 class="text-sm font-medium text-red-900 dark:text-red-400">
                                    Drop Table
                                </h4>
                                <p class="mt-1 text-xs text-red-700 dark:text-red-500">
                                    Permanently delete this table and all its data. This action cannot be undone.
                                </p>
                            </div>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="confirmDrop"
                            >
                                Drop
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
