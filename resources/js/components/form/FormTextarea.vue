<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { HTMLAttributes } from 'vue';

interface Props {
    id: string;
    label: string;
    modelValue?: string;
    error?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    rows?: number;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    required: false,
    disabled: false,
    rows: 4,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <div :class="props.class">
        <Label :for="props.id">
            {{ props.label }}
            <span v-if="props.required" class="text-destructive">*</span>
        </Label>
        <Textarea
            :id="props.id"
            :model-value="props.modelValue"
            :placeholder="props.placeholder"
            :required="props.required"
            :disabled="props.disabled"
            :rows="props.rows"
            :aria-invalid="!!props.error"
            :aria-describedby="props.error ? `${props.id}-error` : undefined"
            @update:model-value="emit('update:modelValue', $event)"
        />
        <InputError
            v-if="props.error"
            :id="`${props.id}-error`"
            :message="props.error"
        />
    </div>
</template>
