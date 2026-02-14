<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Database,
    Eye,
    HardDrive,
    Map,
    Power,
    RefreshCw,
    Settings,
    Trash2,
    Zap,
} from 'lucide-vue-next';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import StatCard from '@/components/StatCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { index as cacheIndex } from '@/routes/admin/cache';
import { type BreadcrumbItem } from '@/types';

interface CacheItem {
    key: string;
    full_key: string;
    size: number;
    expires_at: string;
    is_expired: boolean;
}

interface Props {
    driver: {
        default: string;
        stores: string[];
        prefix: string;
    };
    stats: {
        items: number;
        expired: number;
        active: number;
    };
    items: CacheItem[];
    maintenance: {
        is_down: boolean;
    };
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Cache & Maintenance', href: cacheIndex().url },
];

const maintenanceSecret = ref('');

function clearCache(): void {
    router.post('/admin/cache/clear', {}, { preserveScroll: true });
}

function clearViews(): void {
    router.post('/admin/cache/clear-views', {}, { preserveScroll: true });
}

function clearRoutes(): void {
    router.post('/admin/cache/clear-routes', {}, { preserveScroll: true });
}

function clearConfig(): void {
    router.post('/admin/cache/clear-config', {}, { preserveScroll: true });
}

function clearAll(): void {
    if (!confirm('Are you sure you want to clear all caches?')) return;
    router.post('/admin/cache/clear-all', {}, { preserveScroll: true });
}

