<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings, Shield, UserRound } from 'lucide-vue-next';
import { computed, nextTick, ref } from 'vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import ImpersonateModal from './ImpersonateModal.vue';
import { logout } from '@/routes';
import { index as adminIndex } from '@/routes/admin';
import { edit } from '@/routes/profile';
import type { User } from '@/types';

type Props = {
    user: User;
};

const props = defineProps<Props>();

const isAdmin = computed(() => {
    const roles = props.user.roles ?? [];
    return roles.includes('admin') || roles.includes('super-admin');
});

const canImpersonate = computed(() => {
    const roles = props.user.roles ?? [];
    const permissions = props.user.permissions ?? [];

    // Check if user is super-admin or has impersonate permission
    return roles.includes('super-admin') || permissions.includes('impersonate');
});

const isModalOpen = ref(false);
const users = ref<
    Array<{ id: number; name: string; email: string; initials: string }>
>([]);

const openModal = async () => {
    // Load users when modal opens
    await loadUsers();

    // Use nextTick to ensure modal opens after any dropdown state changes
    await nextTick();
    isModalOpen.value = true;
};

const loadUsers = async () => {
    try {
        const response = await fetch('/impersonate?partial=1', {
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
                users.value = data.users;
            }
        }
    } catch (error) {
        console.error('Failed to load users:', error);
    }
};

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem v-if="isAdmin" :as-child="true">
            <Link
                class="block w-full cursor-pointer"
                :href="adminIndex().url"
                prefetch
                as="button"
            >
                <Shield class="mr-2 h-4 w-4" />
                Administration
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem
            v-if="canImpersonate"
            @select.prevent="openModal"
            @click.stop="openModal"
        >
            <UserRound class="mr-2 h-4 w-4" />
            Impersonate
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <ImpersonateModal
        v-if="canImpersonate"
        v-model:open="isModalOpen"
        :users="users"
    />
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link
                class="block w-full cursor-pointer"
                :href="logout()"
                @click="handleLogout"
                as="button"
                data-test="logout-button"
            >
                <LogOut class="mr-2 h-4 w-4" />
                Log out
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
</template>
