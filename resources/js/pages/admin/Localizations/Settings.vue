<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useLocalizationNav } from '@/composables/useLocalizationNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface LocalizationSettings {
    static: { driver: 'file' | 'database' | 'hybrid' };
    content: {
        enabled: boolean;
        strategy: 'json_column' | 'polymorphic_table';
    };
    detection: {
        strategy:
            | 'session'
            | 'url_prefix'
            | 'user_preference'
            | 'browser'
            | 'chain';
        chain: string[];
        user_column: string;
    };
    cache: { enabled: boolean; ttl: number };
}

interface SettingsPageProps {
    settings: LocalizationSettings;
}

const props = defineProps<SettingsPageProps>();
const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useLocalizationNav();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Localization', href: '/admin/localizations/languages' },
    { title: 'Settings', href: '#' },
];

const availableResolvers = [
    'session',
    'url_prefix',
    'user_preference',
    'browser',
];

const formData = ref({
    static: { driver: props.settings.static.driver },
    content: {
        enabled: props.settings.content.enabled,
        strategy: props.settings.content.strategy,
    },
    detection: {
        strategy: props.settings.detection.strategy,
        chain: [...(props.settings.detection.chain ?? [])],
        user_column: props.settings.detection.user_column,
    },
    cache: {
        enabled: props.settings.cache.enabled,
        ttl: props.settings.cache.ttl,
    },
});

function toggleChainResolver(resolver: string): void {
    const index = formData.value.detection.chain.indexOf(resolver);
    if (index === -1) {
        formData.value.detection.chain.push(resolver);
    } else {
        formData.value.detection.chain.splice(index, 1);
    }
}
</script>

<template>
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head title="Localization Settings" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Localization Settings"
                    description="Configure translation drivers, locale detection, and caching"
                />

                <Form
                    action="/admin/localizations/settings"
                    method="patch"
                    class="space-y-8"
                    v-slot="{ errors, processing, recentlySuccessful }"
                    :data="formData"
                >
                    <!-- Translation Driver -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h3 class="text-sm font-medium">Translation Driver</h3>
                        <div class="grid gap-2 sm:max-w-xs">
                            <Label for="static.driver">Driver</Label>
                            <select
                                id="static.driver"
                                v-model="formData.static.driver"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            >
                                <option value="file">File</option>
                                <option value="database">Database</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                            <p class="text-xs text-muted-foreground">
                                How static UI translations are loaded
                            </p>
                            <InputError :message="errors['static.driver']" />
                        </div>
                    </div>

                    <!-- Content Translation -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h3 class="text-sm font-medium">Content Translation</h3>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    v-model="formData.content.enabled"
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <div>
                                    <span class="text-sm font-medium"
                                        >Enable Content Translation</span
                                    >
                                    <p class="text-xs text-muted-foreground">
                                        Allow model content to be translated via
                                        the HasTranslations trait
                                    </p>
                                </div>
                            </label>
                            <InputError :message="errors['content.enabled']" />

                            <div class="grid gap-2 sm:max-w-xs">
                                <Label for="content.strategy"
                                    >Content Strategy</Label
                                >
                                <select
                                    id="content.strategy"
                                    v-model="formData.content.strategy"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                >
                                    <option value="json_column">
                                        JSON Column
                                    </option>
                                    <option value="polymorphic_table">
                                        Polymorphic Table
                                    </option>
                                </select>
                                <p class="text-xs text-muted-foreground">
                                    How translated content is stored
                                </p>
                                <InputError
                                    :message="errors['content.strategy']"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Locale Detection -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h3 class="text-sm font-medium">Locale Detection</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="detection.strategy"
                                    >Detection Strategy</Label
                                >
                                <select
                                    id="detection.strategy"
                                    v-model="formData.detection.strategy"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                >
                                    <option value="session">Session</option>
                                    <option value="url_prefix">
                                        URL Prefix
                                    </option>
                                    <option value="user_preference">
                                        User Preference
                                    </option>
                                    <option value="browser">Browser</option>
                                    <option value="chain">Chain</option>
                                </select>
                                <p class="text-xs text-muted-foreground">
                                    How the current locale is determined from
                                    the request
                                </p>
                                <InputError
                                    :message="errors['detection.strategy']"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="detection.user_column"
                                    >User Column</Label
                                >
                                <Input
                                    id="detection.user_column"
                                    v-model="formData.detection.user_column"
                                    type="text"
                                    placeholder="locale"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Column on users table for user_preference
                                    resolver
                                </p>
                                <InputError
                                    :message="errors['detection.user_column']"
                                />
                            </div>
                        </div>

                        <div
                            v-if="formData.detection.strategy === 'chain'"
                            class="space-y-3"
                        >
                            <Label>Chain Resolvers (in priority order)</Label>
                            <div class="space-y-2">
                                <label
                                    v-for="resolver in availableResolvers"
                                    :key="resolver"
                                    class="flex items-center gap-3"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="
                                            formData.detection.chain.includes(
                                                resolver,
                                            )
                                        "
                                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                        @change="toggleChainResolver(resolver)"
                                    />
                                    <span class="text-sm">{{
                                        resolver.replace('_', ' ')
                                    }}</span>
                                </label>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Select resolvers to try in order when using
                                chain strategy
                            </p>
                            <InputError :message="errors['detection.chain']" />
                        </div>
                    </div>

                    <!-- Cache -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h3 class="text-sm font-medium">Cache</h3>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    v-model="formData.cache.enabled"
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <div>
                                    <span class="text-sm font-medium"
                                        >Enable Translation Cache</span
                                    >
                                    <p class="text-xs text-muted-foreground">
                                        Cache loaded translations for better
                                        performance
                                    </p>
                                </div>
                            </label>
                            <InputError :message="errors['cache.enabled']" />

                            <div class="grid gap-2 sm:max-w-xs">
                                <Label for="cache.ttl"
                                    >Cache TTL (seconds)</Label
                                >
                                <Input
                                    id="cache.ttl"
                                    v-model.number="formData.cache.ttl"
                                    type="number"
                                    min="0"
                                />
                                <p class="text-xs text-muted-foreground">
                                    How long translations are cached (0 to
                                    disable TTL)
                                </p>
                                <InputError :message="errors['cache.ttl']" />
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" type="submit">
                            Save Settings
                        </Button>
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600 dark:text-neutral-400"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </div>
    </ModuleLayout>
</template>
