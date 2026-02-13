import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useUserPermissions() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user as Record<string, unknown> | null);
    const roles = computed<string[]>(() => (user.value?.roles as string[]) ?? []);
    const permissions = computed<string[]>(() => (user.value?.permissions as string[]) ?? []);

    const isSuperAdmin = computed(() => roles.value.includes('super-admin'));
    const isAdmin = computed(() => roles.value.includes('admin') || isSuperAdmin.value);

    function hasRole(role: string): boolean {
        return roles.value.includes(role);
    }

    function hasAnyRole(...checkRoles: string[]): boolean {
        return checkRoles.some((role) => roles.value.includes(role));
    }

    function hasPermission(permission: string): boolean {
        return isSuperAdmin.value || permissions.value.includes(permission);
    }

    return {
        user,
        roles,
        permissions,
        isSuperAdmin,
        isAdmin,
        hasRole,
        hasAnyRole,
        hasPermission,
    };
}
