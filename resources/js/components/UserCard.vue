<template>
    <button
        type="button"
        class="flex w-full items-center gap-3 rounded-lg border border-border bg-card p-4 text-left transition-colors hover:bg-accent hover:text-accent-foreground"
        :disabled="isLoading"
        :class="{ 'cursor-not-allowed opacity-60': isLoading }"
        @click="handleClick"
    >
        <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-medium text-primary-foreground"
        >
            {{ user.initials }}
        </div>
        <div class="min-w-0 flex-1">
            <div class="truncate font-medium">{{ user.name }}</div>
            <div class="truncate text-sm text-muted-foreground">
                {{ user.email }}
            </div>
        </div>
        <Loader2
            v-if="isLoading"
            class="h-4 w-4 shrink-0 animate-spin text-muted-foreground"
        />
        <UserRound v-else class="h-4 w-4 shrink-0 text-muted-foreground" />
    </button>
</template>

<script setup lang="ts">
import { Loader2, UserRound } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    initials: string;
}

interface Props {
    user: User;
    isLoading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isLoading: false,
});

const emit = defineEmits<{
    impersonate: [userId: number];
}>();

const handleClick = () => {
    emit('impersonate', props.user.id);
};
</script>