function toggleMaintenance(): void {
    const message = 'Are you sure you want to toggle maintenance mode?';
    if (!confirm(message)) return;
    router.post(
        '/admin/cache/maintenance',
        { secret: maintenanceSecret.value || null },
        { preserveScroll: true },
    );
}

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Cache & Maintenance" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <Heading
                        title="Cache & Maintenance"
                        description="Manage application caches and maintenance mode"
                        variant="small"
                    />
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="clearAll"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Clear All Caches
                    </Button>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <StatCard
                        label="Cache Driver"
                        :value="driver.default"
                        :icon="Database"
                        icon-color="text-blue-600 dark:text-blue-400"
                        icon-bg="bg-blue-50 dark:bg-blue-950/50"
                    />
                    <StatCard
                        label="Cached Items"
                        :value="stats.items"
                        :icon="HardDrive"
                        icon-color="text-green-600 dark:text-green-400"
                        icon-bg="bg-green-50 dark:bg-green-950/50"
                    />
                    <StatCard
                        label="Active Items"
                        :value="stats.active"
                        :icon="Zap"
                        icon-color="text-purple-600 dark:text-purple-400"
                        icon-bg="bg-purple-50 dark:bg-purple-950/50"
                    />
                    <StatCard
                        label="Expired Items"
                        :value="stats.expired"
                        :icon="AlertTriangle"
                        icon-color="text-orange-600 dark:text-orange-400"
                        icon-bg="bg-orange-50 dark:bg-orange-950/50"
                    />
                </div>

                <!-- Cache Actions -->
                <div class="rounded-lg border">
                    <div class="border-b px-6 py-4">
                        <h3 class="font-semibold">Cache Actions</h3>
                        <p class="text-sm text-muted-foreground">
                            Clear specific cache stores
                        </p>
                    </div>
                    <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
                        <button
                            class="flex items-center gap-3 rounded-lg border p-4 text-left transition-colors hover:bg-muted/50"
                            @click="clearCache"
                        >
                            <div
                                class="rounded-lg bg-blue-50 p-2 dark:bg-blue-950/50"
                            >
                                <Database
                                    class="h-5 w-5 text-blue-600 dark:text-blue-400"
                                />
                            </div>
                            <div>
                                <p class="text-sm font-medium">
                                    Application Cache
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    cache:clear
                                </p>
                            </div>
                        </button>
                        <button
                            class="flex items-center gap-3 rounded-lg border p-4 text-left transition-colors hover:bg-muted/50"
                            @click="clearViews"
                        >
                            <div
                                class="rounded-lg bg-green-50 p-2 dark:bg-green-950/50"
                            >
                                <Eye
                                    class="h-5 w-5 text-green-600 dark:text-green-400"
                                />
                            </div>
                            <div>
                                <p class="text-sm font-medium">
                                    Compiled Views
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    view:clear
                                </p>
                            </div>
                        </button>
                        <button
                            class="flex items-center gap-3 rounded-lg border p-4 text-left transition-colors hover:bg-muted/50"
                            @click="clearRoutes"
                        >
                            <div
                                class="rounded-lg bg-purple-50 p-2 dark:bg-purple-950/50"
                            >
                                <Map
                                    class="h-5 w-5 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                            <div>
                                <p class="text-sm font-medium">Route Cache</p>
                                <p class="text-xs text-muted-foreground">
                                    route:clear
                                </p>
                            </div>
                        </button>
                        <button
                            class="flex items-center gap-3 rounded-lg border p-4 text-left transition-colors hover:bg-muted/50"
                            @click="clearConfig"
                        >
                            <div
                                class="rounded-lg bg-orange-50 p-2 dark:bg-orange-950/50"
                            >
                                <Settings
                                    class="h-5 w-5 text-orange-600 dark:text-orange-400"
                                />
                            </div>
                            <div>
                                <p class="text-sm font-medium">
                                    Config Cache
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    config:clear
                                </p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Maintenance Mode -->
                <div class="rounded-lg border">
                    <div class="border-b px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold">Maintenance Mode</h3>
                                <p class="text-sm text-muted-foreground">
                                    Toggle application maintenance mode
                                </p>
                            </div>
                            <Badge
                                :variant="
                                    maintenance.is_down
                                        ? 'destructive'
                                        : 'default'
                                "
                            >
                                {{
                                    maintenance.is_down
                                        ? 'Maintenance'
                                        : 'Live'
                                }}
                            </Badge>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                            <div
                                v-if="!maintenance.is_down"
                                class="flex-1"
                            >
                                <label
                                    for="secret"
                                    class="mb-1 block text-sm font-medium"
                                >
                                    Bypass Secret
                                    <span class="text-muted-foreground">
                                        (optional)
                                    </span>
                                </label>
                                <input
                                    id="secret"
                                    v-model="maintenanceSecret"
                                    type="text"
                                    placeholder="Enter a secret to bypass maintenance mode..."
                                    class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                />
                                <p class="mt-1 text-xs text-muted-foreground">
                                    If set, users can bypass maintenance mode by
                                    visiting /{{ maintenanceSecret || 'secret' }}
                                </p>
                            </div>
                            <Button
                                :variant="
                                    maintenance.is_down
                                        ? 'default'
                                        : 'destructive'
                                "
                                @click="toggleMaintenance"
                            >
                                <Power class="mr-2 h-4 w-4" />
                                {{
                                    maintenance.is_down
                                        ? 'Bring Application Up'
                                        : 'Enable Maintenance Mode'
                                }}
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Cache Items (database driver only) -->
                <div
                    v-if="driver.default === 'database'"
                    class="rounded-lg border"
                >
                    <div class="border-b px-6 py-4">
                        <h3 class="font-semibold">Cached Items</h3>
                        <p class="text-sm text-muted-foreground">
                            Recent items in the database cache (max 50)
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table
                            v-if="items.length > 0"
                            class="w-full text-sm"
                        >
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th
                                        class="px-4 py-3 text-left font-medium"
                                    >
                                        Key
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-medium"
                                    >
                                        Size
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-medium"
                                    >
                                        Expires At
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left font-medium"
                                    >
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="item in items"
                                    :key="item.full_key"
                                    class="hover:bg-muted/30"
                                >
                                    <td class="px-4 py-3">
                                        <p
                                            class="max-w-xs truncate font-mono text-xs"
                                        >
                                            {{ item.key }}
                                        </p>
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-xs text-muted-foreground"
                                    >
                                        {{ formatBytes(item.size) }}
                                    </td>
                                    <td
                                        class="whitespace-nowrap px-4 py-3 text-xs text-muted-foreground"
                                    >
                                        {{ item.expires_at }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge
                                            :variant="
                                                item.is_expired
                                                    ? 'destructive'
                                                    : 'outline'
                                            "
                                        >
                                            {{
                                                item.is_expired
                                                    ? 'Expired'
                                                    : 'Active'
                                            }}
                                        </Badge>
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
                                Cache is Empty
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                No items are currently cached.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Driver info for non-database drivers -->
                <div
                    v-else
                    class="rounded-lg border"
                >
                    <div class="border-b px-6 py-4">
                        <h3 class="font-semibold">Cache Driver Info</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Default Driver
                                </p>
                                <p class="font-mono text-sm">
                                    {{ driver.default }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Cache Prefix
                                </p>
                                <p class="font-mono text-sm">
                                    {{ driver.prefix || '(none)' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Available Stores
                                </p>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <Badge
                                        v-for="store in driver.stores"
                                        :key="store"
                                        variant="outline"
                                    >
                                        {{ store }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-muted-foreground">
                            Cache item browsing is only available when using
                            the database driver. Current driver:
                            <span class="font-medium">{{
                                driver.default
                            }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
