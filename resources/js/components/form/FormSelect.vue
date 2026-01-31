<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface SelectOption {
    value: string;
    label: string;
}

interface Props {
    id: string;
    label: string;
    modelValue?: string;
    options: SelectOption[];
    error?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    required: false,
    disabled: false,
    placeholder: 'Select an option',
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
        <Select
            :model-value="props.modelValue"
            :disabled="props.disabled"
            @update:model-value="emit('update:modelValue', $event)"
        >
            <SelectTrigger
                :id="props.id"
                :aria-invalid="!!props.error"
                :aria-describedby="
                    props.error ? `${props.id}-error` : undefined
                "
            >
                <SelectValue :placeholder="props.placeholder" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in props.options"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError
            v-if="props.error"
            :id="`${props.id}-error`"
            :message="props.error"
        />
    </div>
</template>
