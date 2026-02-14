<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ImpersonationBanner from '@/components/ImpersonationBanner.vue';
import ModuleSidebar from '@/components/ModuleSidebar.vue';
import type { BreadcrumbItem, NavItem } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
    moduleTitle: string;
    moduleIcon: LucideIcon;
    moduleItems: NavItem[];
    backHref?: string;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    backHref: '/admin/',
});
</script>

<template>
    <AppShell variant="sidebar">
        <ModuleSidebar
            :title="moduleTitle"
            :icon="moduleIcon"
            :items="moduleItems"
            :back-href="backHref"
        />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <ImpersonationBanner />
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
