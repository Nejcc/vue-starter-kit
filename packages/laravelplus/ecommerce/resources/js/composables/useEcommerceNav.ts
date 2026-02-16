import {
    FolderTree,
    LayoutDashboard,
    Network,
    Package,
    ShoppingBag,
    ShoppingCart,
    SlidersHorizontal,
    Tags,
} from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';
import type { NavItem } from '@/types';

interface ModuleNav {
    title: string;
    icon: LucideIcon;
    items: NavItem[];
}

export function useEcommerceNav(): ModuleNav {
    return {
        title: 'Ecommerce',
        icon: ShoppingCart,
        items: [
            {
                title: 'Dashboard',
                href: '/admin/ecommerce',
                icon: LayoutDashboard,
            },
            {
                title: 'Products',
                href: '/admin/ecommerce/products',
                icon: Package,
            },
            {
                title: 'Categories',
                href: '/admin/ecommerce/categories',
                icon: FolderTree,
            },
            {
                title: 'Category Tree',
                href: '/admin/ecommerce/categories/tree',
                icon: Network,
            },
            {
                title: 'Tags',
                href: '/admin/ecommerce/tags',
                icon: Tags,
            },
            {
                title: 'Attributes',
                href: '/admin/ecommerce/attributes',
                icon: SlidersHorizontal,
            },
            {
                title: 'Orders',
                href: '/admin/ecommerce/orders',
                icon: ShoppingBag,
            },
        ],
    };
}
