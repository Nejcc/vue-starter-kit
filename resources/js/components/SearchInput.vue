<script setup lang="ts">
import { Search, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface Props {
    modelValue: string;
    placeholder?: string;
    showClear?: boolean;
}

withDefaults(defineProps<Props>(), {
    placeholder: 'Search...',
    showClear: true,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
    search: [];
    clear: [];
}>();

function onInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', target.value);
    emit('search');
}

function onClear(): void {
    emit('update:modelValue', '');
    emit('clear');
}
</script>

<template>
    <div class="flex items-center gap-4">
        <div class="relative flex-1">
            <Search
                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                :value="modelValue"
                type="text"
                :placeholder="placeholder"
                class="w-full pl-9"
                @input="onInput"
            />
        </div>
        <Button
            v-if="showClear && modelValue"
            variant="outline"
            size="sm"
            @click="onClear"
        >
            <X class="mr-1 h-3 w-3" />
            Clear
        </Button>
    </div>
</template>
