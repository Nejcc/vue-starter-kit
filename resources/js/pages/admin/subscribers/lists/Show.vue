<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ChevronLeft,
    ChevronRight,
    Eye,
    Mail,
    Save,
} from 'lucide-vue-next';

import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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

interface PaginatedSubscribers {
    data: Subscriber[];
    current_page: number;
    last_page: number;
    total: number;
}

interface SubscriptionList {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_public: boolean;
    is_default: boolean;
    double_opt_in: boolean;
    welcome_email_enabled: boolean;
    welcome_email_subject: string | null;
    welcome_email_content: string | null;
    subscribers_count: number;
    active_subscribers_count: number;
    created_at: string;
}

interface Props {
    list: SubscriptionList;
    subscribers: PaginatedSubscribers;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.list.name,
    description: props.list.description || '',
    is_public: props.list.is_public,
    is_default: props.list.is_default,
    double_opt_in: props.list.double_opt_in,
    welcome_email_enabled: props.list.welcome_email_enabled,
    welcome_email_subject: props.list.welcome_email_subject || '',
    welcome_email_content: props.list.welcome_email_content || '',
});

const saveList = () => {
    form.put(`/admin/subscribers/lists/${props.list.id}`);
};

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
    { title: 'Subscribers', href: '/admin/subscribers' },
    { title: 'Lists', href: '/admin/subscribers/lists' },
    { title: props.list.name, href: '#' },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`List: ${list.name}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="router.visit('/admin/subscribers/lists')"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <Heading
                        :title="list.name"
                        description="View and edit list settings"
                        variant="small"
                    />
                    <Badge v-if="list.is_default" variant="secondary">
                        Default
                    </Badge>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Settings Form -->
                    <div class="space-y-6 lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>List Settings</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <form
                                    class="space-y-4"
                                    @submit.prevent="saveList"
                                >
                                    <div class="space-y-2">
                                        <Label for="name">Name</Label>
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            required
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="description"
                                            >Description</Label
                                        >
                                        <Textarea
                                            id="description"
                                            v-model="form.description"
                                            rows="2"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-3">
                                        <div class="flex items-center gap-2">
                                            <Checkbox
                                                id="is_public"
                                                v-model:checked="form.is_public"
                                            />
                                            <Label for="is_public"
                                                >Public list</Label
                                            >
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Checkbox
                                                id="is_default"
                                                v-model:checked="
                                                    form.is_default
                                                "
                                            />
                                            <Label for="is_default"
                                                >Default list</Label
                                            >
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Checkbox
                                                id="double_opt_in"
                                                v-model:checked="
                                                    form.double_opt_in
                                                "
                                            />
                                            <Label for="double_opt_in"
                                                >Require double opt-in</Label
                                            >
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <Checkbox
                                                id="welcome_email_enabled"
                                                v-model:checked="
                                                    form.welcome_email_enabled
                                                "
                                            />
                                            <Label for="welcome_email_enabled"
                                                >Send welcome email</Label
                                            >
                                        </div>
                                    </div>

                                    <div
                                        v-if="form.welcome_email_enabled"
                                        class="space-y-4 rounded-lg border p-4"
                                    >
                                        <div class="space-y-2">
                                            <Label for="welcome_email_subject"
                                                >Welcome Email Subject</Label
                                            >
                                            <Input
                                                id="welcome_email_subject"
                                                v-model="
                                                    form.welcome_email_subject
                                                "
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="welcome_email_content"
                                                >Welcome Email Content</Label
                                            >
                                            <Textarea
                                                id="welcome_email_content"
                                                v-model="
                                                    form.welcome_email_content
                                                "
                                                rows="4"
                                            />
                                        </div>
                                    </div>

                                    <Button
                                        type="submit"
                                        :disabled="form.processing"
                                    >
                                        <Save class="mr-2 h-4 w-4" />
                                        Save Changes
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>

                        <!-- Subscribers in List -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Subscribers</CardTitle>
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
                                                    Joined
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
                                                    {{
                                                        new Date(
                                                            subscriber.created_at,
                                                        ).toLocaleDateString()
                                                    }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 text-right"
                                                >
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        as-child
                                                    >
                                                        <Link
                                                            :href="`/admin/subscribers/subscribers/${subscriber.id}`"
                                                        >
                                                            <Eye
                                                                class="h-4 w-4"
                                                            />
                                                        </Link>
                                                    </Button>
                                                </td>
                                            </tr>
                                            <tr
                                                v-if="
                                                    subscribers.data.length ===
                                                    0
                                                "
                                            >
                                                <td
                                                    colspan="5"
                                                    class="px-4 py-8 text-center text-muted-foreground"
                                                >
                                                    No subscribers in this list
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
                                            :disabled="
                                                subscribers.current_page === 1
                                            "
                                            @click="
                                                router.get(
                                                    `${window.location.pathname}?page=${subscribers.current_page - 1}`,
                                                )
                                            "
                                        >
                                            <ChevronLeft class="h-4 w-4" />
                                        </Button>
                                        <span class="text-sm">
                                            Page
                                            {{ subscribers.current_page }} of
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

                    <!-- Sidebar Stats -->
                    <div class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Statistics</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Total Subscribers
                                    </p>
                                    <p class="text-2xl font-bold">
                                        {{ list.subscribers_count }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Active Subscribers
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-green-600"
                                    >
                                        {{ list.active_subscribers_count }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Created
                                    </p>
                                    <p class="font-medium">
                                        {{
                                            new Date(
                                                list.created_at,
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
