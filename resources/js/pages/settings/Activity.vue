<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Globe,
    KeyRound,
    LogIn,
    LogOut,
    Mail,
    Shield,
    UserCog,
    UserPlus,
} from 'lucide-vue-next';
import { type Component } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

type ActivityLog = {
    id: number;
    event: string;
    description: string;
    ip_address: string | null;
    user_agent: string | null;
    created_at: string;
    created_at_human: string;
};

type PaginatedLogs = {
    data: ActivityLog[];
    current_page: number;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
};

type Props = {
    logs: PaginatedLogs;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Activity',
        href: '/settings/activity',
    },
];

const eventIcons: Record<string, Component> = {
    'auth.login': LogIn,
    'auth.logout': LogOut,
    'auth.registered': UserPlus,
    'auth.password_reset': KeyRound,
    'auth.email_verified': Mail,
    'auth.login_failed': Shield,
    'user.profile_updated': UserCog,
    'user.password_changed': KeyRound,
    'impersonation.started': UserCog,
    'impersonation.stopped': UserCog,
};

function getEventIcon(event: string): Component {
    return eventIcons[event] || Globe;
}

function getEventColor(event: string): string {
    if (event.startsWith('auth.login_failed')) return 'text-red-500';
    if (event.startsWith('auth.login')) return 'text-green-500';
    if (event.startsWith('auth.logout')) return 'text-yellow-500';
    if (event.includes('password')) return 'text-orange-500';
    if (event.includes('verified')) return 'text-blue-500';
    return 'text-muted-foreground';
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Activity" />

        <h1 class="sr-only">Activity Log</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Activity log"
                    description="Recent activity on your account including sign-ins, profile changes, and security events."
                />

                <div class="space-y-1">
                    <div
                        v-for="log in logs.data"
                        :key="log.id"
                        class="flex items-start gap-3 rounded-lg border-b p-3 last:border-b-0"
                    >
                        <div
                            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted"
                        >
                            <component
                                :is="getEventIcon(log.event)"
                                class="h-4 w-4"
                                :class="getEventColor(log.event)"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium">
                                {{ log.description }}
                            </p>
                            <div
                                class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground"
                            >
                                <span>{{ log.created_at_human }}</span>
                                <span v-if="log.ip_address">
                                    {{ log.ip_address }}
                                </span>
                            </div>
                        </div>

                        <Badge variant="outline" class="shrink-0 text-xs">
                            {{ log.event.split('.')[0] }}
                        </Badge>
                    </div>

                    <div
                        v-if="logs.data.length === 0"
                        class="rounded-lg border border-dashed p-8 text-center"
                    >
                        <Globe
                            class="mx-auto h-8 w-8 text-muted-foreground"
                        />
                        <p class="mt-2 text-sm text-muted-foreground">
                            No activity recorded yet.
                        </p>
                    </div>
                </div>

                <div
                    v-if="logs.last_page > 1"
                    class="flex items-center justify-between border-t pt-4"
                >
                    <p class="text-sm text-muted-foreground">
                        Page {{ logs.current_page }} of {{ logs.last_page }}
                    </p>
                    <div class="flex gap-2">
                        <Button
                            v-if="logs.prev_page_url"
                            variant="outline"
                            size="sm"
                            as-child
                        >
                            <Link :href="logs.prev_page_url" preserve-scroll>
                                Previous
                            </Link>
                        </Button>
                        <Button
                            v-if="logs.next_page_url"
                            variant="outline"
                            size="sm"
                            as-child
                        >
                            <Link :href="logs.next_page_url" preserve-scroll>
                                Next
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
