<script setup lang="ts">
import { BarChart3, Cookie, Settings, Shield, Target } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import {
    useCookieConsent,
    type CookieCategory,
} from '@/composables/useCookieConsent';

interface Props {
    cookieConsent?: {
        hasConsent: boolean;
        preferences: Record<string, boolean>;
        categories: Record<string, CookieCategory>;
        config: any;
    };
}

const props = withDefaults(defineProps<Props>(), {
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
    config,
    isEnabled,
    acceptAll,
    rejectAll,
    updatePreferences,
    initializeFromServer,
} = useCookieConsent();

const showModal = ref(false);
const localPreferences = ref<Record<string, boolean>>({});
const isProcessing = ref(false);

// Initialize from server data
onMounted(() => {
    if (props.cookieConsent) {
        initializeFromServer(props.cookieConsent);
    }
});

// Show banner only if consent is not given and system is enabled
const shouldShowBanner = computed(() => {
    return isEnabled.value && !hasConsent.value;
});

// Handle accept all
const handleAcceptAll = async () => {
    isProcessing.value = true;
    try {
        await acceptAll();
    } finally {
        isProcessing.value = false;
    }
};

// Handle reject all
const handleRejectAll = async () => {
    isProcessing.value = true;
    try {
        await rejectAll();
    } finally {
        isProcessing.value = false;
    }
};

// Handle customize
const handleCustomize = () => {
    localPreferences.value = { ...preferences.value };
    showModal.value = true;
};

// Handle save preferences
const handleSavePreferences = async () => {
    isProcessing.value = true;
    try {
        await updatePreferences(localPreferences.value);
        showModal.value = false;
    } finally {
        isProcessing.value = false;
    }
};

// Handle cancel
const handleCancel = () => {
    localPreferences.value = { ...preferences.value };
    showModal.value = false;
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
</script>

<template>
    <!-- Cookie Consent Banner -->
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div
            v-if="shouldShowBanner"
            class="fixed right-0 bottom-0 left-0 z-[9999] border-t bg-background/95 shadow-lg backdrop-blur supports-[backdrop-filter]:bg-background/60"
        >
            <div class="container mx-auto px-4 py-4">
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex-1 space-y-2">
                        <h3 class="text-lg font-semibold">
                            {{ config.banner?.title || 'We use cookies' }}
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            {{
                                config.banner?.description ||
                                'We use cookies to enhance your browsing experience.'
                            }}
                        </p>
                        <div class="flex gap-4 text-sm">
                            <a
                                v-if="config.banner?.links?.privacy_policy"
                                :href="config.banner.links.privacy_policy.url"
                                class="text-primary hover:underline"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ config.banner.links.privacy_policy.text }}
                            </a>
                            <a
                                v-if="config.banner?.links?.cookie_policy"
                                :href="config.banner.links.cookie_policy.url"
                                class="text-primary hover:underline"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ config.banner.links.cookie_policy.text }}
                            </a>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="handleRejectAll"
                            :disabled="isProcessing"
                        >
                            {{
                                config.banner?.buttons?.reject_all ||
                                'Reject All'
                            }}
                        </Button>

                        <Button
                            variant="outline"
                            size="sm"
                            @click="handleCustomize"
                            :disabled="isProcessing"
                        >
                            {{
                                config.banner?.buttons?.customize || 'Customize'
                            }}
                        </Button>

                        <Button
                            size="sm"
                            @click="handleAcceptAll"
                            :disabled="isProcessing"
                        >
                            {{
                                config.banner?.buttons?.accept_all ||
                                'Accept All'
                            }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Cookie Preferences Modal -->
    <Dialog v-model:open="showModal">
        <DialogContent class="max-h-[80vh] max-w-2xl overflow-y-auto">
            <DialogHeader>
                <DialogTitle>
                    {{ config.modal?.title || 'Cookie Preferences' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        config.modal?.description ||
                        'Manage your cookie preferences.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-6">
                <!-- Essential Cookies -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Shield class="h-5 w-5" />
                            {{
                                config.modal?.sections?.essential_title ||
                                'Essential Cookies'
                            }}
                        </CardTitle>
                        <CardDescription>
                            {{
                                config.modal?.sections?.essential_description ||
                                'These cookies are necessary for the website to function.'
                            }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="(category, key) in categories"
                                :key="key"
                                v-show="category.required"
                                class="flex items-center justify-between rounded-lg border bg-muted/50 p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <component
                                        :is="getCategoryIcon(key)"
                                        class="h-4 w-4"
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
                                    </div>
                                </div>
                                <Checkbox
                                    :checked="true"
                                    disabled
                                    class="pointer-events-none"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Optional Cookies -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Settings class="h-5 w-5" />
                            {{
                                config.modal?.sections?.optional_title ||
                                'Optional Cookies'
                            }}
                        </CardTitle>
                        <CardDescription>
                            {{
                                config.modal?.sections?.optional_description ||
                                'You can choose which optional cookies to allow.'
                            }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div
                                v-for="(category, key) in categories"
                                :key="key"
                                v-show="!category.required"
                                class="flex items-center justify-between rounded-lg border p-3"
                            >
                                <div class="flex items-center gap-3">
                                    <component
                                        :is="getCategoryIcon(key)"
                                        class="h-4 w-4"
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
                                            {{ category.cookies.join(', ') }}
                                        </div>
                                    </div>
                                </div>
                                <Checkbox
                                    :checked="localPreferences[key] || false"
                                    @update:checked="
                                        (checked) =>
                                            updateLocalPreference(key, checked)
                                    "
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Separator />

            <div class="flex justify-between">
                <Button
                    variant="outline"
                    @click="handleCancel"
                    :disabled="isProcessing"
                >
                    {{ config.modal?.buttons?.cancel || 'Cancel' }}
                </Button>

                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        @click="handleRejectAll"
                        :disabled="isProcessing"
                    >
                        {{ config.modal?.buttons?.reject_all || 'Reject All' }}
                    </Button>

                    <Button @click="handleAcceptAll" :disabled="isProcessing">
                        {{ config.modal?.buttons?.accept_all || 'Accept All' }}
                    </Button>

                    <Button
                        @click="handleSavePreferences"
                        :disabled="isProcessing"
                    >
                        {{ config.modal?.buttons?.save || 'Save Preferences' }}
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
