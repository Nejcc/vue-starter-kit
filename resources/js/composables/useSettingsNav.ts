import { LayoutDashboard, Plus, Settings } from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';
import type { NavItem } from '@/types';

interface ModuleNav {
    title: string;
    icon: LucideIcon;
    items: NavItem[];
}

export function useSettingsNav(): ModuleNav {
    return {
        title: 'Global Settings',
        icon: Settings,
        items: [
            { title: 'All Settings', href: '/admin/settings', icon: LayoutDashboard },
            { title: 'Create', href: '/admin/settings/create', icon: Plus },
        ],
    };
}
