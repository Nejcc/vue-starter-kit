import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export type BreadcrumbItem = {
    title: string;
    href?: string;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
    children?: NavItem[];
    disabled?: boolean;
};

export type NavGroup = {
    title: string;
    icon?: LucideIcon;
    items: NavItem[];
};

export type ModuleNavGroupData = {
    title: string;
    icon: string;
    items: { title: string; href: string; icon: string }[];
};
