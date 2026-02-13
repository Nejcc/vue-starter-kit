<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { PaginatedResponse } from '@/types';

interface Props {
    pagination: PaginatedResponse<unknown>;
}

const props = defineProps<Props>();

function goToPage(url: string | null): void {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
}
</script>

<template>
    <div
        v-if="pagination.last_page > 1"
        class="flex items-center justify-between"
    >
        <Button
            variant="outline"
            size="sm"
            :disabled="!pagination.prev_page_url"
            @click="goToPage(pagination.prev_page_url)"
        >
            <ChevronLeft class="mr-1 h-4 w-4" />
            Previous
        </Button>
        <span class="text-sm text-muted-foreground">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </span>
        <Button
            variant="outline"
            size="sm"
            :disabled="!pagination.next_page_url"
            @click="goToPage(pagination.next_page_url)"
        >
            Next
            <ChevronRight class="ml-1 h-4 w-4" />
        </Button>
    </div>
</template>
