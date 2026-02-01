import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface LocaleData {
    code: string;
    name: string;
    native_name: string;
    direction: string;
    is_default: boolean;
}

interface LocalizationProps {
    locale: string;
    fallbackLocale: string;
    translations: Record<string, Record<string, string>>;
    availableLocales: LocaleData[];
}

export function useTranslation() {
    const page = usePage();

    const localization = computed<LocalizationProps | null>(
        () =>
            (page.props as Record<string, unknown>)
                .localization as LocalizationProps | null,
    );

    const locale = computed(() => localization.value?.locale ?? 'en');

    const availableLocales = computed<LocaleData[]>(
        () => localization.value?.availableLocales ?? [],
    );

    const direction = computed(() => {
        const current = availableLocales.value.find(
            (l) => l.code === locale.value,
        );
        return current?.direction ?? 'ltr';
    });

    /**
     * Translate a key with optional replacements.
     * Supports dot notation (e.g. "messages.welcome") and :placeholder replacement.
     */
    function t(key: string, replacements?: Record<string, string>): string {
        const translations = localization.value?.translations ?? {};

        // Try group.key format (e.g. "messages.welcome")
        const dotIndex = key.indexOf('.');
        if (dotIndex > -1) {
            const group = key.substring(0, dotIndex);
            const subKey = key.substring(dotIndex + 1);

            if (translations[group]?.[subKey] !== undefined) {
                return applyReplacements(
                    translations[group][subKey],
                    replacements,
                );
            }
        }

        // Try JSON translations (group "*")
        if (translations['*']?.[key] !== undefined) {
            return applyReplacements(translations['*'][key], replacements);
        }

        // Fallback to the key itself
        return applyReplacements(key, replacements);
    }

    /**
     * Check if a translation key exists.
     */
    function te(key: string): boolean {
        const translations = localization.value?.translations ?? {};

        const dotIndex = key.indexOf('.');
        if (dotIndex > -1) {
            const group = key.substring(0, dotIndex);
            const subKey = key.substring(dotIndex + 1);
            return translations[group]?.[subKey] !== undefined;
        }

        return translations['*']?.[key] !== undefined;
    }

    return {
        locale,
        availableLocales,
        direction,
        t,
        te,
    };
}

function applyReplacements(
    value: string,
    replacements?: Record<string, string>,
): string {
    if (!replacements) {
        return value;
    }

    let result = value;
    for (const [placeholder, replacement] of Object.entries(replacements)) {
        result = result.replace(
            new RegExp(`:${placeholder}`, 'g'),
            replacement,
        );
    }

    return result;
}
