import {
    ArrowLeftRight,
    CreditCard,
    FileText,
    LayoutDashboard,
    ListChecks,
    RefreshCw,
    Users,
} from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';
import type { NavItem } from '@/types';

interface ModuleNav {
    title: string;
    icon: LucideIcon;
    items: NavItem[];
}

export function usePaymentNav(): ModuleNav {
    return {
        title: 'Payments',
        icon: CreditCard,
        items: [
            { title: 'Dashboard', href: '/admin/payments', icon: LayoutDashboard },
            { title: 'Transactions', href: '/admin/payments/transactions', icon: ArrowLeftRight },
            { title: 'Subscriptions', href: '/admin/payments/subscriptions', icon: RefreshCw },
            { title: 'Customers', href: '/admin/payments/customers', icon: Users },
            { title: 'Plans', href: '/admin/payments/plans', icon: ListChecks },
            { title: 'Invoices', href: '/admin/payments/invoices', icon: FileText },
        ],
    };
}
