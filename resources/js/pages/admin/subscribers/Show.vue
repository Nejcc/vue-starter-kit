<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle,
    Mail,
    RefreshCw,
    Save,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { useSubscriberNav } from '@/composables/useSubscriberNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useSubscriberNav();

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
    source: string | null;
    ip_address: string | null;
    created_at: string;
    confirmed_at: string | null;
}

interface Props {
    subscriber: Subscriber;
    allLists: SubscriptionList[];
}

const props = defineProps<Props>();

const form = useForm({
    first_name: props.subscriber.first_name || '',
    last_name: props.subscriber.last_name || '',
    phone: props.subscriber.phone || '',
    company: props.subscriber.company || '',
    status: props.subscriber.status,
    tags: props.subscriber.tags || [],
    lists: props.subscriber.lists.map((l) => l.id),
});

const deleteDialogOpen = ref(false);
const newTag = ref('');

const addTag = () => {
    if (newTag.value && !form.tags.includes(newTag.value)) {
        form.tags.push(newTag.value);
        newTag.value = '';
    }
};

const removeTag = (tag: string) => {
    form.tags = form.tags.filter((t) => t !== tag);
};

const toggleList = (listId: number) => {
    if (form.lists.includes(listId)) {
        form.lists = form.lists.filter((id) => id !== listId);
    } else {
        form.lists.push(listId);
    }
};

const saveSubscriber = () => {
    form.put(`/admin/subscribers/subscribers/${props.subscriber.id}`);
};

const confirmSubscriber = () => {
    router.post(
        `/admin/subscribers/subscribers/${props.subscriber.id}/confirm`,
    );
};

const resendConfirmation = () => {
    router.post(`/admin/subscribers/subscribers/${props.subscriber.id}/resend`);
};

const deleteForm = useForm({});

const deleteSubscriber = () => {
    deleteForm.delete(`/admin/subscribers/subscribers/${props.subscriber.id}`, {
        onSuccess: () => {
            router.visit('/admin/subscribers/subscribers');
        },
    });
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
    { title: 'Subscribers', href: '/admin/subscribers/subscribers' },
    { title: props.subscriber.email, href: '#' },
];
</script>

<template>
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head :title="`Subscriber: ${subscriber.email}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="
                                router.visit('/admin/subscribers/subscribers')
                            "
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                        <Heading
                            :title="subscriber.email"
                            description="View and edit subscriber details"
                            variant="small"
                        />
                        <Badge :class="getStatusColor(subscriber.status)">
                            {{ subscriber.status }}
                        </Badge>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="subscriber.status === 'pending'"
                            variant="outline"
                            @click="resendConfirmation"
                        >
                            <RefreshCw class="mr-2 h-4 w-4" />
                            Resend Confirmation
                        </Button>
                        <Button
                            v-if="subscriber.status === 'pending'"
                            variant="outline"
                            @click="confirmSubscriber"
                        >
                            <CheckCircle class="mr-2 h-4 w-4" />
                            Confirm
                        </Button>
                        <Button
                            variant="destructive"
                            @click="deleteDialogOpen = true"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </Button>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Main Form -->
                    <div class="space-y-6 lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Subscriber Details</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <form
                                    class="space-y-4"
                                    @submit.prevent="saveSubscriber"
                                >
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label for="email">Email</Label>
                                            <div
                                                class="flex items-center gap-2 rounded-md border bg-muted/50 px-3 py-2"
                                            >
                                                <Mail
                                                    class="h-4 w-4 text-muted-foreground"
                                                />
                                                <span>{{
                                                    subscriber.email
                                                }}</span>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="status">Status</Label>
                                            <select
                                                v-model="form.status"
                                                class="w-full rounded-md border bg-background px-3 py-2 text-sm"
                                            >
                                                <option value="pending">
                                                    Pending
                                                </option>
                                                <option value="subscribed">
                                                    Subscribed
                                                </option>
                                                <option value="unsubscribed">
                                                    Unsubscribed
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label for="first_name"
                                                >First Name</Label
                                            >
                                            <Input
                                                id="first_name"
                                                v-model="form.first_name"
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="last_name"
                                                >Last Name</Label
                                            >
                                            <Input
                                                id="last_name"
                                                v-model="form.last_name"
                                            />
                                        </div>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label for="phone">Phone</Label>
                                            <Input
                                                id="phone"
                                                v-model="form.phone"
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="company">Company</Label>
                                            <Input
                                                id="company"
                                                v-model="form.company"
                                            />
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label>Tags</Label>
                                        <div class="mb-2 flex flex-wrap gap-2">
                                            <Badge
                                                v-for="tag in form.tags"
                                                :key="tag"
                                                variant="secondary"
                                                class="cursor-pointer"
                                                @click="removeTag(tag)"
                                            >
                                                {{ tag }}
                                                <span class="ml-1"
                                                    >&times;</span
                                                >
                                            </Badge>
                                        </div>
                                        <div class="flex gap-2">
                                            <Input
                                                v-model="newTag"
                                                placeholder="Add tag..."
                                                @keydown.enter.prevent="addTag"
                                            />
                                            <Button
                                                type="button"
                                                variant="outline"
                                                @click="addTag"
                                            >
                                                Add
                                            </Button>
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

                        <Card>
                            <CardHeader>
                                <CardTitle>Lists</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-3">
                                    <div
                                        v-for="list in allLists"
                                        :key="list.id"
                                        class="flex items-center gap-3"
                                    >
                                        <Checkbox
                                            :id="`list-${list.id}`"
                                            :checked="
                                                form.lists.includes(list.id)
                                            "
                                            @update:checked="
                                                toggleList(list.id)
                                            "
                                        />
                                        <Label
                                            :for="`list-${list.id}`"
                                            class="cursor-pointer"
                                        >
                                            {{ list.name }}
                                        </Label>
                                    </div>
                                    <p
                                        v-if="allLists.length === 0"
                                        class="text-muted-foreground"
                                    >
                                        No lists available
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Information</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Subscribed
                                    </p>
                                    <p class="font-medium">
                                        {{
                                            new Date(
                                                subscriber.created_at,
                                            ).toLocaleString()
                                        }}
                                    </p>
                                </div>
                                <div v-if="subscriber.confirmed_at">
                                    <p class="text-sm text-muted-foreground">
                                        Confirmed
                                    </p>
                                    <p class="font-medium">
                                        {{
                                            new Date(
                                                subscriber.confirmed_at,
                                            ).toLocaleString()
                                        }}
                                    </p>
                                </div>
                                <div v-if="subscriber.source">
                                    <p class="text-sm text-muted-foreground">
                                        Source
                                    </p>
                                    <p class="truncate font-medium">
                                        {{ subscriber.source }}
                                    </p>
                                </div>
                                <div v-if="subscriber.ip_address">
                                    <p class="text-sm text-muted-foreground">
                                        IP Address
                                    </p>
                                    <p class="font-medium">
                                        {{ subscriber.ip_address }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Subscriber</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete
                        <strong>{{ subscriber.email }}</strong
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
