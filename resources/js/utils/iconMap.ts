import {
    Activity,
    ArrowLeftRight,
    Building2,
    CreditCard,
    Database,
    FileText,
    Key,
    LayoutDashboard,
    List,
    ListChecks,
    Mail,
    Package,
    Pencil,
    Receipt,
    RefreshCw,
    Save,
    Settings,
    Settings2,
    Shield,
    Table2,
    UserCheck,
    Users,
} from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';

const iconMap: Record<string, LucideIcon> = {
    Activity,
    ArrowLeftRight,
    Building2,
    CreditCard,
    Database,
    FileText,
    Key,
    LayoutDashboard,
    List,
    ListChecks,
    Mail,
    Package,
    Pencil,
    Receipt,
    RefreshCw,
    Save,
    Settings,
    Settings2,
    Shield,
    Table2,
    UserCheck,
    Users,
};

export function resolveIcon(name: string): LucideIcon | undefined {
    return iconMap[name];
}
