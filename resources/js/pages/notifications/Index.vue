<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Bell,
    Check,
    CheckCheck,
    ChevronLeft,
    ChevronRight,
    ExternalLink,
    Trash2,
} from 'lucide-vue-next';

import {
    destroy,
    index,
    markAllAsRead as markAllAsReadAction,
    markAsRead as markAsReadAction,
} from '@/actions/App/Http/Controllers/NotificationsController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import type { NotificationsPageProps } from '@/types/pages';

defineProps<NotificationsPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Notifications', href: index.url() },
];

const filters = [
    { label: 'All', value: 'all' },
    { label: 'Unread', value: 'unread' },
    { label: 'Read', value: 'read' },
];

function applyFilter(filter: string) {
    router.get(index.url(), filter === 'all' ? {} : { filter }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function markAsRead(id: string) {
    router.patch(markAsReadAction.url(id), {}, { preserveScroll: true });
}

function markAllAsRead() {
    router.post(markAllAsReadAction.url(), {}, { preserveScroll: true });
}

function deleteNotification(id: string) {
    router.delete(destroy.url(id), {
        preserveScroll: true,
    });
}

function goToPage(url: string | null) {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
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

function formatTimeAgo(dateStr: string): string {
    const date = new Date(dateStr);
    const now = new Date();
    const seconds = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (seconds < 60) return 'just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
    if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
    return formatDate(dateStr);
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Notifications" />

        <div class="mx-auto w-full max-w-3xl px-4 py-8">
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <Heading
                        title="Notifications"
                        description="Stay up to date with your latest activity"
                        variant="small"
                    />
                    <Button
                        v-if="unreadCount > 0"
                        variant="outline"
                        size="sm"
                        @click="markAllAsRead"
                    >
                        <CheckCheck class="mr-1.5 h-4 w-4" />
                        Mark all as read
                    </Button>
                </div>

                <!-- Filter Tabs -->
                <div class="flex gap-1 rounded-lg bg-muted p-1">
                    <button
                        v-for="tab in filters"
                        :key="tab.value"
                        class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="
                            filter === tab.value
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'
                        "
                        @click="applyFilter(tab.value)"
                    >
                        {{ tab.label }}
                        <span
                            v-if="tab.value === 'unread' && unreadCount > 0"
                            class="ml-1 text-xs"
                        >
                            ({{ unreadCount }})
                        </span>
                    </button>
                </div>

                <!-- Notification Cards -->
                <div
                    v-if="notifications.data.length > 0"
                    class="flex flex-col gap-2"
                >
                    <div
                        v-for="notification in notifications.data"
                        :key="notification.id"
                        class="group flex items-start gap-4 rounded-lg border p-4 transition-colors"
                        :class="{ 'bg-muted/40': !notification.read_at }"
                    >
                        <div
                            class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full"
                            :class="
                                notification.read_at
                                    ? 'bg-transparent'
                                    : 'bg-primary'
                            "
                        />
                        <div class="min-w-0 flex-1">
                            <p class="font-medium">
                                {{ notification.data.title }}
                            </p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ notification.data.body }}
                            </p>
                            <div class="mt-2 flex items-center gap-3">
                                <span class="text-xs text-muted-foreground">{{
                                    formatTimeAgo(notification.created_at)
                                }}</span>
                                <a
                                    v-if="notification.data.action_url"
                                    :href="notification.data.action_url"
                                    class="inline-flex items-center text-xs text-primary hover:underline"
                                >
                                    View details
                                    <ExternalLink class="ml-1 h-3 w-3" />
                                </a>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <Button
                                v-if="!notification.read_at"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 opacity-0 group-hover:opacity-100"
                                title="Mark as read"
                                @click="markAsRead(notification.id)"
                            >
                                <Check class="h-4 w-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-muted-foreground opacity-0 group-hover:opacity-100 hover:text-destructive"
                                title="Delete"
                                @click="deleteNotification(notification.id)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="rounded-lg border p-12 text-center">
                    <Bell class="mx-auto mb-3 h-8 w-8 text-muted-foreground" />
                    <p class="text-muted-foreground">
                        {{
                            filter === 'unread'
                                ? 'No unread notifications.'
                                : filter === 'read'
                                  ? 'No read notifications.'
                                  : 'No notifications yet.'
                        }}
                    </p>
                </div>

                <!-- Pagination -->
                <div
                    v-if="notifications.last_page > 1"
                    class="flex items-center justify-between"
                >
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="!notifications.prev_page_url"
                        @click="goToPage(notifications.prev_page_url)"
                    >
                        <ChevronLeft class="mr-1 h-4 w-4" />
                        Previous
                    </Button>
                    <span class="text-sm text-muted-foreground">
                        Page {{ notifications.current_page }} of
                        {{ notifications.last_page }}
                    </span>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="!notifications.next_page_url"
                        @click="goToPage(notifications.next_page_url)"
                    >
                        Next
                        <ChevronRight class="ml-1 h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
