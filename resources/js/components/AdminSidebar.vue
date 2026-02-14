<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Activity,
    AlertTriangle,
    Bell,
    Building2,
    ClipboardList,
    CreditCard,
    Database,
    HardDrive,
    HeartPulse,
    Home,
    Key,
    Languages,
    List,
    Package,
    ScrollText,
    Settings,
    ShieldCheck,
    Users,
    Wrench,
} from 'lucide-vue-next';
import { computed } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarGroup,
    SidebarGroupLabel,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import { index as auditLogsIndex } from '@/routes/admin/audit-logs';
import { index as cacheIndex } from '@/routes/admin/cache';
import { index as databaseIndex } from '@/routes/admin/database';
import { index as failedJobsIndex } from '@/routes/admin/failed-jobs';
import { index as healthIndex } from '@/routes/admin/health';
import { index as logsIndex } from '@/routes/admin/logs';
import { index as modulesIndex } from '@/routes/admin/modules';
import { index as notificationsIndex } from '@/routes/admin/notifications';
import { index as packagesIndex } from '@/routes/admin/packages';
import { index as permissionsIndex } from '@/routes/admin/permissions';
import { index as rolesIndex } from '@/routes/admin/roles';
import { index as usersIndex } from '@/routes/admin/users';
import { type ModuleNavGroupData, type NavGroup } from '@/types';
import AppLogo from './AppLogo.vue';

const page = usePage();
const modules = computed(() => page.props.modules);
const moduleNavigation = computed<ModuleNavGroupData[]>(
    () => page.props.moduleNavigation ?? [],
);
const { isCurrentUrl } = useCurrentUrl();

/**
 * Get the first href from a module navigation group matched by keyword.
 */
function moduleHref(keyword: string, fallback: string): string {
    const group = moduleNavigation.value.find((g) =>
        g.title.toLowerCase().includes(keyword.toLowerCase()),
    );
    return group?.items[0]?.href ?? fallback;
}

const navGroups = computed<NavGroup[]>(() => {
    // --- Static groups ---
    const groups: NavGroup[] = [
        {
            title: 'Access Control',
            icon: ShieldCheck,
            items: [
                { title: 'Users', href: usersIndex().url, icon: Users },
                { title: 'Roles', href: rolesIndex().url, icon: ShieldCheck },
                {
                    title: 'Permissions',
                    href: permissionsIndex().url,
                    icon: Key,
                },
            ],
        },
        {
            title: 'System',
            icon: Wrench,
            items: [
                {
                    title: 'Database',
                    href: databaseIndex().url,
                    icon: Database,
                },
                {
                    title: 'Audit Logs',
                    href: auditLogsIndex().url,
                    icon: ClipboardList,
                },
                {
                    title: 'Application Logs',
                    href: logsIndex().url,
                    icon: ScrollText,
                },
                {
                    title: 'Notifications',
                    href: notificationsIndex().url,
                    icon: Bell,
                },
                {
                    title: 'System Health',
                    href: healthIndex().url,
                    icon: HeartPulse,
                },
                {
                    title: 'Failed Jobs',
                    href: failedJobsIndex().url,
                    icon: AlertTriangle,
                },
                {
                    title: 'Cache & Maintenance',
                    href: cacheIndex().url,
                    icon: HardDrive,
                },
                {
                    title: 'Packages',
                    href: packagesIndex().url,
                    icon: Package,
                },
            ],
        },
    ];

    // --- Modules group ---
    const moduleItems: NavItem[] = [];

    // Global Settings
    if (modules.value?.globalSettings) {
        moduleItems.push({
            title: 'Global Settings',
            href: '/admin/settings',
            icon: Settings,
        });
    } else {
        moduleItems.push({
            title: 'Global Settings',
            href: '#',
            icon: Settings,
            disabled: true,
        });
    }

    // Payment Gateway
    if (modules.value?.payments) {
        moduleItems.push({
            title: 'Payment Gateway',
            href: moduleHref('payment', '/admin/payments'),
            icon: CreditCard,
        });
    } else {
        moduleItems.push({
            title: 'Payment Gateway',
            href: '#',
            icon: CreditCard,
            disabled: true,
        });
    }

    // Subscribers
    if (modules.value?.subscribers) {
        moduleItems.push({
            title: 'Subscribers',
            href: moduleHref('subscriber', '/admin/subscribers'),
            icon: List,
        });
    } else {
        moduleItems.push({
            title: 'Subscribers',
            href: '#',
            icon: List,
            disabled: true,
        });
    }

    // Localization
    if (modules.value?.localizations) {
        moduleItems.push({
            title: 'Localization',
            href: moduleHref('localization', '/admin/localizations/languages'),
            icon: Languages,
        });
    } else {
        moduleItems.push({
            title: 'Localization',
            href: '#',
            icon: Languages,
            disabled: true,
        });
    }

    // Organizations
    if (modules.value?.organizations) {
        moduleItems.push({
            title: 'Organizations',
            href: moduleHref('organization', '/admin/organizations'),
            icon: Building2,
        });
    } else {
        moduleItems.push({
            title: 'Organizations',
            href: '#',
            icon: Building2,
            disabled: true,
        });
    }

    // Horizon
    if (modules.value?.horizon) {
        moduleItems.push({
            title: 'Horizon',
            href: '/horizon',
            icon: Activity,
        });
    } else {
        moduleItems.push({
            title: 'Horizon',
            href: '#',
            icon: Activity,
            disabled: true,
        });
    }

    // "View All" link
    moduleItems.push({
        title: 'View All',
        href: modulesIndex().url,
        icon: Package,
    });

    groups.push({
        title: 'Modules',
        icon: Package,
        items: moduleItems,
    });

    return groups;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()" prefetch>
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Platform</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(dashboard().url)"
                            tooltip="Dashboard"
                        >
                            <Link :href="dashboard().url" prefetch>
                                <Home />
                                <span>Dashboard</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
            <NavMain :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="[]" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
