<script setup lang="ts">
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
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as databaseIndex } from '@/routes/admin/database';
import { index as permissionsIndex } from '@/routes/admin/permissions';
import { index as rolesIndex } from '@/routes/admin/roles';
import { index as settingsIndex } from '@/routes/admin/settings';
import { index as usersIndex } from '@/routes/admin/users';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Activity, CreditCard, Database, Home, Key, Mail, Settings, Shield, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const modules = computed(() => page.props.modules);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard().url,
            icon: Home,
        },
        {
            title: 'Users',
            href: usersIndex().url,
            icon: Users,
        },
        {
            title: 'Roles',
            href: rolesIndex().url,
            icon: Shield,
        },
        {
            title: 'Permissions',
            href: permissionsIndex().url,
            icon: Key,
        },
        {
            title: 'Database',
            href: databaseIndex().url,
            icon: Database,
        },
    ];

    // Add optional module navigation items
    if (modules.value?.payments) {
        items.push({
            title: 'Payments',
            href: '/admin/payments',
            icon: CreditCard,
        });
    }

    if (modules.value?.subscribers) {
        items.push({
            title: 'Subscribers',
            href: '/admin/subscribers',
            icon: Mail,
        });
    }

    if (modules.value?.horizon) {
        items.push({
            title: 'Horizon',
            href: '/horizon',
            icon: Activity,
        });
    }

    // Settings always at the end
    items.push({
        title: 'Settings',
        href: settingsIndex().url,
        icon: Settings,
    });

    return items;
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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="[]" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
