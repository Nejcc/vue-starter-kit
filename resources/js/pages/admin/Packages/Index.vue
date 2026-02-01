<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import { Building2, CreditCard, ExternalLink, Languages, Mail, Package, Settings } from 'lucide-vue-next';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Switch } from '@/components/ui/switch';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { update as packagesUpdate } from '@/routes/admin/packages';
import { type BreadcrumbItem } from '@/types';

interface PackageItem {
    key: string;
    name: string;
    description: string;
    icon: string;
    package: string;
    enabled: boolean;
    installed: boolean;
    settingsUrl: string | null;
    adminUrl: string | null;
    required: boolean;
}

const props = defineProps<{
    packages: PackageItem[];
}>();

const page = usePage();
const status = computed(() => page.props.status as string | undefined);
const error = computed(() => page.props.error as string | undefined);

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Packages', href: '#' },
];

const iconMap: Record<string, LucideIcon> = {
    Building2,
    CreditCard,
    Languages,
    Mail,
    Settings,
};

function getIcon(name: string): LucideIcon {
    return iconMap[name] ?? Package;
}

function togglePackage(pkg: PackageItem): void {
    router.patch(
        packagesUpdate(pkg.key).url,
        { enabled: pkg.enabled ? 0 : 1 },
        { preserveScroll: true },
    );
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Packages" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col gap-8">
                <Heading title="Packages" description="Enable or disable installed packages" variant="small" />

                <div
                    v-if="status"
                    class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-400"
                >
                    {{ status }}
                </div>

                <div
                    v-if="error"
                    class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-400"
                >
                    {{ error }}
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="pkg in packages"
                        :key="pkg.key"
                        class="flex flex-col gap-4 rounded-lg border p-6"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    :class="[
                                        pkg.enabled
                                            ? 'bg-primary/10 text-primary'
                                            : 'bg-muted text-muted-foreground',
                                        'rounded-lg p-2.5',
                                    ]"
                                >
                                    <component :is="getIcon(pkg.icon)" class="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 class="font-semibold">{{ pkg.name }}</h3>
                                    <p class="text-xs text-muted-foreground">{{ pkg.package }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <StatusBadge
                                    v-if="pkg.required"
                                    label="Required"
                                    variant="purple"
                                />
                                <StatusBadge
                                    v-else
                                    :label="pkg.enabled ? 'Enabled' : 'Disabled'"
                                    :variant="pkg.enabled ? 'success' : 'default'"
                                />
                            </div>
                        </div>

                        <p class="text-sm text-muted-foreground">{{ pkg.description }}</p>

                        <div class="mt-auto flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Switch
                                    :model-value="pkg.enabled"
                                    :disabled="pkg.required"
                                    @update:model-value="togglePackage(pkg)"
                                />
                                <span class="text-sm text-muted-foreground">
                                    {{ pkg.required ? 'Always on' : (pkg.enabled ? 'On' : 'Off') }}
                                </span>
                            </div>
                            <Link
                                v-if="pkg.enabled && pkg.settingsUrl"
                                :href="pkg.settingsUrl"
                                class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                            >
                                Settings
                                <ExternalLink class="h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
