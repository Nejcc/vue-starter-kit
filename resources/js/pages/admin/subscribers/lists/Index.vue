<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Edit,
    Eye,
    ListIcon,
    Plus,
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
import { Textarea } from '@/components/ui/textarea';
import { useSubscriberNav } from '@/composables/useSubscriberNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const { title: moduleTitle, icon: moduleIcon, items: moduleItems } = useSubscriberNav();

interface SubscriptionList {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_public: boolean;
    is_default: boolean;
    double_opt_in: boolean;
    subscribers_count: number;
    active_subscribers_count: number;
    created_at: string;
}

interface PaginatedLists {
    data: SubscriptionList[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    lists: PaginatedLists;
}

const props = defineProps<Props>();

const createDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const listToDelete = ref<SubscriptionList | null>(null);

const createForm = useForm({
    name: '',
    description: '',
    is_public: true,
    is_default: false,
    double_opt_in: true,
    welcome_email_enabled: false,
    welcome_email_subject: '',
    welcome_email_content: '',
});

const createList = () => {
    createForm.post('/admin/subscribers/lists', {
        onSuccess: () => {
            createDialogOpen.value = false;
            createForm.reset();
        },
    });
};

const confirmDelete = (list: SubscriptionList) => {
    listToDelete.value = list;
    deleteDialogOpen.value = true;
};

const deleteForm = useForm({});

const deleteList = () => {
    if (!listToDelete.value) return;

    deleteForm.delete(`/admin/subscribers/lists/${listToDelete.value.id}`, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            listToDelete.value = null;
        },
    });
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Subscribers', href: '/admin/subscribers' },
    { title: 'Lists', href: '#' },
];
</script>

<template>
    <ModuleLayout :breadcrumbs="breadcrumbItems" :module-title="moduleTitle" :module-icon="moduleIcon" :module-items="moduleItems">
        <Head title="Subscription Lists" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        title="Subscription Lists"
                        description="Manage your subscription lists"
                        variant="small"
                    />
                    <Button @click="createDialogOpen = true">
                        <Plus class="mr-2 h-4 w-4" />
                        Create List
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>All Lists</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th
                                            class="px-4 py-3 text-left text-sm font-semibold"
                                        >
                                            Name
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-sm font-semibold"
                                        >
                                            Subscribers
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-sm font-semibold"
                                        >
                                            Active
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-sm font-semibold"
                                        >
                                            Double Opt-In
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-sm font-semibold"
                                        >
                                            Public
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
                                        v-for="list in lists.data"
                                        :key="list.id"
                                        class="border-b"
                                    >
                                        <td class="px-4 py-3">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <ListIcon
                                                    class="h-4 w-4 text-muted-foreground"
                                                />
                                                <span class="font-medium">{{
                                                    list.name
                                                }}</span>
                                                <Badge
                                                    v-if="list.is_default"
                                                    variant="secondary"
                                                >
                                                    Default
                                                </Badge>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ list.subscribers_count }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ list.active_subscribers_count }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <Badge
                                                :variant="
                                                    list.double_opt_in
                                                        ? 'default'
                                                        : 'secondary'
                                                "
                                            >
                                                {{
                                                    list.double_opt_in
                                                        ? 'Yes'
                                                        : 'No'
                                                }}
                                            </Badge>
                                        </td>
                                        <td class="px-4 py-3">
                                            <Badge
                                                :variant="
                                                    list.is_public
                                                        ? 'default'
                                                        : 'secondary'
                                                "
                                            >
                                                {{
                                                    list.is_public
                                                        ? 'Yes'
                                                        : 'No'
                                                }}
                                            </Badge>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    as-child
                                                >
                                                    <Link
                                                        :href="`/admin/subscribers/lists/${list.id}`"
                                                    >
                                                        <Eye class="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    :disabled="list.is_default"
                                                    @click="confirmDelete(list)"
                                                >
                                                    <Trash2
                                                        class="h-4 w-4 text-destructive"
                                                    />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="lists.data.length === 0">
                                        <td
                                            colspan="6"
                                            class="px-4 py-8 text-center text-muted-foreground"
                                        >
                                            No lists found
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="lists.last_page > 1"
                            class="mt-4 flex items-center justify-between"
                        >
                            <p class="text-sm text-muted-foreground">
                                Showing {{ lists.data.length }} of
                                {{ lists.total }} lists
                            </p>
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="lists.current_page === 1"
                                    @click="
                                        router.get(
                                            `${window.location.pathname}?page=${lists.current_page - 1}`,
                                        )
                                    "
                                >
                                    <ChevronLeft class="h-4 w-4" />
                                </Button>
                                <span class="text-sm">
                                    Page {{ lists.current_page }} of
                                    {{ lists.last_page }}
                                </span>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="
                                        lists.current_page === lists.last_page
                                    "
                                    @click="
                                        router.get(
                                            `${window.location.pathname}?page=${lists.current_page + 1}`,
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

        <!-- Create List Dialog -->
        <Dialog v-model:open="createDialogOpen">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Create List</DialogTitle>
                    <DialogDescription>
                        Create a new subscription list to organize your
                        subscribers.
                    </DialogDescription>
                </DialogHeader>
                <form class="space-y-4" @submit.prevent="createList">
                    <div class="space-y-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="createForm.name"
                            placeholder="Newsletter"
                            required
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea
                            id="description"
                            v-model="createForm.description"
                            placeholder="Our main newsletter..."
                            rows="2"
                        />
                    </div>
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="is_public"
                                v-model:checked="createForm.is_public"
                            />
                            <Label for="is_public">Public list</Label>
                        </div>
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="is_default"
                                v-model:checked="createForm.is_default"
                            />
                            <Label for="is_default">Default list</Label>
                        </div>
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="double_opt_in"
                                v-model:checked="createForm.double_opt_in"
                            />
                            <Label for="double_opt_in"
                                >Require double opt-in</Label
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="welcome_email_enabled"
                                v-model:checked="
                                    createForm.welcome_email_enabled
                                "
                            />
                            <Label for="welcome_email_enabled"
                                >Send welcome email</Label
                            >
                        </div>
                    </div>
                    <div
                        v-if="createForm.welcome_email_enabled"
                        class="space-y-4 rounded-lg border p-4"
                    >
                        <div class="space-y-2">
                            <Label for="welcome_email_subject"
                                >Welcome Email Subject</Label
                            >
                            <Input
                                id="welcome_email_subject"
                                v-model="createForm.welcome_email_subject"
                                placeholder="Welcome to our newsletter!"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="welcome_email_content"
                                >Welcome Email Content</Label
                            >
                            <Textarea
                                id="welcome_email_content"
                                v-model="createForm.welcome_email_content"
                                placeholder="Thank you for subscribing..."
                                rows="4"
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="createDialogOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="createForm.processing">
                            Create List
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete List</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete
                        <strong>{{ listToDelete?.name }}</strong
                        >? Subscribers will be removed from this list but not
                        deleted.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="deleteDialogOpen = false">
                        Cancel
                    </Button>
                    <Button
                        variant="destructive"
                        :disabled="deleteForm.processing"
                        @click="deleteList"
                    >
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </ModuleLayout>
</template>
