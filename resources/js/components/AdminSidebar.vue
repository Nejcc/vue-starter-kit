<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Activity,
    Blocks,
    ClipboardList,
    CreditCard,
    Database,
    Home,
    Key,
    Mail,
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
import { index as databaseIndex } from '@/routes/admin/database';
import { index as permissionsIndex } from '@/routes/admin/permissions';
import { index as rolesIndex } from '@/routes/admin/roles';
import { index as usersIndex } from '@/routes/admin/users';
import { type NavGroup } from '@/types';
import AppLogo from './AppLogo.vue';

const page = usePage();
const modules = computed(() => page.props.modules);
const { isCurrentUrl } = useCurrentUrl();

const navGroups = computed<NavGroup[]>(() => {
    const groups: NavGroup[] = [
        {
            title: 'Access Control',
            icon: ShieldCheck,
            items: [
                {
                    title: 'Users',
                    href: usersIndex().url,
                    icon: Users,
                },
                {
                    title: 'Roles',
                    href: rolesIndex().url,
                    icon: ShieldCheck,
                },
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
                ...(modules.value?.globalSettings
                    ? [
                          {
                              title: 'Settings',
                              href: '/admin/settings',
                              icon: Settings,
                          },
                      ]
                    : []),
            ],
        },
    ];

    // Add Modules group if any module is enabled
    const moduleItems = [];
    if (modules.value?.payments) {
        moduleItems.push({
            title: 'Payments',
            href: '/admin/payments',
            icon: CreditCard,
        });
    }
    if (modules.value?.subscribers) {
        moduleItems.push({
            title: 'Subscribers',
            href: '/admin/subscribers',
            icon: Mail,
        });
    }
    if (modules.value?.horizon) {
        moduleItems.push({
            title: 'Horizon',
            href: '/horizon',
            icon: Activity,
        });
    }

    if (moduleItems.length > 0) {
        groups.push({
            title: 'Modules',
            icon: Blocks,
            items: moduleItems,
        });
    }

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
            <!-- Dashboard as standalone top-level item -->
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

            <!-- Collapsible nav groups -->
            <NavMain :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="[]" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
