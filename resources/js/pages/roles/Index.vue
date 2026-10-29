<script setup lang="ts">
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Check,
    Edit,
    KeyRound,
    Minus,
    Plus,
    ShieldCheck,
    Trash2,
    Users as UsersIcon,
} from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { index as rolesIndex, create as rolesCreate } from '@/routes/roles';
import RoleController from '@/actions/App/Http/Controllers/RoleController';

type RoleRow = {
    id: number;
    name: string;
    users_count: number;
    permissions: string[];
};

const props = defineProps<{
    roles: RoleRow[];
    permissions: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Roles & permissions',
                href: rolesIndex(),
            },
        ],
    },
});

const roleDescriptions: Record<string, string> = {
    'super-admin': 'Full access. Bypasses every permission check.',
    admin: 'Broad access to dashboard features and settings.',
    manager: 'Can view and update users, export dashboards, and read reports.',
    viewer: 'Read-only access to the dashboard and reports.',
};

const roleDescription = (name: string): string =>
    roleDescriptions[name] ?? 'Custom access level.';

const roleVariant = (name: string): 'default' | 'secondary' | 'outline' => {
    if (name === 'super-admin') {
        return 'default';
    }

    return name === 'admin' || name === 'manager' ? 'secondary' : 'outline';
};

const hasPermission = (role: RoleRow, permission: string): boolean =>
    role.permissions.includes(permission);

const permissionGroup = (permission: string): string =>
    permission.split('.')[0] ?? permission;

const permissionAction = (permission: string): string =>
    permission.split('.').slice(1).join('.') || 'access';

const superAdminCount = computed(
    () =>
        props.roles.find((role) => role.name === 'super-admin')?.users_count ??
        0,
);

const groups = computed(() => {
    const map = new Map<string, string[]>();
    for (const permission of props.permissions) {
        const group = permission.split('.')[0] ?? 'other';
        if (!map.has(group)) {
            map.set(group, []);
        }
        map.get(group)?.push(permission);
    }
    return Array.from(map.entries()).map(([name, perms]) => ({
        name,
        permissions: perms,
    }));
});

const deleteRole = (role: RoleRow) => {
    if (role.name === 'super-admin') {
        return;
    }

    if (role.users_count > 0) {
        return;
    }

    if (confirm(`Delete role "${role.name}"? This action cannot be undone.`)) {
        router.delete(RoleController.destroy(role.id).url);
    }
};
</script>

