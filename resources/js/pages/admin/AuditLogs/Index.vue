<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Activity, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
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

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedLogs {
    data: AuditLogEntry[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
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

const searchQuery = ref(props.filters.search);
const selectedEvent = ref(props.filters.event);

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Audit Logs', href: '#' },
];

function applyFilters(params: Record<string, string | null>): void {
    router.get(
        route('admin.audit-logs.index'),
        {
            search:
                params.search !== undefined
                    ? params.search
                    : searchQuery.value || null,
            event:
                params.event !== undefined
                    ? params.event
                    : selectedEvent.value || null,
        },
        { preserveState: true, preserveScroll: true },
    );
}

const debouncedSearch = useDebounceFn((query: string) => {
    applyFilters({ search: query || null });
}, 300);

function handleSearch(): void {
    debouncedSearch(searchQuery.value);
}

function handleEventFilter(value: string): void {
    selectedEvent.value = value === 'all' ? '' : value;
    applyFilters({ event: value === 'all' ? null : value });
}

function clearFilters(): void {
    searchQuery.value = '';
    selectedEvent.value = '';
    router.get(route('admin.audit-logs.index'), {}, { preserveState: false });
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatEventName(event: string): string {
    return event.replace(/[._]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

function formatModelType(type: string | null): string {
    if (!type) {
        return '';
    }
    const parts = type.split('\\');
    return parts[parts.length - 1];
}

function goToPage(url: string | null): void {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
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
                        <Input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by event, user, or IP address..."
                            class="w-full"
                            @input="handleSearch"
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
                <div
                    v-if="logs.data.length > 0"
                    class="overflow-hidden rounded-lg border"
                >
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left font-medium">
                                    Event
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    User
                                </th>
                                <th
                                    class="hidden px-4 py-3 text-left font-medium md:table-cell"
                                >
                                    Subject
                                </th>
                                <th
                                    class="hidden px-4 py-3 text-left font-medium md:table-cell"
                                >
                                    IP Address
                                </th>
                                <th class="px-4 py-3 text-left font-medium">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="log in logs.data"
                                :key="log.id"
                                class="hover:bg-muted/30"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Activity
                                            class="h-4 w-4 shrink-0 text-muted-foreground"
                                        />
                                        <span class="font-medium">{{
                                            formatEventName(log.event)
                                        }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div v-if="log.user">
                                        <p class="font-medium">
                                            {{ log.user.name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ log.user.email }}
                                        </p>
                                    </div>
                                    <span v-else class="text-muted-foreground"
                                        >System</span
                                    >
                                </td>
                                <td class="hidden px-4 py-3 md:table-cell">
                                    <span
                                        v-if="log.auditable_type"
                                        class="text-muted-foreground"
                                    >
                                        {{
                                            formatModelType(log.auditable_type)
                                        }}
                                        <span v-if="log.auditable_id"
                                            >#{{ log.auditable_id }}</span
                                        >
                                    </span>
                                    <span v-else class="text-muted-foreground"
                                        >&mdash;</span
                                    >
                                </td>
                                <td class="hidden px-4 py-3 md:table-cell">
                                    <code
                                        v-if="log.ip_address"
                                        class="text-xs"
                                        >{{ log.ip_address }}</code
                                    >
                                    <span v-else class="text-muted-foreground"
                                        >&mdash;</span
                                    >
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ formatDate(log.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

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
                <div
                    v-if="logs.last_page > 1"
                    class="flex items-center justify-between"
                >
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="!logs.prev_page_url"
                        @click="goToPage(logs.prev_page_url)"
                    >
                        <ChevronLeft class="mr-1 h-4 w-4" />
                        Previous
                    </Button>
                    <span class="text-sm text-muted-foreground">
                        Page {{ logs.current_page }} of {{ logs.last_page }}
                    </span>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="!logs.next_page_url"
                        @click="goToPage(logs.next_page_url)"
                    >
                        Next
                        <ChevronRight class="ml-1 h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
