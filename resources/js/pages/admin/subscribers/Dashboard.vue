<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CheckCircle,
    Clock,
    ListIcon,
    Mail,
    RefreshCw,
    UserMinus,
    Users,
} from 'lucide-vue-next';

import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Subscriber {
    id: number;
    email: string;
    first_name: string | null;
    last_name: string | null;
    status: string;
    created_at: string;
}

interface SubscriptionList {
    id: number;
    name: string;
    slug: string;
    is_default: boolean;
    subscribers_count: number;
}

interface Stats {
    total: number;
    active: number;
    pending: number;
    unsubscribed: number;
    lists: number;
}

interface Props {
    stats: Stats;
    recentSubscribers: Subscriber[];
    topLists: SubscriptionList[];
    monthlyGrowth: Record<string, number>;
}

defineProps<Props>();

const getStatusColor = (status: string): string => {
    switch (status) {
        case 'subscribed':
            return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
        case 'unsubscribed':
            return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
    }
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Subscribers', href: '#' },
    { title: 'Dashboard', href: '#' },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Subscriber Dashboard" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        title="Subscriber Dashboard"
                        description="Overview of your email subscribers"
                        variant="small"
                    />
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="router.reload()"
                        >
                            <RefreshCw class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium"
                                >Total Subscribers</CardTitle
                            >
                            <Users class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ stats.total }}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium"
                                >Active</CardTitle
                            >
                            <CheckCircle
                                class="h-4 w-4 text-muted-foreground"
                            />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-green-600">
                                {{ stats.active }}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium"
                                >Pending</CardTitle
                            >
                            <Clock class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-yellow-600">
                                {{ stats.pending }}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium"
                                >Unsubscribed</CardTitle
                            >
                            <UserMinus class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-red-600">
                                {{ stats.unsubscribed }}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between space-y-0 pb-2"
                        >
                            <CardTitle class="text-sm font-medium"
                                >Lists</CardTitle
                            >
                            <ListIcon class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">
                                {{ stats.lists }}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Growth Chart -->
                <Card v-if="Object.keys(monthlyGrowth).length > 0">
                    <CardHeader>
                        <CardTitle>Monthly Growth</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex h-48 items-end gap-2">
                            <div
                                v-for="(count, month) in monthlyGrowth"
                                :key="month"
                                class="flex flex-1 flex-col items-center gap-1"
                            >
                                <div
                                    class="w-full rounded-t bg-primary/80 transition-all hover:bg-primary"
                                    :style="{
                                        height: `${Math.max(8, (count / Math.max(...Object.values(monthlyGrowth))) * 160)}px`,
                                    }"
                                    :title="`${month}: ${count} subscribers`"
                                />
                                <span
                                    class="w-full truncate text-center text-xs text-muted-foreground"
                                >
                                    {{ month }}
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Links & Recent Subscribers -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Quick Links -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Links</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-3">
                                <Link
                                    href="/admin/subscribers/subscribers"
                                    class="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent"
                                >
                                    <Users class="h-5 w-5 text-primary" />
                                    <span class="text-sm font-medium"
                                        >All Subscribers</span
                                    >
                                </Link>
                                <Link
                                    href="/admin/subscribers/lists"
                                    class="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent"
                                >
                                    <ListIcon class="h-5 w-5 text-primary" />
                                    <span class="text-sm font-medium"
                                        >Lists</span
                                    >
                                </Link>
                                <Link
                                    href="/admin/subscribers/subscribers/export"
                                    class="flex items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent"
                                >
                                    <Mail class="h-5 w-5 text-primary" />
                                    <span class="text-sm font-medium"
                                        >Export</span
                                    >
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Top Lists -->
                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between"
                        >
                            <CardTitle>Top Lists</CardTitle>
                            <Link
                                href="/admin/subscribers/lists"
                                class="text-sm text-primary hover:underline"
                            >
                                View all
                            </Link>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="topLists.length === 0"
                                class="py-6 text-center text-muted-foreground"
                            >
                                No lists yet
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="list in topLists"
                                    :key="list.id"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10"
                                        >
                                            <ListIcon
                                                class="h-4 w-4 text-primary"
                                            />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium">
                                                {{ list.name }}
                                            </p>
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                {{ list.subscribers_count }}
                                                subscribers
                                            </p>
                                        </div>
                                    </div>
                                    <Badge
                                        v-if="list.is_default"
                                        variant="secondary"
                                    >
                                        Default
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Recent Subscribers -->
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between"
                    >
                        <CardTitle>Recent Subscribers</CardTitle>
                        <Link
                            href="/admin/subscribers/subscribers"
                            class="text-sm text-primary hover:underline"
                        >
                            View all
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="recentSubscribers.length === 0"
                            class="py-6 text-center text-muted-foreground"
                        >
                            No subscribers yet
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="sub in recentSubscribers"
                                :key="sub.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10"
                                    >
                                        <Mail class="h-4 w-4 text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">
                                            {{ sub.email }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                sub.first_name || sub.last_name
                                                    ? `${sub.first_name || ''} ${sub.last_name || ''}`.trim()
                                                    : 'No name'
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge :class="getStatusColor(sub.status)">
                                        {{ sub.status }}
                                    </Badge>
                                    <span class="text-xs text-muted-foreground">
                                        {{
                                            new Date(
                                                sub.created_at,
                                            ).toLocaleDateString()
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
