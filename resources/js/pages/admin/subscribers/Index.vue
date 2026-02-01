<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Download,
    Eye,
    Mail,
    Search,
    Trash2,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';

import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useSubscriberNav } from '@/composables/useSubscriberNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const { title: moduleTitle, icon: moduleIcon, items: moduleItems } = useSubscriberNav();

interface SubscriptionList {
    id: number;
    name: string;
}

interface Subscriber {
    id: number;
    email: string;
    first_name: string | null;
    last_name: string | null;
    phone: string | null;
    company: string | null;
    status: string;
    tags: string[];
    lists: SubscriptionList[];
    created_at: string;
    confirmed_at: string | null;
}

interface PaginatedSubscribers {
    data: Subscriber[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Filters {
    search?: string;
    status?: string;
    list?: string;
}

interface Props {
    subscribers: PaginatedSubscribers;
    lists: SubscriptionList[];
    filters: Filters;
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const list = ref(props.filters.list || '');

const deleteDialogOpen = ref(false);
const subscriberToDelete = ref<Subscriber | null>(null);

let searchTimeout: ReturnType<typeof setTimeout>;

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

const applyFilters = () => {
    router.get(
        window.location.pathname,
        {
            search: search.value || undefined,
            status: status.value || undefined,
            list: list.value || undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const getStatusColor = (statusValue: string): string => {
    switch (statusValue) {
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

const confirmDelete = (subscriber: Subscriber) => {
    subscriberToDelete.value = subscriber;
    deleteDialogOpen.value = true;
};

const deleteForm = useForm({});

const deleteSubscriber = () => {
    if (!subscriberToDelete.value) return;

    deleteForm.delete(
        `/admin/subscribers/subscribers/${subscriberToDelete.value.id}`,
        {
            onSuccess: () => {
                deleteDialogOpen.value = false;
                subscriberToDelete.value = null;
            },
        },
    );
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Subscribers', href: '/admin/subscribers' },
    { title: 'All Subscribers', href: '#' },
];
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Subscribers" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        title="Subscribers"
                        description="Manage your email subscribers"
                        variant="small"
                    />
                    <Button as="a" href="/admin/subscribers/subscribers/export">
                        <Download class="mr-2 h-4 w-4" />
                        Export
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <div
                            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                        >
                            <CardTitle>All Subscribers</CardTitle>
                            <div class="flex flex-col gap-2 md:flex-row">
                                <div class="relative">
                                    <Search
                                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                    />
                                    <Input
                                        v-model="search"
                                        placeholder="Search subscribers..."
                                        class="w-64 pl-9"
                                    />
                                </div>
                                <select
                                    v-model="status"
                                    class="rounded-md border bg-background px-3 py-2 text-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Status</option>
                                    <option value="subscribed">
                                        Subscribed
                                    </option>
                                    <option value="pending">Pending</option>
                                    <option value="unsubscribed">
                                        Unsubscribed
                                    </option>
                                </select>
                                <select
                                    v-model="list"
                                    class="rounded-md border bg-background px-3 py-2 text-sm"
                                    @change="applyFilters"
                                >
                                    <option value="">All Lists</option>
                                    <option
                                        v-for="l in lists"
                                        :key="l.id"
                                        :value="String(l.id)"
                                    >
                                        {{ l.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th
                                            class="px-4 py-3 text-left text-sm font-semibold"
                                        >
                                            Email
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-sm font-semibold"
                                        >
                                            Name
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-sm font-semibold"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-sm font-semibold"
                                        >
                                            Lists
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-sm font-semibold"
                                        >
                                            Subscribed
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-sm font-semibold"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="subscriber in subscribers.data"
                                        :key="subscriber.id"
                                        class="border-b"
                                    >
                                        <td class="px-4 py-3">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <Mail
                                                    class="h-4 w-4 text-muted-foreground"
                                                />
                                                {{ subscriber.email }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            {{
                                                subscriber.first_name ||
                                                subscriber.last_name
                                                    ? `${subscriber.first_name || ''} ${subscriber.last_name || ''}`.trim()
                                                    : '-'
                                            }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <Badge
                                                :class="
                                                    getStatusColor(
                                                        subscriber.status,
                                                    )
                                                "
                                            >
                                                {{ subscriber.status }}
                                            </Badge>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-1">
                                                <Badge
                                                    v-for="l in subscriber.lists"
                                                    :key="l.id"
                                                    variant="outline"
                                                >
                                                    {{ l.name }}
                                                </Badge>
                                                <span
                                                    v-if="
                                                        subscriber.lists
                                                            .length === 0
                                                    "
                                                    class="text-muted-foreground"
                                                >
                                                    -
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            {{
                                                new Date(
                                                    subscriber.created_at,
                                                ).toLocaleDateString()
                                            }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    as-child
                                                >
                                                    <Link
                                                        :href="`/admin/subscribers/subscribers/${subscriber.id}`"
                                                    >
                                                        <Eye class="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="
                                                        confirmDelete(
                                                            subscriber,
                                                        )
                                                    "
                                                >
                                                    <Trash2
                                                        class="h-4 w-4 text-destructive"
                                                    />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="subscribers.data.length === 0">
                                        <td
                                            colspan="6"
                                            class="px-4 py-8 text-center text-muted-foreground"
                                        >
                                            No subscribers found
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="subscribers.last_page > 1"
                            class="mt-4 flex items-center justify-between"
                        >
                            <p class="text-sm text-muted-foreground">
                                Showing {{ subscribers.data.length }} of
                                {{ subscribers.total }} subscribers
                            </p>
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="subscribers.current_page === 1"
                                    @click="
                                        router.get(
                                            `${window.location.pathname}?page=${subscribers.current_page - 1}`,
                                        )
                                    "
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                </Button>
                                <span class="text-sm">
                                    Page {{ subscribers.current_page }} of
                                    {{ subscribers.last_page }}
                                </span>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="
                                        subscribers.current_page ===
                                        subscribers.last_page
                                    "
                                    @click="
                                        router.get(
                                            `${window.location.pathname}?page=${subscribers.current_page + 1}`,
                                        )
                                    "
                                >
                                    <ChevronRight class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Subscriber</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete
                        <strong>{{ subscriberToDelete?.email }}</strong
                        >? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="deleteDialogOpen = false">
                        Cancel
                    </Button>
                    <Button
                        variant="destructive"
                        :disabled="deleteForm.processing"
                        @click="deleteSubscriber"
                    >
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ModuleLayout>
</template>
