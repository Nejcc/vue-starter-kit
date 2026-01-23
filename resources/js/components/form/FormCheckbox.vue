<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import type { HTMLAttributes } from 'vue';

interface Props {
    id: string;
    label: string;
    modelValue?: boolean;
    error?: string;
    description?: string;
    disabled?: boolean;
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    modelValue: false,
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
                :checked="props.modelValue"
                :disabled="props.disabled"
                :aria-invalid="!!props.error"
                :aria-describedby="
                    props.description || props.error
                        ? `${props.id}-description ${props.id}-error`
                        : undefined
                "
                @update:checked="emit('update:modelValue', $event as boolean)"
            />
            <div class="flex flex-col gap-1.5">
                <Label :for="props.id" class="leading-none font-medium">
                    {{ props.label }}
                </Label>
                <p
                    v-if="props.description"
                    :id="`${props.id}-description`"
                    class="text-sm text-muted-foreground"
                >
                    {{ props.description }}
                </p>
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
