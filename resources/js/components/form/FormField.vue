<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { HTMLAttributes } from 'vue';

interface Props {
    id: string;
    label: string;
    modelValue?: string | number;
    type?: string;
    error?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    required: false,
    disabled: false,
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
        <Input
            :id="props.id"
            :type="props.type"
            :model-value="props.modelValue"
            :placeholder="props.placeholder"
            :required="props.required"
            :disabled="props.disabled"
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
