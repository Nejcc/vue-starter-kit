<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';

interface Props {
    id: string;
    label: string;
    modelValue?: boolean;
    error?: string;
    description?: string;
    disabled?: boolean;
    name?: string;
    required?: boolean;
    tabindex?: number;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    modelValue: false,
    required: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();
</script>

<template>
    <div :class="props.class">
        <div class="flex items-start gap-3">
            <Checkbox
                :id="props.id"
                :name="props.name"
                :checked="props.modelValue"
                :disabled="props.disabled"
                :required="props.required"
                :tabindex="props.tabindex"
                :aria-invalid="!!props.error"
                :aria-describedby="
                    props.description || $slots.description || props.error
                        ? `${props.id}-description ${props.id}-error`
                        : undefined
                "
                @update:checked="emit('update:modelValue', $event as boolean)"
            />
            <div class="flex flex-col gap-1.5">
                <Label :for="props.id" class="leading-none font-medium">
                    {{ props.label }}
                </Label>
                <div
                    v-if="$slots.description || props.description"
                    :id="`${props.id}-description`"
                    class="text-sm text-muted-foreground"
                >
                    <slot name="description">
                        {{ props.description }}
                    </slot>
                </div>
            </div>
        </div>
        <InputError
            v-if="props.error"
            :id="`${props.id}-error`"
            :message="props.error"
            class="mt-2"
        />
    </div>
</template>
