import { LayoutDashboard, List, Mail, Users } from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';
import type { NavItem } from '@/types';

interface ModuleNav {
    title: string;
    icon: LucideIcon;
    items: NavItem[];
}

export function useSubscriberNav(): ModuleNav {
    return {
        title: 'Subscribers',
        icon: Mail,
        items: [
            { title: 'Dashboard', href: '/admin/subscribers', icon: LayoutDashboard },
            { title: 'Subscribers', href: '/admin/subscribers/subscribers', icon: Users },
            { title: 'Lists', href: '/admin/subscribers/lists', icon: List },
        ],
    };
}
