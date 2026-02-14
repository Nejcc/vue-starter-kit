<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Eye,
    RefreshCw,
    RotateCcw,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatCard from '@/components/StatCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useDateFormat } from '@/composables/useDateFormat';
import { useSearch } from '@/composables/useSearch';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import {
    index as failedJobsIndex,
    show as failedJobsShow,
} from '@/routes/admin/failed-jobs';
import { type BreadcrumbItem } from '@/types';

interface FailedJob {
    id: number;
    uuid: string;
    connection: string;
    queue: string;
    job_name: string;
    exception_summary: string;
    failed_at: string;
}

interface PaginatedFailedJobs {
    data: FailedJob[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

interface Props {
    failedJobs: PaginatedFailedJobs;
    queues: string[];
    stats: {
        total: number;
        queues: number;
    };
    filters: {
        search: string;
        queue: string;
    };
}

const props = defineProps<Props>();
const { formatShortDate } = useDateFormat();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Failed Jobs', href: '#' },
];

const selectedQueue = ref(props.filters.queue || 'all');

const { searchQuery, handleSearch, initFromFilter } = useSearch({
    url: failedJobsIndex().url,
    extraParams: () => ({
        queue: selectedQueue.value !== 'all' ? selectedQueue.value : null,
    }),
});

initFromFilter(props.filters.search);

function filterByQueue(value: string): void {
    selectedQueue.value = value;
    router.get(
        failedJobsIndex().url,
        {
            search: searchQuery.value || null,
            queue: value !== 'all' ? value : null,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function retryJob(uuid: string): void {
    router.post(
        `/admin/failed-jobs/${uuid}/retry`,
        {},
        { preserveScroll: true },
    );
}

function deleteJob(id: number): void {
    if (!confirm('Are you sure you want to delete this failed job?')) return;
    router.delete(`/admin/failed-jobs/${id}`, { preserveScroll: true });
}

function retryAll(): void {
    if (
        !confirm(
            'Are you sure you want to retry all failed jobs? They will be pushed back onto the queue.',
        )
    )
        return;
    router.post(
        `/admin/failed-jobs/retry-all`,
        {},
        { preserveScroll: true },
    );
}

function deleteAll(): void {
    if (
        !confirm(
            'Are you sure you want to delete ALL failed jobs? This cannot be undone.',
        )
    )
        return;
    router.delete(`/admin/failed-jobs`, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Failed Jobs" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <Heading
                        title="Failed Jobs"
                        description="View and manage failed queue jobs"
                        variant="small"
                    />
                    <div
                        v-if="stats.total > 0"
                        class="flex items-center gap-2"
                    >
                        <Button variant="outline" size="sm" @click="retryAll">
                            <RotateCcw class="mr-2 h-4 w-4" />
                            Retry All
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="deleteAll"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Flush All
                        </Button>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <StatCard
                        label="Failed Jobs"
                        :value="stats.total"
                        :icon="AlertTriangle"
                        icon-color="text-red-600 dark:text-red-400"
                        icon-bg="bg-red-50 dark:bg-red-950/50"
                    />
                    <StatCard
                        label="Queues"
                        :value="stats.queues"
                        :icon="RefreshCw"
                        icon-color="text-blue-600 dark:text-blue-400"
                        icon-bg="bg-blue-50 dark:bg-blue-950/50"
                    />
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex-1">
                        <SearchInput
                            v-model="searchQuery"
                            placeholder="Search jobs..."
                            @update:model-value="handleSearch"
                        />
                    </div>
                    <select
                        :value="selectedQueue"
                        class="h-9 w-48 rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        @change="
                            filterByQueue(
                                ($event.target as HTMLSelectElement).value,
                            )
                        "
                    >
                        <option value="all">All Queues</option>
                        <option
                            v-for="q in queues"
                            :key="q"
                            :value="q"
                        >
                            {{ q }}
                        </option>
                    </select>
                </div>

                <!-- Table -->
                <div class="rounded-lg border">
                    <div class="overflow-x-auto">
                        <table
                            v-if="failedJobs.data.length > 0"
                            class="w-full text-sm"
                        >
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th
                                        class="px-4 py-3 text-left font-medium"
                                    >
                                        Job
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-medium"
                                    >
                                        Queue
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-medium"
                                    >
                                        Error
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-medium"
                                    >
                                        Failed At
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right font-medium"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="job in failedJobs.data"
                                    :key="job.id"
                                    class="hover:bg-muted/30"
                                >
                                    <td class="px-4 py-3">
                                        <div class="font-medium">
                                            {{ job.job_name }}
                                        </div>
                                        <div
                                            class="font-mono text-xs text-muted-foreground"
                                        >
                                            {{ job.uuid.substring(0, 8) }}...
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge variant="outline">
                                            {{ job.queue }}
                                        </Badge>
                                    </td>
                                    <td class="max-w-xs px-4 py-3">
                                        <p
                                            class="truncate text-xs text-muted-foreground"
                                        >
                                            {{ job.exception_summary }}
                                        </p>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-xs text-muted-foreground"
                                    >
                                        {{ formatShortDate(job.failed_at) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div
                                            class="flex items-center justify-end gap-1"
                                        >
                                            <Link
                                                :href="
                                                    failedJobsShow(job.id).url
                                                "
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent"
                                            >
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                            <button
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-md text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/50"
                                                title="Retry"
                                                @click="retryJob(job.uuid)"
                                            >
                                                <RotateCcw class="h-4 w-4" />
                                            </button>
                                            <button
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-md text-destructive hover:bg-destructive/10"
                                                title="Delete"
                                                @click="deleteJob(job.id)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div
                            v-else
                            class="flex flex-col items-center justify-center px-4 py-16"
                        >
                            <div
                                class="rounded-full bg-green-50 p-4 dark:bg-green-950/50"
                            >
                                <RefreshCw
                                    class="h-8 w-8 text-green-600 dark:text-green-400"
                                />
                            </div>
                            <h3 class="mt-4 text-lg font-semibold">
                                No Failed Jobs
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                All queue jobs are running smoothly.
                            </p>
                        </div>
                    </div>
                </div>

                <Pagination
                    v-if="failedJobs.last_page > 1"
                    :current-page="failedJobs.current_page"
                    :last-page="failedJobs.last_page"
                    :from="failedJobs.from"
                    :to="failedJobs.to"
                    :total="failedJobs.total"
                />
            </div>
        </div>
    </AdminLayout>
</template>
