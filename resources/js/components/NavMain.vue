<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { type NavGroup, type NavItem } from '@/types';

defineProps<{
    items?: NavItem[];
    groups?: NavGroup[];
}>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <!-- Grouped mode: collapsible sections -->
    <SidebarGroup v-if="groups" class="px-2 py-0">
        <SidebarMenu>
            <Collapsible
                v-for="group in groups"
                :key="group.title"
                as-child
                :default-open="true"
            >
                <SidebarMenuItem>
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton :tooltip="group.title">
                            <component :is="group.icon" v-if="group.icon" />
                            <span>{{ group.title }}</span>
                            <ChevronRight
                                class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                            />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenuSub>
                            <template
                                v-for="item in group.items"
                                :key="item.title"
                            >
                                <!-- Item with children: nested collapsible -->
                                <SidebarMenuSubItem
                                    v-if="item.children?.length"
                                >
                                    <Collapsible as-child :default-open="false">
                                        <div>
                                            <CollapsibleTrigger as-child>
                                                <SidebarMenuSubButton
                                                    class="cursor-pointer"
                                                >
                                                    <component
                                                        :is="item.icon"
                                                        v-if="item.icon"
                                                        class="size-4 shrink-0"
                                                    />
                                                    <span>{{
                                                        item.title
                                                    }}</span>
                                                    <ChevronRight
                                                        class="ml-auto size-3 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                                    />
                                                </SidebarMenuSubButton>
                                            </CollapsibleTrigger>
                                            <CollapsibleContent>
                                                <SidebarMenuSub>
                                                    <SidebarMenuSubItem
                                                        v-for="child in item.children"
                                                        :key="child.title"
                                                    >
                                                        <SidebarMenuSubButton
                                                            as-child
                                                            :is-active="
                                                                isCurrentUrl(
                                                                    child.href,
                                                                )
                                                            "
                                                        >
                                                            <Link
                                                                :href="
                                                                    child.href
                                                                "
                                                                prefetch
                                                            >
                                                                <component
                                                                    :is="
                                                                        child.icon
                                                                    "
                                                                    v-if="
                                                                        child.icon
                                                                    "
                                                                    class="size-4 shrink-0"
                                                                />
                                                                <span>{{
                                                                    child.title
                                                                }}</span>
                                                            </Link>
                                                        </SidebarMenuSubButton>
                                                    </SidebarMenuSubItem>
                                                </SidebarMenuSub>
                                            </CollapsibleContent>
                                        </div>
                                    </Collapsible>
                                </SidebarMenuSubItem>

                                <!-- Disabled item: muted text, no link -->
                                <SidebarMenuSubItem v-else-if="item.disabled">
                                    <SidebarMenuSubButton
                                        class="pointer-events-none opacity-40"
                                    >
                                        <component
                                            :is="item.icon"
                                            v-if="item.icon"
                                            class="size-4 shrink-0"
                                        />
                                        <span>{{ item.title }}</span>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>

                                <!-- Regular item: simple link -->
                                <SidebarMenuSubItem v-else>
                                    <SidebarMenuSubButton
                                        as-child
                                        :is-active="isCurrentUrl(item.href)"
                                    >
                                        <Link :href="item.href" prefetch>
                                            <component
                                                :is="item.icon"
                                                v-if="item.icon"
                                                class="size-4 shrink-0"
                                            />
                                            <span>{{ item.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </template>
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </Collapsible>
        </SidebarMenu>
    </SidebarGroup>

    <!-- Flat mode: simple list (backward compatible) -->
    <SidebarGroup v-else-if="items" class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href" prefetch>
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
