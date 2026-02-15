<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle,
    Clock,
    Globe,
    HardDrive,
    Info,
    RefreshCw,
    Server,
    XCircle,
} from 'lucide-vue-next';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface HealthCheck {
    name: string;
    status: 'ok' | 'warning' | 'error';
    message: string;
    details: Record<string, unknown>;
}

interface SystemInfo {
    php_version: string;
    laravel_version: string;
    environment: string;
    debug_mode: boolean;
    timezone: string;
    locale: string;
    server_time: string;
}

interface Props {
    checks: HealthCheck[];
    system: SystemInfo;
}

defineProps<Props>();

const refreshing = ref(false);

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'System Health', href: '#' },
];

function refresh(): void {
    refreshing.value = true;
    router.reload({
        onFinish: () => {
            refreshing.value = false;
        },
    });
}

function statusIcon(status: string) {
    switch (status) {
        case 'ok':
            return CheckCircle;
        case 'warning':
            return AlertTriangle;
        case 'error':
            return XCircle;
        default:
            return Info;
    }
}

function statusColor(status: string): string {
    switch (status) {
        case 'ok':
            return 'text-green-600 dark:text-green-400';
        case 'warning':
            return 'text-yellow-600 dark:text-yellow-400';
        case 'error':
            return 'text-red-600 dark:text-red-400';
        default:
            return 'text-muted-foreground';
    }
}

function statusBadgeVariant(
    status: string,
): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'ok':
            return 'default';
        case 'warning':
            return 'secondary';
        case 'error':
            return 'destructive';
        default:
            return 'outline';
    }
}

function statusLabel(status: string): string {
    switch (status) {
        case 'ok':
            return 'Healthy';
        case 'warning':
            return 'Warning';
        case 'error':
            return 'Error';
        default:
            return 'Unknown';
    }
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="System Health" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <Heading
                        title="System Health"
                        description="Monitor your application's health status"
                        variant="small"
                    />
                    <Button
                        variant="outline"
                        :disabled="refreshing"
                        @click="refresh"
                    >
                        <RefreshCw
                            class="mr-2 h-4 w-4"
                            :class="{ 'animate-spin': refreshing }"
                        />
                        Refresh
                    </Button>
                </div>

                <!-- Health Checks -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="check in checks"
                        :key="check.name"
                        class="rounded-lg border p-6"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <component
                                    :is="statusIcon(check.status)"
                                    class="h-5 w-5"
                                    :class="statusColor(check.status)"
                                />
                                <h3 class="font-semibold">{{ check.name }}</h3>
                            </div>
                            <Badge :variant="statusBadgeVariant(check.status)">
                                {{ statusLabel(check.status) }}
                            </Badge>
                        </div>
                        <p class="mt-3 text-sm text-muted-foreground">
                            {{ check.message }}
                        </p>
                        <div
                            v-if="Object.keys(check.details).length > 0"
                            class="mt-3 space-y-1"
                        >
                            <div
                                v-for="(value, key) in check.details"
                                :key="key"
                                class="flex items-center justify-between text-xs"
                            >
                                <span class="text-muted-foreground">
                                    {{
                                        String(key)
                                            .replace(/_/g, ' ')
                                            .replace(/\b\w/g, (c) =>
                                                c.toUpperCase(),
                                            )
                                    }}
                                </span>
                                <span class="font-mono">{{ value }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="rounded-lg border">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center gap-2">
                            <Server class="h-5 w-5 text-muted-foreground" />
                            <h3 class="font-semibold">System Information</h3>
                        </div>
                    </div>
                    <div class="grid gap-4 p-6 md:grid-cols-2 lg:grid-cols-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-blue-50 p-2 dark:bg-blue-950/50"
                            >
                                <Server
                                    class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                />
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    PHP Version
                                </p>
                                <p class="text-sm font-medium">
                                    {{ system.php_version }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-red-50 p-2 dark:bg-red-950/50"
                            >
                                <HardDrive
                                    class="h-4 w-4 text-red-600 dark:text-red-400"
                                />
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Laravel Version
                                </p>
                                <p class="text-sm font-medium">
                                    {{ system.laravel_version }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-green-50 p-2 dark:bg-green-950/50"
                            >
                                <Globe
                                    class="h-4 w-4 text-green-600 dark:text-green-400"
                                />
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Environment
                                </p>
                                <p class="text-sm font-medium">
                                    {{ system.environment }}
                                    <Badge
                                        v-if="system.debug_mode"
                                        variant="destructive"
                                        class="ml-1"
                                    >
                                        DEBUG
                                    </Badge>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-purple-50 p-2 dark:bg-purple-950/50"
                            >
                                <Clock
                                    class="h-4 w-4 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Timezone
                                </p>
                                <p class="text-sm font-medium">
                                    {{ system.timezone }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-orange-50 p-2 dark:bg-orange-950/50"
                            >
                                <Globe
                                    class="h-4 w-4 text-orange-600 dark:text-orange-400"
                                />
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Locale
                                </p>
                                <p class="text-sm font-medium">
                                    {{ system.locale }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-gray-50 p-2 dark:bg-gray-950/50"
                            >
                                <Clock
                                    class="h-4 w-4 text-gray-600 dark:text-gray-400"
                                />
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Server Time
                                </p>
                                <p class="text-sm font-medium">
                                    {{ system.server_time }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
