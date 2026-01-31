<script setup lang="ts">
import { Loader2 } from 'lucide-vue-next';
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    size?: 'sm' | 'md' | 'lg';
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
});

const sizeClasses = {
    sm: 'h-4 w-4',
    md: 'h-8 w-8',
    lg: 'h-12 w-12',
};
</script>

<template>
    <Loader2
        :class="
            cn(
                'animate-spin text-muted-foreground',
                sizeClasses[props.size],
                props.class,
            )
        "
    />
</template>
