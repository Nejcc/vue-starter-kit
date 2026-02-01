import { Globe, Languages, LayoutDashboard } from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';
import type { NavItem } from '@/types';

interface ModuleNav {
    title: string;
    icon: LucideIcon;
    items: NavItem[];
}

export function useLocalizationNav(): ModuleNav {
    return {
        title: 'Localization',
        icon: Globe,
        items: [
            { title: 'Languages', href: '/admin/localizations/languages', icon: Languages },
            { title: 'Translations', href: '/admin/localizations/translations', icon: LayoutDashboard },
        ],
    };
}
