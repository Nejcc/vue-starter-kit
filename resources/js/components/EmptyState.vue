<script setup lang="ts">
import type { Component } from 'vue';
import { Button } from '@/components/ui/button';

interface Props {
    icon?: Component;
    title: string;
    description?: string;
    actionText?: string;
    actionHref?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    action: [];
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12 text-center"
    >
        <component
            :is="props.icon"
            v-if="props.icon"
            class="mb-4 h-12 w-12 text-muted-foreground"
        />
        <h3 class="mb-2 text-lg font-semibold">{{ props.title }}</h3>
        <p v-if="props.description" class="mb-4 text-sm text-muted-foreground">
            {{ props.description }}
        </p>
        <Button v-if="props.actionText && props.actionHref" as-child>
            <a :href="props.actionHref">
                {{ props.actionText }}
            </a>
        </Button>
        <Button v-else-if="props.actionText" @click="emit('action')">
            {{ props.actionText }}
        </Button>
    </div>
</template>
