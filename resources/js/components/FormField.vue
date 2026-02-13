<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

interface Props {
    label: string;
    id: string;
    error?: string;
    description?: string;
    required?: boolean;
}

withDefaults(defineProps<Props>(), {
    error: undefined,
    description: undefined,
    required: false,
});
</script>

<template>
    <div class="grid gap-2">
        <Label :for="id">
            {{ label }}
            <span v-if="required" class="text-destructive">*</span>
        </Label>
        <slot />
        <InputError v-if="error" :message="error" />
        <p v-if="description" class="text-sm text-muted-foreground">
            {{ description }}
        </p>
    </div>
</template>
