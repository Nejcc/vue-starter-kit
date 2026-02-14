<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTranslation } from '@/composables/useTranslation';

const { locale, availableLocales } = useTranslation();

const hasMultipleLocales = computed(() => availableLocales.value.length > 1);

const switchUrl = ref<((code: string) => { url: string }) | null>(null);

onMounted(async () => {
    try {
        const path =
            '/resources/js/actions/LaravelPlus/Localization/Http/Controllers/LocaleSwitchController';
        const mod = await import(/* @vite-ignore */ path);
        switchUrl.value = mod.default?.url ? mod.default : null;
    } catch {
        // Localization package not installed
    }
});

function switchLocale(code: string) {
    if (code === locale.value) return;
    if (switchUrl.value) {
        router.post(switchUrl.value(code), {}, { preserveScroll: true });
    }
}
</script>

<template>
    <DropdownMenu v-if="hasMultipleLocales && switchUrl">
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                size="icon"
                class="group h-9 w-9 cursor-pointer"
            >
                <span
                    class="text-xs font-semibold uppercase opacity-80 group-hover:opacity-100"
                    >{{ locale }}</span
                >
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="min-w-44">
            <DropdownMenuItem
                v-for="lang in availableLocales"
                :key="lang.code"
                class="cursor-pointer gap-2"
                @select="switchLocale(lang.code)"
            >
                <span
                    class="w-6 text-xs font-medium text-muted-foreground uppercase"
                    >{{ lang.code }}</span
                >
                <span class="flex-1">{{ lang.native_name }}</span>
                <Check v-if="lang.code === locale" class="size-4" />
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
