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
    disabledOptions?: string[];
    disabledLabel?: string;
}

const props = withDefaults(defineProps<Props>(), {
    label: undefined,
    error: undefined,
    columns: 1,
    disabledOptions: () => [],
    disabledLabel: '(via role)',
});

const emit = defineEmits<{
    'update:modelValue': [value: string[]];
}>();

const toggleableOptions = computed(() =>
    props.options.filter((o) => !props.disabledOptions.includes(o)),
);

const allSelected = computed(() => {
    return (
        toggleableOptions.value.length > 0 &&
        toggleableOptions.value.every((o) => props.modelValue.includes(o))
    );
});

function toggleAll(): void {
    if (allSelected.value) {
        emit(
            'update:modelValue',
            props.modelValue.filter(
                (v) => !toggleableOptions.value.includes(v),
            ),
        );
    } else {
        const merged = new Set([
            ...props.modelValue,
            ...toggleableOptions.value,
        ]);
        emit('update:modelValue', [...merged]);
    }
}

function toggleOption(option: string): void {
    if (props.disabledOptions.includes(option)) {
        return;
    }
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
        <div
            class="max-h-60 space-y-2 overflow-y-auto rounded-md border p-4"
            :class="gridClass"
        >
            <div
                v-for="option in options"
                :key="option"
                class="flex items-center space-x-2"
                :class="{ 'opacity-50': disabledOptions.includes(option) }"
            >
                <input
                    :id="`${name}-${option}`"
                    type="checkbox"
                    :value="option"
                    :name="`${name}[]`"
                    :checked="
                        modelValue.includes(option) ||
                        disabledOptions.includes(option)
                    "
                    :disabled="disabledOptions.includes(option)"
                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    :class="{
                        'cursor-not-allowed': disabledOptions.includes(option),
                    }"
                    @change="toggleOption(option)"
                />
                <Label
                    :for="`${name}-${option}`"
                    class="text-sm font-medium"
                    :class="{
                        'cursor-not-allowed': disabledOptions.includes(option),
                    }"
                >
                    {{ option }}
                    <span
                        v-if="disabledOptions.includes(option)"
                        class="ml-1 text-xs text-muted-foreground"
                    >
                        {{ disabledLabel }}
                    </span>
                </Label>
            </div>
        </div>
        <InputError v-if="error" :message="error" />
    </div>
</template>
