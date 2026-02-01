<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import { Activity, Building2, CreditCard, Mail, Package, Settings } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Module {
    key: string;
    name: string;
    description: string;
    icon: string;
    package: string;
    installed: boolean;
    adminUrl: string | null;
}

defineProps<{
    modules: Module[];
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Modules', href: '#' },
];

const iconMap: Record<string, LucideIcon> = {
    Activity,
    Building2,
    CreditCard,
    Mail,
    Settings,
};

function getIcon(name: string): LucideIcon {
    return iconMap[name] ?? Package;
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Modules" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col gap-8">
                <Heading title="Modules" description="Installed packages and extensions" variant="small" />

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="mod in modules"
                        :key="mod.key"
                        class="flex flex-col gap-4 rounded-lg border p-6"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    :class="[
                                        mod.installed
                                            ? 'bg-primary/10 text-primary'
                                            : 'bg-muted text-muted-foreground',
                                        'rounded-lg p-2.5',
                                    ]"
                                >
                                    <component :is="getIcon(mod.icon)" class="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 class="font-semibold">{{ mod.name }}</h3>
                                    <p class="text-xs text-muted-foreground">{{ mod.package }}</p>
                                </div>
                            </div>
                            <StatusBadge
                                :label="mod.installed ? 'Installed' : 'Not Installed'"
                                :variant="mod.installed ? 'success' : 'default'"
                            />
                        </div>

                        <p class="text-sm text-muted-foreground">{{ mod.description }}</p>

                        <div class="mt-auto">
                            <Link
                                v-if="mod.installed && mod.adminUrl"
                                :href="mod.adminUrl"
                                class="inline-flex items-center text-sm font-medium text-primary hover:underline"
                            >
                                Manage &rarr;
                            </Link>
                            <span v-else class="text-sm text-muted-foreground">Not available</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
