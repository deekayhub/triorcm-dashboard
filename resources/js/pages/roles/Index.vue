<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Check,
    KeyRound,
    Minus,
    ShieldCheck,
    Users as UsersIcon,
} from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { index as rolesIndex } from '@/routes/roles';

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
</script>

<template>
    <Head title="Roles & permissions" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
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
                    Read only
                </Badge>
            </div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Roles &amp; permissions
            </h1>
            <p class="text-sm text-muted-foreground">
                Review what each role can access across the dashboard.
            </p>
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

        <Card class="gap-0 py-0 shadow-none">
            <CardHeader class="border-b px-5 py-5">
                <CardTitle class="text-base">Permission matrix</CardTitle>
                <CardDescription>
                    A checkmark means the role has that permission. Super admins
                    bypass every check.
                </CardDescription>
            </CardHeader>

            <CardContent class="px-0">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1080px] text-sm">
                        <thead>
                            <tr
                                class="border-b text-left text-[11px] tracking-[0.12em] text-muted-foreground uppercase"
                            >
                                <th class="min-w-[240px] px-5 py-3 font-medium">
                                    Role
                                </th>
                                <th
                                    class="w-16 px-3 py-3 text-center font-medium"
                                >
                                    Users
                                </th>
                                <th
                                    v-for="permission in permissions"
                                    :key="permission"
                                    class="min-w-[92px] px-2 py-3 text-center align-bottom font-medium"
                                >
                                    <span
                                        class="block text-[10px] tracking-[0.16em] text-muted-foreground/80"
                                    >
                                        {{ permissionGroup(permission) }}
                                    </span>
                                    <span
                                        class="mt-0.5 block tracking-normal text-foreground/80 normal-case"
                                    >
                                        {{ permissionAction(permission) }}
                                    </span>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            <tr
                                v-for="role in roles"
                                :key="role.id"
                                class="transition-colors hover:bg-muted/40"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                                        >
                                            <ShieldCheck
                                                class="size-4"
                                                aria-hidden="true"
                                            />
                                        </div>
                                        <div class="space-y-1">
                                            <Badge
                                                :variant="
                                                    roleVariant(role.name)
                                                "
                                            >
                                                {{ role.name }}
                                            </Badge>
                                            <p
                                                class="max-w-[210px] text-xs leading-relaxed text-muted-foreground"
                                            >
                                                {{ roleDescription(role.name) }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-4 text-center">
                                    <span class="font-medium tabular-nums">
                                        {{ role.users_count }}
                                    </span>
                                </td>

                                <td
                                    v-for="permission in permissions"
                                    :key="`${role.id}-${permission}`"
                                    class="px-2 py-4 text-center"
                                >
                                    <span
                                        v-if="hasPermission(role, permission)"
                                        class="inline-flex size-6 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                                    >
                                        <Check
                                            class="size-3.5"
                                            aria-hidden="true"
                                        />
                                        <span class="sr-only">Granted</span>
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex size-6 items-center justify-center rounded-full text-muted-foreground/25"
                                    >
                                        <Minus
                                            class="size-3.5"
                                            aria-hidden="true"
                                        />
                                        <span class="sr-only">
                                            Not granted
                                        </span>
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="roles.length === 0">
                                <td
                                    :colspan="permissions.length + 2"
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
                <span>Roles are managed by the system.</span>
            </div>
        </Card>
    </div>
</template>
