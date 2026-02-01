<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Building2, Check, ChevronsUpDown, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface OrgItem {
    id: number;
    name: string;
    slug: string;
    is_personal: boolean;
}

const page = usePage();

const currentOrganization = computed<OrgItem | null>(() => page.props.currentOrganization as OrgItem | null);
const organizations = computed<OrgItem[]>(() => (page.props.organizations as OrgItem[]) ?? []);

function switchOrganization(org: OrgItem) {
    if (org.id === currentOrganization.value?.id) {
        return;
    }
    router.post(`/organizations/switch/${org.slug}`, {}, {
        preserveState: false,
    });
}
</script>

<template>
    <DropdownMenu v-if="organizations.length > 0">
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                class="w-full justify-between gap-2 px-2"
            >
                <div class="flex items-center gap-2 truncate">
                    <Building2 class="h-4 w-4 shrink-0" />
                    <span class="truncate text-sm">
                        {{ currentOrganization?.name ?? 'Select Organization' }}
                    </span>
                </div>
                <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-56" align="start">
            <DropdownMenuLabel>Organizations</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                v-for="org in organizations"
                :key="org.id"
                class="cursor-pointer"
                @click="switchOrganization(org)"
            >
                <div class="flex w-full items-center justify-between">
                    <span class="truncate">{{ org.name }}</span>
                    <Check
                        v-if="currentOrganization?.id === org.id"
                        class="ml-2 h-4 w-4 shrink-0"
                    />
                </div>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
