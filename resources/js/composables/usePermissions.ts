import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { Auth } from '@/types';

export function usePermissions() {
    const page = usePage();
    const auth = computed(() => page.props.auth as Auth);
    const roles = computed(() => auth.value.roles ?? []);
    const permissions = computed(() => auth.value.permissions ?? []);

    const can = (permission: string): boolean =>
        permissions.value.includes(permission);
    const hasRole = (role: string): boolean => roles.value.includes(role);
    const hasAnyRole = (roleNames: string[]): boolean =>
        roleNames.some(hasRole);

    return { auth, roles, permissions, can, hasRole, hasAnyRole };
}
