import { Bell, Globe, LayoutDashboard, Lock, Paintbrush, Plus, Server, Settings, Shield } from 'lucide-vue-next';
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
            { title: 'General', href: '/admin/settings/group/general', icon: Globe },
            { title: 'Authentication', href: '/admin/settings/group/authentication', icon: Lock },
            { title: 'Notifications', href: '/admin/settings/group/notifications', icon: Bell },
            { title: 'Security', href: '/admin/settings/group/security', icon: Shield },
            { title: 'Appearance', href: '/admin/settings/group/appearance', icon: Paintbrush },
            { title: 'System', href: '/admin/settings/group/system', icon: Server },
            { title: 'Create', href: '/admin/settings/create', icon: Plus },
        ],
    };
}
