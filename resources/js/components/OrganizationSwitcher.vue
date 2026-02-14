<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Building2, Check, ChevronsUpDown, Plus } from 'lucide-vue-next';
import { computed } from 'vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarMenuButton } from '@/components/ui/sidebar';

interface OrgItem {
    id: number;
    name: string;
    slug: string;
    is_personal: boolean;
}

const page = usePage();

const currentOrganization = computed<OrgItem | null>(
    () => page.props.currentOrganization as OrgItem | null,
);
const organizations = computed<OrgItem[]>(
    () => (page.props.organizations as OrgItem[]) ?? [],
);

function switchOrganization(org: OrgItem) {
    if (org.id === currentOrganization.value?.id) {
        return;
    }
    router.post(
        `/organizations/switch/${org.slug}`,
        {},
        {
            preserveState: false,
        },
    );
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <SidebarMenuButton
                size="lg"
                class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
            >
                <div
                    class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
                >
                    <AppLogoIcon
                        class="size-5 fill-current text-white dark:text-black"
                    />
                </div>
                <div class="ml-1 grid flex-1 text-left text-sm leading-tight">
                    <span class="truncate font-semibold">{{
                        currentOrganization?.name ?? 'Select Organization'
                    }}</span>
                </div>
                <ChevronsUpDown class="ml-auto h-4 w-4 shrink-0" />
            </SidebarMenuButton>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            class="w-[--reka-dropdown-menu-trigger-width] min-w-56"
            align="start"
        >
            <DropdownMenuLabel>Organizations</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                v-for="org in organizations"
                :key="org.id"
                class="cursor-pointer"
                @click="switchOrganization(org)"
            >
                <div class="flex w-full items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Building2
                            class="h-4 w-4 shrink-0 text-muted-foreground"
                        />
                        <span class="truncate">{{ org.name }}</span>
                    </div>
                    <Check
                        v-if="currentOrganization?.id === org.id"
                        class="ml-2 h-4 w-4 shrink-0"
                    />
                </div>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
                <Link
                    href="/organizations/create"
                    class="flex cursor-pointer items-center gap-2"
                >
                    <Plus class="h-4 w-4" />
                    <span>Create Organization</span>
                </Link>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
