<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ChevronDown,
    ChevronRight,
    FileText,
    Info,
    OctagonAlert,
    ScrollText,
    XCircle,
} from 'lucide-vue-next';
import { type Component, ref } from 'vue';

import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchInput from '@/components/SearchInput.vue';
import { Button } from '@/components/ui/button';
import { useDateFormat } from '@/composables/useDateFormat';
import { useSearch } from '@/composables/useSearch';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface LogEntry {
    id: number;
    timestamp: string;
    environment: string;
    level: string;
    message: string;
    context: string | null;
}

interface LogFile {
    name: string;
    size: number;
    lastModified: string;
}

interface PaginatedLogs {
    data: LogEntry[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

interface LogsPageProps {
    logs: PaginatedLogs;
    levels: string[];
    files: LogFile[];
    filters: {
        search: string;
        level: string;
        file: string;
    };
}

const props = defineProps<LogsPageProps>();
const { formatDateTime } = useDateFormat();

const selectedLevel = ref(props.filters.level);
const selectedFile = ref(props.filters.file);
const expandedRows = ref<Set<number>>(new Set());

const routeUrl = '/admin/logs';

const { searchQuery, handleSearch } = useSearch({
    url: routeUrl,
    extraParams: () => ({
        level: selectedLevel.value || null,
        file: selectedFile.value || null,
    }),
});
searchQuery.value = props.filters.search;

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Application Logs', href: '#' },
];

const levelConfig: Record<string, { icon: Component; color: string; bg: string }> = {
    EMERGENCY: { icon: OctagonAlert, color: 'text-red-600 dark:text-red-400', bg: 'bg-red-100 dark:bg-red-950' },
    ALERT: { icon: OctagonAlert, color: 'text-red-600 dark:text-red-400', bg: 'bg-red-100 dark:bg-red-950' },
    CRITICAL: { icon: XCircle, color: 'text-red-600 dark:text-red-400', bg: 'bg-red-100 dark:bg-red-950' },
    ERROR: { icon: XCircle, color: 'text-red-500 dark:text-red-400', bg: 'bg-red-50 dark:bg-red-950/50' },
    WARNING: { icon: AlertTriangle, color: 'text-yellow-600 dark:text-yellow-400', bg: 'bg-yellow-50 dark:bg-yellow-950/50' },
    NOTICE: { icon: Info, color: 'text-blue-600 dark:text-blue-400', bg: 'bg-blue-50 dark:bg-blue-950/50' },
    INFO: { icon: Info, color: 'text-blue-500 dark:text-blue-400', bg: 'bg-blue-50 dark:bg-blue-950/50' },
    DEBUG: { icon: ScrollText, color: 'text-gray-500 dark:text-gray-400', bg: 'bg-gray-50 dark:bg-gray-900' },
};

function getLevelConfig(level: string) {
    return levelConfig[level] ?? { icon: Info, color: 'text-gray-500', bg: '' };
}

function applyFilter(type: 'level' | 'file', value: string): void {
    if (type === 'level') {
        selectedLevel.value = value === 'all' ? '' : value;
    } else {
        selectedFile.value = value === 'all' ? '' : value;
    }

    router.get(
        routeUrl,
        {
            search: searchQuery.value || null,
            level: type === 'level' ? (value === 'all' ? null : value) : selectedLevel.value || null,
            file: type === 'file' ? (value === 'all' ? null : value) : selectedFile.value || null,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function clearFilters(): void {
    searchQuery.value = '';
    selectedLevel.value = '';
    selectedFile.value = '';
    router.get(routeUrl, {}, { preserveState: false });
}

function toggleRow(id: number): void {
    if (expandedRows.value.has(id)) {
        expandedRows.value.delete(id);
    } else {
        expandedRows.value.add(id);
    }
}

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(1))} ${sizes[i]}`;
}

const hasFilters = props.filters.search || props.filters.level || props.filters.file;
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Application Logs" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col gap-6">
                <Heading
                    title="Application Logs"
                    description="View Laravel application log entries"
                    variant="small"
                />

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex-1">
                        <SearchInput
                            v-model="searchQuery"
                            placeholder="Search log messages..."
                            :show-clear="false"
                            @search="handleSearch"
                        />
                    </div>
                    <select
                        :value="selectedFile || 'all'"
                        class="h-9 w-[220px] rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                        @change="applyFilter('file', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="all">All log files</option>
                        <option
                            v-for="f in files"
                            :key="f.name"
                            :value="f.name"
                        >
                            {{ f.name }} ({{ formatBytes(f.size) }})
                        </option>
                    </select>
                    <select
                        :value="selectedLevel || 'all'"
                        class="h-9 w-[160px] rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                        @change="applyFilter('level', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="all">All levels</option>
                        <option
                            v-for="level in levels"
                            :key="level"
                            :value="level"
                        >
                            {{ level }}
                        </option>
                    </select>
                    <Button
                        v-if="hasFilters"
                        variant="outline"
                        @click="clearFilters"
                    >
                        Clear
                    </Button>
                </div>

                <!-- Results count -->
                <p v-if="logs.total > 0" class="text-sm text-muted-foreground">
                    Showing {{ logs.from }}&ndash;{{ logs.to }} of {{ logs.total }} entries
                </p>

                <!-- Log entries -->
                <div v-if="logs.data.length > 0" class="overflow-hidden rounded-lg border">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="w-8 px-3 py-3"></th>
                                <th class="px-4 py-3 text-left font-medium">Level</th>
                                <th class="px-4 py-3 text-left font-medium">Message</th>
                                <th class="hidden px-4 py-3 text-left font-medium md:table-cell">Environment</th>
                                <th class="px-4 py-3 text-left font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template v-for="entry in logs.data" :key="entry.id">
                                <tr
                                    class="cursor-pointer hover:bg-muted/30"
                                    :class="getLevelConfig(entry.level).bg"
                                    @click="toggleRow(entry.id)"
                                >
                                    <td class="px-3 py-3">
                                        <ChevronRight
                                            v-if="entry.context && !expandedRows.has(entry.id)"
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                        <ChevronDown
                                            v-else-if="entry.context && expandedRows.has(entry.id)"
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <component
                                                :is="getLevelConfig(entry.level).icon"
                                                class="h-4 w-4 shrink-0"
                                                :class="getLevelConfig(entry.level).color"
                                            />
                                            <span
                                                class="text-xs font-semibold uppercase"
                                                :class="getLevelConfig(entry.level).color"
                                            >
                                                {{ entry.level }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="max-w-md truncate px-4 py-3 font-mono text-xs">
                                        {{ entry.message }}
                                    </td>
                                    <td class="hidden px-4 py-3 md:table-cell">
                                        <code class="text-xs">{{ entry.environment }}</code>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">
                                        {{ formatDateTime(entry.timestamp) }}
                                    </td>
                                </tr>
                                <tr v-if="entry.context && expandedRows.has(entry.id)">
                                    <td colspan="5" class="bg-muted/20 px-4 py-4">
                                        <pre class="max-h-96 overflow-auto whitespace-pre-wrap break-all rounded-md bg-muted p-4 font-mono text-xs">{{ entry.context }}</pre>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Empty state -->
                <div v-else class="rounded-lg border p-12 text-center">
                    <FileText class="mx-auto mb-3 h-8 w-8 text-muted-foreground" />
                    <p class="text-muted-foreground">
                        {{ hasFilters ? 'No log entries matching your filters.' : 'No log entries found.' }}
                    </p>
                </div>

                <!-- Pagination -->
                <Pagination :pagination="(logs as any)" />
            </div>
        </div>
    </AdminLayout>
</template>
