<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Bell, Check, Send, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import DataTable from '@/components/DataTable.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import SearchInput from '@/components/SearchInput.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useDateFormat } from '@/composables/useDateFormat';
import { useSearch } from '@/composables/useSearch';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import {
    destroy,
    destroyAll,
    index as notificationsIndex,
    markAsRead,
    send,
} from '@/routes/admin/notifications';
import { type BreadcrumbItem } from '@/types';

interface NotificationUser {
    id: number;
    name: string;
    email: string;
}

interface AdminNotification {
    id: string;
    type: string;
    data: {
        title: string;
        body: string;
        action_url?: string | null;
        icon?: string | null;
    };
    read_at: string | null;
    created_at: string;
    notifiable: NotificationUser | null;
}

interface PaginatedNotifications {
    data: AdminNotification[];
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

interface Props {
    notifications: PaginatedNotifications;
    users: NotificationUser[];
    stats: {
        total: number;
        unread: number;
        read: number;
    };
    filters: {
        search: string;
        filter: string;
        user_id: string;
    };
}

const props = defineProps<Props>();
const page = usePage();
const { formatDateTime } = useDateFormat();
const successMessage = computed(() => page.props.flash?.success as string | undefined);

const selectedFilter = ref(props.filters.filter);
const selectedUserId = ref(props.filters.user_id);

const { searchQuery, handleSearch } = useSearch({
    url: notificationsIndex().url,
    extraParams: () => ({
        filter: selectedFilter.value !== 'all' ? selectedFilter.value : null,
        user_id: selectedUserId.value || null,
    }),
});
searchQuery.value = props.filters.search;

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Notifications', href: '#' },
];

const columns = [
    { key: 'status', label: '' },
    { key: 'title', label: 'Notification' },
    { key: 'user', label: 'User' },
    { key: 'type', label: 'Type', hideBelow: 'md' as const },
    { key: 'created_at', label: 'Date' },
    { key: 'actions', label: '' },
];

// Send notification dialog
const showSendDialog = ref(false);
const selectAllUsers = ref(false);

const sendForm = useForm({
    user_ids: [] as number[],
    title: '',
    body: '',
    action_url: '',
});

function toggleSelectAllUsers(): void {
    selectAllUsers.value = !selectAllUsers.value;
    sendForm.user_ids = selectAllUsers.value
        ? props.users.map((u) => u.id)
        : [];
}

function toggleUser(userId: number): void {
    const idx = sendForm.user_ids.indexOf(userId);
    if (idx >= 0) {
        sendForm.user_ids.splice(idx, 1);
    } else {
        sendForm.user_ids.push(userId);
    }
    selectAllUsers.value = sendForm.user_ids.length === props.users.length;
}

function submitSend(): void {
    sendForm.post(send().url, {
        onSuccess: () => {
            showSendDialog.value = false;
            sendForm.reset();
            selectAllUsers.value = false;
        },
    });
}

