<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, Check, CheckCheck, ExternalLink } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useDateFormat } from '@/composables/useDateFormat';
import type { DatabaseNotification } from '@/types';

const page = usePage();
const { formatTimeAgo } = useDateFormat();
const unreadCount = computed(() => page.props.notifications?.unreadCount ?? 0);

const recentNotifications = ref<DatabaseNotification[]>([]);
const loading = ref(false);
const loaded = ref(false);

async function fetchRecent() {
    if (loaded.value || loading.value) return;
    loading.value = true;
    try {
        const response = await fetch(route('notifications.recent'), {
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        recentNotifications.value = data.notifications;
        loaded.value = true;
    } finally {
        loading.value = false;
    }
}

function handleOpen(open: boolean) {
    if (open) {
        loaded.value = false;
        fetchRecent();
    }
}

function markAsRead(id: string) {
    router.patch(
        route('notifications.mark-as-read', { id }),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                const n = recentNotifications.value.find((n) => n.id === id);
                if (n) n.read_at = new Date().toISOString();
            },
        },
    );
}

function markAllAsRead() {
    router.post(
        route('notifications.mark-all-read'),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                recentNotifications.value.forEach((n) => {
                    if (!n.read_at) n.read_at = new Date().toISOString();
                });
            },
        },
    );
}


</script>

<template>
    <DropdownMenu @update:open="handleOpen">
        <TooltipProvider :delay-duration="0">
            <Tooltip>
                <TooltipTrigger as-child>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="group relative h-9 w-9 cursor-pointer"
                        >
                            <Bell
                                class="size-5 opacity-80 group-hover:opacity-100"
                            />
                            <span
                                v-if="unreadCount > 0"
                                class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-bold text-destructive-foreground"
                            >
                                {{ unreadCount > 99 ? '99+' : unreadCount }}
                            </span>
                        </Button>
                    </DropdownMenuTrigger>
                </TooltipTrigger>
                <TooltipContent>
                    <p>Notifications</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>

        <DropdownMenuContent align="end" class="w-80">
            <div class="flex items-center justify-between border-b px-3 py-2">
                <span class="text-sm font-semibold">Notifications</span>
                <Button
                    v-if="unreadCount > 0"
                    variant="ghost"
                    size="sm"
                    class="h-auto px-2 py-1 text-xs"
                    @click="markAllAsRead"
                >
                    <CheckCheck class="mr-1 h-3 w-3" />
                    Mark all read
                </Button>
            </div>

            <div class="max-h-[300px] overflow-y-auto">
                <div
                    v-if="loading"
                    class="px-3 py-6 text-center text-sm text-muted-foreground"
                >
                    Loading...
                </div>
                <div
                    v-else-if="recentNotifications.length === 0"
                    class="px-3 py-6 text-center text-sm text-muted-foreground"
                >
                    No notifications
                </div>
                <template v-else>
                    <div
                        v-for="notification in recentNotifications"
                        :key="notification.id"
                        class="group flex items-start gap-3 border-b px-3 py-2.5 last:border-b-0"
                        :class="{ 'bg-muted/50': !notification.read_at }"
                    >
                        <div
                            class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                            :class="
                                notification.read_at
                                    ? 'bg-transparent'
                                    : 'bg-primary'
                            "
                        />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm leading-tight font-medium">
                                {{ notification.data.title }}
                            </p>
                            <p
                                class="mt-0.5 line-clamp-2 text-xs text-muted-foreground"
                            >
                                {{ notification.data.body }}
                            </p>
                            <div class="mt-1 flex items-center gap-2">
                                <span
                                    class="text-[11px] text-muted-foreground"
                                    >{{
                                        formatTimeAgo(notification.created_at)
                                    }}</span
                                >
                                <a
                                    v-if="notification.data.action_url"
                                    :href="notification.data.action_url"
                                    class="inline-flex items-center text-[11px] text-primary hover:underline"
                                >
                                    View
                                    <ExternalLink class="ml-0.5 h-2.5 w-2.5" />
                                </a>
                            </div>
                        </div>
                        <Button
                            v-if="!notification.read_at"
                            variant="ghost"
                            size="icon"
                            class="h-6 w-6 shrink-0 opacity-0 group-hover:opacity-100"
                            @click.stop="markAsRead(notification.id)"
                        >
                            <Check class="h-3 w-3" />
                        </Button>
                    </div>
                </template>
            </div>

            <div class="border-t px-3 py-2 text-center">
                <Link
                    :href="route('notifications.index')"
                    class="text-xs font-medium text-primary hover:underline"
                >
                    View all notifications
                </Link>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