<template>
    <Head title="Roles & permissions" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <p
                        class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        Access control
                    </p>
                    <Badge
                        variant="outline"
                        class="gap-1.5 py-0 text-[11px] font-normal"
                    >
                        <span
                            class="size-1.5 rounded-full bg-emerald-500"
                            aria-hidden="true"
                        />
                        Configurable
                    </Badge>
                </div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Roles &amp; permissions
                </h1>
                <p class="text-sm text-muted-foreground">
                    Manage roles and assign granular permissions.
                </p>
            </div>
            <Button @click="router.visit(rolesCreate())">
                <Plus class="size-4" />
                Create role
            </Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <Card class="gap-0 py-4 shadow-none">
                <CardContent class="flex items-center gap-3 px-5">
                    <div
                        class="flex size-9 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                    >
                        <ShieldCheck class="size-4" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Roles</p>
                        <p class="text-xl font-semibold tabular-nums">
                            {{ roles.length }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card class="gap-0 py-4 shadow-none">
                <CardContent class="flex items-center gap-3 px-5">
                    <div
                        class="flex size-9 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                    >
                        <KeyRound class="size-4" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Permissions</p>
                        <p class="text-xl font-semibold tabular-nums">
                            {{ permissions.length }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card class="gap-0 py-4 shadow-none">
                <CardContent class="flex items-center gap-3 px-5">
                    <div
                        class="flex size-9 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                    >
                        <UsersIcon class="size-4" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">
                            Super admins
                        </p>
                        <p class="text-xl font-semibold tabular-nums">
                            {{ superAdminCount }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 xl:grid-cols-[320px_1fr]">
            <Card class="h-fit gap-0 py-0 shadow-none">
                <CardHeader class="border-b px-5 py-4">
                    <CardTitle class="text-base">Roles</CardTitle>
                    <CardDescription>
                        Select a role to manage permissions
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="divide-y">
                        <div
                            v-for="role in roles"
                            :key="role.id"
                            class="flex items-start gap-3 px-5 py-4"
                        >
                            <div
                                class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                            >
                                <ShieldCheck class="size-4" aria-hidden="true" />
                            </div>
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex items-center justify-between gap-2">
                                    <Badge :variant="roleVariant(role.name)">
                                        {{ role.name }}
                                    </Badge>
                                    <span class="text-xs text-muted-foreground tabular-nums">
                                        {{ role.users_count }} users
                                    </span>
                                </div>
                                <p class="text-xs leading-relaxed text-muted-foreground">
                                    {{ roleDescription(role.name) }}
                                </p>
                                <div class="flex items-center gap-1 pt-1">
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        class="h-7 px-2"
                                        @click="router.visit(RoleController.edit(role.id).url)"
                                    >
                                        <Edit class="size-3.5" />
                                        Edit
                                    </Button>
                                    <Button
                                        v-if="role.name !== 'super-admin' && role.users_count === 0"
                                        size="sm"
                                        variant="ghost"
                                        class="h-7 px-2 text-destructive hover:text-destructive"
                                        @click="deleteRole(role)"
                                    >
                                        <Trash2 class="size-3.5" />
                                        Delete
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="roles.length === 0"
                            class="px-5 py-10 text-center text-sm text-muted-foreground"
                        >
                            No roles configured.
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="gap-0 py-0 shadow-none">
                <CardHeader class="border-b px-5 py-5">
                    <CardTitle class="text-base">Permission matrix</CardTitle>
                    <CardDescription>
                        Quick overview of permissions per role
                    </CardDescription>
                </CardHeader>
                <CardContent class="px-0">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] text-sm">
                            <thead>
                                <tr
                                    class="border-b text-left text-[11px] tracking-[0.12em] text-muted-foreground uppercase"
                                >
                                    <th class="min-w-[200px] px-5 py-3 font-medium">
                                        Role
                                    </th>
                                    <th
                                        v-for="group in groups"
                                        :key="group.name"
                                        class="min-w-[140px] px-4 py-3 text-left font-medium capitalize"
                                    >
                                        {{ group.name }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="role in roles"
                                    :key="role.id"
                                    class="transition-colors hover:bg-muted/40"
                                >
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <ShieldCheck class="size-4 text-muted-foreground" />
                                            <span class="font-medium">{{ role.name }}</span>
                                        </div>
                                    </td>
                                    <td
                                        v-for="group in groups"
                                        :key="`${role.id}-${group.name}`"
                                        class="px-4 py-3"
                                    >
                                        <div class="flex flex-wrap gap-1.5">
                                            <Badge
                                                v-for="permission in group.permissions"
                                                :key="permission"
                                                :variant="hasPermission(role, permission) ? 'secondary' : 'outline'"
                                                class="gap-1 py-0 text-[11px]"
                                            >
                                                <Check
                                                    v-if="hasPermission(role, permission)"
                                                    class="size-3"
                                                />
                                                <Minus
                                                    v-else
                                                    class="size-3 text-muted-foreground/40"
                                                />
                                                <span class="truncate max-w-[120px]">
                                                    {{ permissionAction(permission) }}
                                                </span>
                                            </Badge>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="roles.length === 0">
                                    <td
                                        :colspan="groups.length + 1"
                                        class="px-5 py-12 text-center text-sm text-muted-foreground"
                                    >
                                        No roles have been configured.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
                <div
                    class="flex flex-wrap items-center justify-between gap-2 border-t px-5 py-2.5 text-xs text-muted-foreground"
                >
                    <span>
                        {{ roles.length }} roles ·
                        {{ permissions.length }} permissions
                    </span>
                    <span>Click Edit on a role to manage its permissions.</span>
                </div>
            </Card>
        </div>
    </div>
</template>
