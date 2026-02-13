<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Activity } from 'lucide-vue-next';
import { ref } from 'vue';

import DataTable from '@/components/DataTable.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchInput from '@/components/SearchInput.vue';
import { Button } from '@/components/ui/button';
import { useDateFormat } from '@/composables/useDateFormat';
import { useSearch } from '@/composables/useSearch';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { index as auditLogsIndex } from '@/routes/admin/audit-logs';
import { type BreadcrumbItem } from '@/types';

interface AuditUser {
    id: number;
    name: string;
    email: string;
}

interface AuditLogEntry {
    id: number;
    user_id: number | null;
    event: string;
    auditable_type: string | null;
    auditable_id: number | null;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    ip_address: string | null;
    user_agent: string | null;
    created_at: string;
    user: AuditUser | null;
}

interface PaginatedLogs {
    data: AuditLogEntry[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{ url: string | null; label: string; active: boolean }>;
    first_page_url: string;
    last_page_url: string;
    next_page_url: string | null;
    prev_page_url: string | null;
}

interface AuditLogsPageProps {
    logs: PaginatedLogs;
    eventTypes: string[];
    filters: {
        search: string;
        event: string;
    };
}

const props = defineProps<AuditLogsPageProps>();
const { formatDateTime } = useDateFormat();

const selectedEvent = ref(props.filters.event);

const { searchQuery, handleSearch } = useSearch({
    url: auditLogsIndex().url,
    extraParams: () => ({ event: selectedEvent.value || null }),
});
searchQuery.value = props.filters.search;

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Audit Logs', href: '#' },
];

const columns = [
    { key: 'event', label: 'Event' },
    { key: 'user', label: 'User' },
    { key: 'subject', label: 'Subject', hideBelow: 'md' as const },
    { key: 'ip_address', label: 'IP Address', hideBelow: 'md' as const },
    { key: 'created_at', label: 'Date' },
];

function handleEventFilter(value: string): void {
    selectedEvent.value = value === 'all' ? '' : value;
    router.get(
        auditLogsIndex().url,
        {
            search: searchQuery.value || null,
            event: value === 'all' ? null : value,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function clearFilters(): void {
    searchQuery.value = '';
    selectedEvent.value = '';
    router.get(auditLogsIndex().url, {}, { preserveState: false });
}

function formatEventName(event: string): string {
    return event.replace(/[._]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function formatModelType(type: string | null): string {
    if (!type) return '';
    const parts = type.split('\\');
    return parts[parts.length - 1];
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Audit Logs" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col gap-6">
                <Heading
                    title="Audit Logs"
                    description="View all tracked activity and changes"
                    variant="small"
                />

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex-1">
                        <SearchInput
                            v-model="searchQuery"
                            placeholder="Search by event, user, or IP address..."
                            :show-clear="false"
                            @search="handleSearch"
                        />
                    </div>
                    <select
                        :value="selectedEvent || 'all'"
                        class="h-9 w-[200px] rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                        @change="
                            handleEventFilter(
                                ($event.target as HTMLSelectElement).value,
                            )
                        "
                    >
                        <option value="all">All events</option>
                        <option
                            v-for="event in eventTypes"
                            :key="event"
                            :value="event"
                        >
                            {{ formatEventName(event) }}
                        </option>
                    </select>
                    <Button
                        v-if="filters.search || filters.event"
                        variant="outline"
                        @click="clearFilters"
                    >
                        Clear
                    </Button>
                </div>

                <!-- Results count -->
                <p v-if="logs.total > 0" class="text-sm text-muted-foreground">
                    Showing {{ logs.from }}–{{ logs.to }} of
                    {{ logs.total }} entries
                </p>

                <!-- Logs table -->
                <DataTable
                    v-if="logs.data.length > 0"
                    :columns="columns"
                    :rows="(logs.data as Record<string, unknown>[])"
                    row-key="id"
                >
                    <template #cell-event="{ row }">
                        <div class="flex items-center gap-2">
                            <Activity
                                class="h-4 w-4 shrink-0 text-muted-foreground"
                            />
                            <span class="font-medium">{{
                                formatEventName(row.event as string)
                            }}</span>
                        </div>
                    </template>
                    <template #cell-user="{ row }">
                        <div v-if="(row as any).user">
                            <p class="font-medium">
                                {{ (row as any).user.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ (row as any).user.email }}
                            </p>
                        </div>
                        <span v-else class="text-muted-foreground">System</span>
                    </template>
                    <template #cell-subject="{ row }">
                        <span
                            v-if="row.auditable_type"
                            class="text-muted-foreground"
                        >
                            {{ formatModelType(row.auditable_type as string | null) }}
                            <span v-if="row.auditable_id">#{{ row.auditable_id }}</span>
                        </span>
                        <span v-else class="text-muted-foreground">&mdash;</span>
                    </template>
                    <template #cell-ip_address="{ row }">
                        <code v-if="row.ip_address" class="text-xs">{{ row.ip_address }}</code>
                        <span v-else class="text-muted-foreground">&mdash;</span>
                    </template>
                    <template #cell-created_at="{ row }">
                        <span class="text-muted-foreground">
                            {{ formatDateTime(row.created_at as string) }}
                        </span>
                    </template>
                </DataTable>

                <!-- Empty state -->
                <div v-else class="rounded-lg border p-12 text-center">
                    <Activity
                        class="mx-auto mb-3 h-8 w-8 text-muted-foreground"
                    />
                    <p class="text-muted-foreground">
                        {{
                            filters.search || filters.event
                                ? 'No audit logs matching your filters.'
                                : 'No audit logs recorded yet.'
                        }}
                    </p>
                </div>

                <!-- Pagination -->
                <Pagination :pagination="logs" />
            </div>
        </div>
    </AdminLayout>
</template>
