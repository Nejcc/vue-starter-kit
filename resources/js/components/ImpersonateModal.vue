<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Impersonate User</DialogTitle>
                <DialogDescription>
                    Search and select a user to impersonate
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4">
                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search by name or email..."
                        class="pl-10"
                        @input="handleSearch"
                    />
                </div>

                <div
                    v-if="isLoading"
                    class="flex items-center justify-center py-8"
                >
                    <div class="text-muted-foreground">Loading users...</div>
                </div>

                <div
                    v-else-if="filteredUsers.length === 0"
                    class="flex items-center justify-center py-8"
                >
                    <div class="text-muted-foreground">No users found</div>
                </div>

                <div
                    v-else
                    class="grid max-h-[400px] grid-cols-1 gap-3 overflow-y-auto"
                >
                    <UserCard
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                        :is-loading="
                            isImpersonating && impersonatingUserId === user.id
                        "
                        @impersonate="handleImpersonate"
                    />
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
// Import shadcn-vue components - adjust path if needed
// If these don't exist, install with: npx shadcn-vue@latest add dialog input
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import UserCard from './UserCard.vue';

interface User {
    id: number;
    name: string;
    email: string;
    initials: string;
}

interface Props {
    open?: boolean;
    users?: User[];
}

const props = withDefaults(defineProps<Props>(), {
    open: false,
    users: () => [],
});

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const searchQuery = ref('');
const isLoading = ref(false);
const localUsers = ref<User[]>(props.users);
const isImpersonating = ref(false);
const impersonatingUserId = ref<number | null>(null);

watch(
    () => props.users,
    (newUsers) => {
        localUsers.value = newUsers;
    },
    { immediate: true },
);

const filteredUsers = computed(() => {
    if (!searchQuery.value.trim()) {
        return localUsers.value;
    }

    const query = searchQuery.value.toLowerCase();
    return localUsers.value.filter(
        (user) =>
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query),
    );
});

const loadUsers = async (search: string = '') => {
    isLoading.value = true;
    try {
        const url = search
            ? `/impersonate?partial=1&search=${encodeURIComponent(search)}`
            : '/impersonate?partial=1';
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (response.ok) {
            const data = await response.json();
            if (data.users) {
                localUsers.value = data.users;
            }
        }
    } catch (error) {
        console.error('Failed to load users:', error);
    } finally {
        isLoading.value = false;
    }
};

const debouncedSearch = useDebounceFn(async (query: string) => {
    if (!query.trim()) {
        await loadUsers();
        return;
    }

    await loadUsers(query);
}, 300);

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        debouncedSearch(searchQuery.value);
    } else {
        loadUsers();
    }
};

const handleImpersonate = (userId: number) => {
    isImpersonating.value = true;
    impersonatingUserId.value = userId;

    router.post(
        '/impersonate',
        { user_id: userId },
        {
            onSuccess: () => {
                isOpen.value = false;
            },
            onFinish: () => {
                isImpersonating.value = false;
                impersonatingUserId.value = null;
            },
        },
    );
};
</script>
