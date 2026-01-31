<script setup lang="ts">
import { Search } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface Props {
    searchQuery?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    clearSearch: [];
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12 text-center"
    >
        <Search class="mb-4 h-12 w-12 text-muted-foreground" />
        <h3 class="mb-2 text-lg font-semibold">No results found</h3>
        <p class="mb-4 text-sm text-muted-foreground">
            <template v-if="props.searchQuery">
                No results found for
                <span class="font-medium">"{{ props.searchQuery }}"</span>
            </template>
            <template v-else>
                Try adjusting your search to find what you're looking for
            </template>
        </p>
        <Button variant="outline" @click="emit('clearSearch')">
            Clear search
        </Button>
    </div>
</template>
