<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    BarChart3,
    Calendar,
    Cookie,
    Settings,
    Shield,
    Target,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Separator } from '@/components/ui/separator';
import { useCookieConsent } from '@/composables/useCookieConsent';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/cookie-preferences';
import { type BreadcrumbItem } from '@/types';

import type { CookiePreferencesPageProps } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Cookie preferences',
        href: edit().url,
    },
];

const props = withDefaults(defineProps<CookiePreferencesPageProps>(), {
    cookieConsent: () => ({
        hasConsent: false,
        preferences: {},
        categories: {},
        config: {},
    }),
});

const {
    hasConsent,
    preferences,
    categories,
    updatePreferences,
    initializeFromServer,
} = useCookieConsent();

const localPreferences = ref<Record<string, boolean>>({});
const isProcessing = ref(false);
const lastUpdated = ref<string | null>(null);

// Initialize from server data
onMounted(() => {
    if (props.cookieConsent) {
        initializeFromServer(props.cookieConsent);
        localPreferences.value = { ...preferences.value };

        // Get last updated time from localStorage
        try {
            const stored = localStorage.getItem(
                `cookie_consent_${window.location.hostname}_${window.location.port || '80'}`,
            );
            if (stored) {
                const parsed = JSON.parse(stored);
                lastUpdated.value = parsed.timestamp;
            }
        } catch (error) {
            console.warn('Failed to load last updated time:', error);
        }
    }
});

// Handle save preferences
const handleSavePreferences = async () => {
    isProcessing.value = true;
    try {
        await updatePreferences(localPreferences.value);
        lastUpdated.value = new Date().toISOString();
    } finally {
        isProcessing.value = false;
    }
};

// Handle reset to defaults
const handleResetToDefaults = () => {
    const defaults: Record<string, boolean> = {};
    Object.entries(categories.value).forEach(([key, category]) => {
        defaults[key] = category.required || category.default_enabled;
    });
    localPreferences.value = defaults;
};

// Get category icon
const getCategoryIcon = (categoryKey: string) => {
    switch (categoryKey) {
        case 'essential':
            return Shield;
        case 'analytics':
            return BarChart3;
        case 'marketing':
            return Target;
        case 'preferences':
            return Settings;
        default:
            return Cookie;
    }
};

// Update local preference
const updateLocalPreference = (categoryKey: string, value: boolean) => {
    localPreferences.value[categoryKey] = value;
};

// Format date
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Cookie preferences" />

        <h1 class="sr-only">Cookie Preferences</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Cookie Preferences"
                    description="Manage your cookie preferences and review your consent history"
                />

                <!-- Current Status -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Cookie class="h-5 w-5" />
                            Current Status
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="font-medium">Cookie Consent:</span>
                            <span
                                :class="
                                    hasConsent
                                        ? 'text-green-600 dark:text-green-500'
                                        : 'text-red-600 dark:text-red-500'
                                "
                            >
                                {{ hasConsent ? 'Given' : 'Not Given' }}
                            </span>
                        </div>

                        <div
                            v-if="lastUpdated"
                            class="flex items-center justify-between"
                        >
                            <span class="font-medium">Last Updated:</span>
                            <div
                                class="flex items-center gap-2 text-sm text-muted-foreground"
                            >
                                <Calendar class="h-4 w-4" />
                                {{ formatDate(lastUpdated) }}
                            </div>
                        </div>

                        <div
                            v-if="props.cookieConsent?.config?.gdpr_mode"
                            class="flex items-center justify-between"
                        >
                            <span class="font-medium">GDPR Mode:</span>
                            <span class="text-green-600 dark:text-green-500"
                                >Enabled</span
                            >
                        </div>
                    </CardContent>
                </Card>

                <!-- Cookie Categories -->
                <Card>
                    <CardHeader>
                        <CardTitle>Cookie Categories</CardTitle>
                        <CardDescription>
                            Choose which types of cookies you want to allow.
                            Essential cookies are required for the website to
                            function.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Essential Cookies -->
                        <div>
                            <h3
                                class="mb-3 flex items-center gap-2 text-lg font-medium"
                            >
                                <Shield class="h-5 w-5" />
                                Essential Cookies
                            </h3>
                            <div class="space-y-3">
                                <div
                                    v-for="(category, key) in categories"
                                    :key="key"
                                    v-show="category.required"
                                    class="flex items-center justify-between rounded-lg border bg-muted/50 p-4"
                                >
                                    <div class="flex items-center gap-3">
                                        <component
                                            :is="getCategoryIcon(key)"
                                            class="h-5 w-5"
                                        />
                                        <div>
                                            <div class="font-medium">
                                                {{ category.name }}
                                            </div>
                                            <div
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{ category.description }}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                Cookies:
                                                {{
                                                    category.cookies.join(', ')
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                    <Checkbox
                                        :checked="true"
                                        disabled
                                        class="pointer-events-none"
                                    />
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <!-- Optional Cookies -->
                        <div>
                            <h3
                                class="mb-3 flex items-center gap-2 text-lg font-medium"
                            >
                                <Settings class="h-5 w-5" />
                                Optional Cookies
                            </h3>
                            <div class="space-y-3">
                                <div
                                    v-for="(category, key) in categories"
                                    :key="key"
                                    v-show="!category.required"
                                    class="flex items-center justify-between rounded-lg border p-4"
                                >
                                    <div class="flex items-center gap-3">
                                        <component
                                            :is="getCategoryIcon(key)"
                                            class="h-5 w-5"
                                        />
                                        <div>
                                            <div class="font-medium">
                                                {{ category.name }}
                                            </div>
                                            <div
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{ category.description }}
                                            </div>
                                            <div
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                Cookies:
                                                {{
                                                    category.cookies.join(', ')
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                    <Checkbox
                                        :checked="
                                            localPreferences[key] || false
                                        "
                                        @update:checked="
                                            (checked) =>
                                                updateLocalPreference(
                                                    key,
                                                    checked,
                                                )
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex justify-between">
                    <Button
                        variant="outline"
                        @click="handleResetToDefaults"
                        :disabled="isProcessing"
                    >
                        Reset to Defaults
                    </Button>

                    <Button
                        @click="handleSavePreferences"
                        :disabled="isProcessing"
                    >
                        {{ isProcessing ? 'Saving...' : 'Save Preferences' }}
                    </Button>
                </div>

                <!-- Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>More Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="text-sm text-muted-foreground">
                            <p>
                                Your cookie preferences are stored locally in
                                your browser and are also saved to your account
                                if you're logged in. You can change these
                                preferences at any time.
                            </p>
                        </div>

                        <div class="flex gap-4">
                            <a
                                href="/privacy-policy"
                                class="text-sm text-primary hover:underline"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Privacy Policy
                            </a>
                            <a
                                href="/cookie-policy"
                                class="text-sm text-primary hover:underline"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Cookie Policy
                            </a>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
