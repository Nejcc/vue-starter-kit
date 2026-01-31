<script setup lang="ts">
import { Bell, Monitor, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import NotificationDropdown from '@/components/NotificationDropdown.vue';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useAppearance } from '@/composables/useAppearance';
import type { BreadcrumbItem } from '@/types';
import type { Appearance } from '@/types/ui';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { appearance, updateAppearance } = useAppearance();

const appearanceCycle: Record<Appearance, Appearance> = {
    light: 'dark',
    dark: 'system',
    system: 'light',
};

const appearanceIcon = computed(() => {
    if (appearance.value === 'light') return Sun;
    if (appearance.value === 'dark') return Moon;
    return Monitor;
});

const appearanceLabel = computed(() => {
    if (appearance.value === 'light') return 'Light mode';
    if (appearance.value === 'dark') return 'Dark mode';
    return 'System theme';
});

function cycleAppearance() {
    updateAppearance(appearanceCycle[appearance.value]);
}
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <div class="ml-auto flex items-center gap-1">
            <NotificationDropdown />
            <TooltipProvider :delay-duration="0">
                <Tooltip>
                    <TooltipTrigger>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="group h-9 w-9 cursor-pointer"
                            @click="cycleAppearance"
                        >
                            <span class="sr-only">{{ appearanceLabel }}</span>
                            <component
                                :is="appearanceIcon"
                                class="size-5 opacity-80 group-hover:opacity-100"
                            />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>
                        <p>{{ appearanceLabel }}</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </div>
    </header>
</template>
