<script setup lang="ts">
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

interface Props {
    modelValue: string[];
    options: string[];
    name: string;
    label?: string;
    error?: string;
    columns?: number;
}

const props = withDefaults(defineProps<Props>(), {
    label: undefined,
    error: undefined,
    columns: 1,
});

const emit = defineEmits<{
    'update:modelValue': [value: string[]];
}>();

const allSelected = computed(() => {
    return props.options.length > 0 && props.modelValue.length === props.options.length;
});

const someSelected = computed(() => {
    return props.modelValue.length > 0 && props.modelValue.length < props.options.length;
});

function toggleAll(): void {
    if (allSelected.value) {
        emit('update:modelValue', []);
    } else {
        emit('update:modelValue', [...props.options]);
    }
}

function toggleOption(option: string): void {
    const current = [...props.modelValue];
    const idx = current.indexOf(option);
    if (idx >= 0) {
        current.splice(idx, 1);
    } else {
        current.push(option);
    }
    emit('update:modelValue', current);
}

const gridClass = computed(() => {
    if (props.columns <= 1) return '';
    return `grid grid-cols-${props.columns} gap-x-4`;
});
</script>

<template>
    <div class="grid gap-2">
        <div v-if="label" class="flex items-center justify-between">
            <Label>{{ label }}</Label>
            <button
                v-if="options.length > 1"
                type="button"
                class="text-xs text-primary hover:underline"
                @click="toggleAll"
            >
                {{ allSelected ? 'Deselect all' : 'Select all' }}
            </button>
        </div>
        <div class="max-h-60 space-y-2 overflow-y-auto rounded-md border p-4" :class="gridClass">
            <div
                v-for="option in options"
                :key="option"
                class="flex items-center space-x-2"
            >
                <input
                    :id="`${name}-${option}`"
                    type="checkbox"
                    :value="option"
                    :name="`${name}[]`"
                    :checked="modelValue.includes(option)"
                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    @change="toggleOption(option)"
                />
                <Label :for="`${name}-${option}`" class="text-sm font-medium">
                    {{ option }}
                </Label>
            </div>
        </div>
        <InputError v-if="error" :message="error" />
    </div>
</template>
