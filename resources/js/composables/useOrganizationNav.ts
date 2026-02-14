import { Building2, LayoutDashboard, Plus, Settings } from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';
import type { NavItem } from '@/types';

interface ModuleNav {
    title: string;
    icon: LucideIcon;
    items: NavItem[];
}

export function useOrganizationNav(): ModuleNav {
    return {
        title: 'Organizations',
        icon: Building2,
        items: [
            {
                title: 'All Organizations',
                href: '/admin/organizations',
                icon: LayoutDashboard,
            },
            {
                title: 'Create',
                href: '/admin/organizations/create',
                icon: Plus,
            },
            {
                title: 'Settings',
                href: '/admin/organizations/settings',
                icon: Settings,
            },
        ],
    };
}