function handleFilterChange(value: string): void {
    selectedFilter.value = value;
    router.get(
        notificationsIndex().url,
        {
            search: searchQuery.value || null,
            filter: value !== 'all' ? value : null,
            user_id: selectedUserId.value || null,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function handleUserFilter(value: string): void {
    selectedUserId.value = value;
    router.get(
        notificationsIndex().url,
        {
            search: searchQuery.value || null,
            filter:
                selectedFilter.value !== 'all'
                    ? selectedFilter.value
                    : null,
            user_id: value || null,
        },
        { preserveState: true, preserveScroll: true },
    );
}

function handleMarkAsRead(id: string): void {
    router.patch(markAsRead(id).url, {}, { preserveScroll: true });
}

function handleDelete(id: string): void {
    if (confirm('Delete this notification?')) {
        router.delete(destroy(id).url, { preserveScroll: true });
    }
}

function handleDeleteAll(): void {
    if (
        confirm(
            'Delete all read notifications? This cannot be undone.',
        )
    ) {
        router.delete(destroyAll({ mergeQuery: { filter: 'read' } }).url, {
            preserveScroll: true,
        });
    }
}

function clearFilters(): void {
    searchQuery.value = '';
    selectedFilter.value = 'all';
    selectedUserId.value = '';
    router.get(notificationsIndex().url, {}, { preserveState: false });
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Notifications" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <Heading
                        title="Notifications"
                        description="Manage all system notifications"
                        variant="small"
                    />
                    <Button @click="showSendDialog = true">
                        <Send class="mr-2 h-4 w-4" />
                        Send Notification
                    </Button>
                </div>

                <!-- Success Message -->
                <div
                    v-if="successMessage"
                    class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-400"
                >
                    {{ successMessage }}
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="rounded-lg border p-4 text-center">
                        <p class="text-2xl font-bold">{{ stats.total }}</p>
                        <p class="text-sm text-muted-foreground">Total</p>
                    </div>
                    <div class="rounded-lg border p-4 text-center">
                        <p class="text-2xl font-bold text-blue-600">
                            {{ stats.unread }}
                        </p>
                        <p class="text-sm text-muted-foreground">Unread</p>
                    </div>
                    <div class="rounded-lg border p-4 text-center">
                        <p class="text-2xl font-bold text-green-600">
                            {{ stats.read }}
                        </p>
                        <p class="text-sm text-muted-foreground">Read</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex-1">
                        <SearchInput
                            v-model="searchQuery"
                            placeholder="Search notifications or users..."
                            :show-clear="false"
                            @search="handleSearch"
                        />
                    </div>
                    <select
                        :value="selectedFilter"
                        class="h-9 w-[150px] rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                        @change="
                            handleFilterChange(
                                ($event.target as HTMLSelectElement).value,
                            )
                        "
                    >
                        <option value="all">All</option>
                        <option value="unread">Unread</option>
                        <option value="read">Read</option>
                    </select>
                    <select
                        :value="selectedUserId"
                        class="h-9 w-[200px] rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                        @change="
                            handleUserFilter(
                                ($event.target as HTMLSelectElement).value,
                            )
                        "
                    >
                        <option value="">All users</option>
                        <option
                            v-for="user in users"
                            :key="user.id"
                            :value="user.id"
                        >
                            {{ user.name }}
                        </option>
                    </select>
                    <Button
                        v-if="
                            filters.search ||
                            filters.filter !== 'all' ||
                            filters.user_id
                        "
                        variant="outline"
                        @click="clearFilters"
                    >
                        Clear
                    </Button>
                    <Button
                        v-if="stats.read > 0"
                        variant="outline"
                        class="text-destructive hover:text-destructive"
                        @click="handleDeleteAll"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete Read
                    </Button>
                </div>

                <!-- Results count -->
                <p
                    v-if="notifications.total > 0"
                    class="text-sm text-muted-foreground"
                >
                    Showing {{ notifications.from }}&ndash;{{
                        notifications.to
                    }}
                    of {{ notifications.total }} notifications
                </p>

                <!-- Notifications table -->
                <DataTable
                    v-if="notifications.data.length > 0"
                    :columns="columns"
                    :rows="(notifications.data as Record<string, unknown>[])"
                    row-key="id"
                >
                    <template #cell-status="{ row }">
                        <div
                            class="h-2.5 w-2.5 rounded-full"
                            :class="
                                (row as unknown as AdminNotification).read_at
                                    ? 'bg-transparent'
                                    : 'bg-blue-500'
                            "
                        />
                    </template>
                    <template #cell-title="{ row }">
                        <div>
                            <p class="font-medium">
                                {{
                                    (row as unknown as AdminNotification).data
                                        .title
                                }}
                            </p>
                            <p
                                class="max-w-md truncate text-sm text-muted-foreground"
                            >
                                {{
                                    (row as unknown as AdminNotification).data
                                        .body
                                }}
                            </p>
                        </div>
                    </template>
                    <template #cell-user="{ row }">
                        <div
                            v-if="
                                (row as unknown as AdminNotification).notifiable
                            "
                        >
                            <p class="font-medium">
                                {{
                                    (row as unknown as AdminNotification)
                                        .notifiable!.name
                                }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{
                                    (row as unknown as AdminNotification)
                                        .notifiable!.email
                                }}
                            </p>
                        </div>
                        <span v-else class="text-muted-foreground"
                            >Deleted user</span
                        >
                    </template>
                    <template #cell-type="{ row }">
                        <Badge variant="outline">
                            {{
                                (row as unknown as AdminNotification).type
                            }}
                        </Badge>
                    </template>
                    <template #cell-created_at="{ row }">
                        <span class="text-sm text-muted-foreground">
                            {{
                                formatDateTime(
                                    (row as unknown as AdminNotification)
                                        .created_at,
                                )
                            }}
                        </span>
                    </template>
                    <template #cell-actions="{ row }">
                        <div class="flex items-center justify-end gap-1">
                            <Button
                                v-if="
                                    !(row as unknown as AdminNotification)
                                        .read_at
                                "
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8"
                                title="Mark as read"
                                @click="
                                    handleMarkAsRead(
                                        (row as unknown as AdminNotification)
                                            .id,
                                    )
                                "
                            >
                                <Check class="h-4 w-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-muted-foreground hover:text-destructive"
                                title="Delete"
                                @click="
                                    handleDelete(
                                        (row as unknown as AdminNotification)
                                            .id,
                                    )
                                "
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </template>
                </DataTable>

                <!-- Empty state -->
                <div v-else class="rounded-lg border p-12 text-center">
                    <Bell
                        class="mx-auto mb-3 h-8 w-8 text-muted-foreground"
                    />
                    <p class="text-muted-foreground">
                        {{
                            filters.search ||
                            filters.filter !== 'all' ||
                            filters.user_id
                                ? 'No notifications matching your filters.'
                                : 'No notifications yet.'
                        }}
                    </p>
                </div>

                <!-- Pagination -->
                <Pagination :pagination="notifications" />
            </div>
        </div>

        <!-- Send Notification Dialog -->
        <Dialog v-model:open="showSendDialog">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Send Notification</DialogTitle>
                    <DialogDescription>
                        Send a notification to one or more users.
                    </DialogDescription>
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="submitSend">
                    <div class="space-y-2">
                        <Label for="title">Title</Label>
                        <Input
                            id="title"
                            v-model="sendForm.title"
                            placeholder="Notification title"
                            required
                        />
                        <p
                            v-if="sendForm.errors.title"
                            class="text-sm text-destructive"
                        >
                            {{ sendForm.errors.title }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="body">Message</Label>
                        <Textarea
                            id="body"
                            v-model="sendForm.body"
                            placeholder="Notification message..."
                            rows="3"
                            required
                        />
                        <p
                            v-if="sendForm.errors.body"
                            class="text-sm text-destructive"
                        >
                            {{ sendForm.errors.body }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="action_url">Action URL (optional)</Label>
                        <Input
                            id="action_url"
                            v-model="sendForm.action_url"
                            placeholder="/dashboard"
                        />
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label>Recipients</Label>
                            <button
                                type="button"
                                class="text-xs text-primary hover:underline"
                                @click="toggleSelectAllUsers"
                            >
                                {{
                                    selectAllUsers
                                        ? 'Deselect all'
                                        : 'Select all'
                                }}
                            </button>
                        </div>
                        <div
                            class="max-h-40 space-y-2 overflow-y-auto rounded-md border p-3"
                        >
                            <div
                                v-for="user in users"
                                :key="user.id"
                                class="flex items-center gap-2"
                            >
                                <Checkbox
                                    :id="`user-${user.id}`"
                                    :checked="
                                        sendForm.user_ids.includes(user.id)
                                    "
                                    @update:checked="toggleUser(user.id)"
                                />
                                <Label
                                    :for="`user-${user.id}`"
                                    class="text-sm font-normal"
                                >
                                    {{ user.name }}
                                    <span class="text-muted-foreground">
                                        ({{ user.email }})
                                    </span>
                                </Label>
                            </div>
                        </div>
                        <p
                            v-if="sendForm.errors.user_ids"
                            class="text-sm text-destructive"
                        >
                            {{ sendForm.errors.user_ids }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ sendForm.user_ids.length }} user(s) selected
                        </p>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="showSendDialog = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            :disabled="
                                sendForm.processing ||
                                sendForm.user_ids.length === 0
                            "
                        >
                            <Send class="mr-2 h-4 w-4" />
                            Send
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
