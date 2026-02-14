<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';
import {
    Activity,
    Building2,
    Check,
    ClipboardCopy,
    CreditCard,
    Mail,
    Package,
    Settings,
} from 'lucide-vue-next';
import { ref } from 'vue';
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

const copiedKey = ref<string | null>(null);

async function copyInstallCommand(mod: Module) {
    try {
        await navigator.clipboard.writeText(`composer require ${mod.package}`);
        copiedKey.value = mod.key;
        setTimeout(() => {
            copiedKey.value = null;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy install command:', err);
    }
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Modules" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col gap-8">
                <Heading
                    title="Modules"
                    description="Installed packages and extensions"
                    variant="small"
                />

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
                                    <component
                                        :is="getIcon(mod.icon)"
                                        class="h-5 w-5"
                                    />
                                </div>
                                <div>
                                    <h3 class="font-semibold">
                                        {{ mod.name }}
                                    </h3>
                                    <p class="text-xs text-muted-foreground">
                                        {{ mod.package }}
                                    </p>
                                </div>
                            </div>
                            <StatusBadge
                                :label="
                                    mod.installed
                                        ? 'Installed'
                                        : 'Not Installed'
                                "
                                :variant="mod.installed ? 'success' : 'default'"
                            />
                        </div>

                        <p class="text-sm text-muted-foreground">
                            {{ mod.description }}
                        </p>

                        <div class="mt-auto">
                            <Link
                                v-if="mod.installed && mod.adminUrl"
                                :href="mod.adminUrl"
                                class="inline-flex items-center text-sm font-medium text-primary hover:underline"
                            >
                                Manage &rarr;
                            </Link>
                            <button
                                v-else-if="!mod.installed"
                                @click="copyInstallCommand(mod)"
                                class="inline-flex items-center gap-1.5 rounded-md bg-muted px-3 py-1.5 font-mono text-xs text-muted-foreground transition-colors hover:bg-muted/80 hover:text-foreground"
                            >
                                <component
                                    :is="
                                        copiedKey === mod.key
                                            ? Check
                                            : ClipboardCopy
                                    "
                                    class="h-3.5 w-3.5"
                                />
                                <span v-if="copiedKey === mod.key"
                                    >Copied!</span
                                >
                                <span v-else
                                    >composer require {{ mod.package }}</span
                                >
                            </button>
                            <span v-else class="text-sm text-muted-foreground"
                                >Not available</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
