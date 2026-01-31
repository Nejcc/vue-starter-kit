<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Props {
    id: string;
    label: string;
    modelValue?: string | number;
    type?: string;
    error?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    name?: string;
    autofocus?: boolean;
    autocomplete?: string;
    tabindex?: number;
    readonly?: boolean;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    required: false,
    disabled: false,
    readonly: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <div :class="props.class">
        <div
            :class="
                $slots['label-end']
                    ? 'flex items-center justify-between'
                    : undefined
            "
        >
            <Label :for="props.id">
                {{ props.label }}
                <span v-if="props.required" class="text-destructive">*</span>
            </Label>
            <slot name="label-end" />
        </div>
        <Input
            :id="props.id"
            :type="props.type"
            :name="props.name"
            :model-value="props.modelValue"
            :placeholder="props.placeholder"
            :required="props.required"
            :disabled="props.disabled"
            :autofocus="props.autofocus"
            :autocomplete="props.autocomplete"
            :tabindex="props.tabindex"
            :readonly="props.readonly"
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
