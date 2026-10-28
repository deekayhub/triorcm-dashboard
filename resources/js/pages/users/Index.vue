<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import { Plus, Search, ShieldCheck, Users as TeamsIcon } from '@lucide/vue';
import UserController from '@/actions/App/Http/Controllers/UserController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useInitials } from '@/composables/useInitials';
import { usePermissions } from '@/composables/usePermissions';
import { index as usersIndex } from '@/routes/users';

type UserRow = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string | null;
    roles: string[];
};

const props = defineProps<{
    users: UserRow[];
    roles: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Teams',
                href: usersIndex(),
            },
        ],
    },
});

const { getInitials } = useInitials();
const { hasRole } = usePermissions();
const isSuperAdmin = computed(() => hasRole('super-admin'));

const search = ref('');
const roleFilter = ref('all');

const filteredUsers = computed(() => {
    const query = search.value.trim().toLowerCase();

    return props.users.filter((user) => {
        const matchesQuery =
            query === '' ||
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query);

        const matchesRole =
            roleFilter.value === 'all' || user.roles.includes(roleFilter.value);

        return matchesQuery && matchesRole;
    });
});

const verifiedCount = computed(
    () => props.users.filter((user) => user.email_verified_at !== null).length,
);

const superAdminCount = computed(
    () =>
        props.users.filter((user) => user.roles.includes('super-admin')).length,
);

const defaultRole = props.roles.includes('viewer')
    ? 'viewer'
    : (props.roles[0] ?? 'viewer');

const createOpen = ref(false);
const selectedRole = ref(defaultRole);

watch(createOpen, (open) => {
    if (!open) {
        selectedRole.value = defaultRole;
    }
});

const handleCreated = (): void => {
    createOpen.value = false;
};

const formatDate = (value: string | null): string => {
    if (value === null) {
        return '—';
    }

    return new Intl.DateTimeFormat(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(value));
};
</script>

<template>
    <Head title="Users" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
        >
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <p
                        class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        Access control
                    </p>
                    <span
                        class="inline-flex items-center gap-1.5 text-[11px] text-muted-foreground"
                    >
                        <span
                            class="size-1.5 rounded-full bg-chart-1"
                            aria-hidden="true"
                        />
                        Team
                    </span>
                </div>
                <h1 class="text-2xl font-semibold tracking-tight">Teams</h1>
                <p class="text-sm text-muted-foreground">
                    Manage team members and assign roles.
                </p>
            </div>

            <Dialog v-model:open="createOpen">
                <DialogTrigger v-if="isSuperAdmin" as-child>
                    <Button class="gap-2">
                        <Plus class="size-4" aria-hidden="true" />
                        Create user
                    </Button>
                </DialogTrigger>

                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Create user</DialogTitle>
                        <DialogDescription>
                            Add a team member and choose the role they should
                            have.
                        </DialogDescription>
                    </DialogHeader>

                    <Form
                        v-bind="UserController.store.form()"
                        :reset-on-success="[
                            'name',
                            'email',
                            'password',
                            'password_confirmation',
                        ]"
                        class="space-y-4"
                        v-slot="{ errors, processing }"
                        @success="handleCreated"
                    >
                        <div class="grid gap-2">
                            <Label for="create-name">Name</Label>
                            <Input
                                id="create-name"
                                name="name"
                                type="text"
                                required
                                autocomplete="name"
                                placeholder="Full name"
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-email">Email address</Label>
                            <Input
                                id="create-email"
                                name="email"
                                type="email"
                                required
                                autocomplete="email"
                                placeholder="email@example.com"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-password">Password</Label>
                            <PasswordInput
                                id="create-password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Minimum 8 characters"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-password-confirmation">
                                Confirm password
                            </Label>
                            <PasswordInput
                                id="create-password-confirmation"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Repeat the password"
                            />
                            <InputError
                                :message="errors.password_confirmation"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="create-role">Role</Label>
                            <select
                                id="create-role"
                                v-model="selectedRole"
                                name="role"
                                required
                                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            >
                                <option
                                    v-for="role in roles"
                                    :key="role"
                                    :value="role"
                                >
                                    {{ role }}
                                </option>
                            </select>
                            <InputError :message="errors.role" />
                        </div>

                        <DialogFooter class="pt-2">
                            <DialogClose as-child>
                                <Button type="button" variant="outline">
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button type="submit" :disabled="processing">
                                <Spinner v-if="processing" />
                                Create user
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <Card class="gap-0 py-4 shadow-none">
                <CardContent class="flex items-center gap-3 px-5">
                    <div
                        class="flex size-9 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                    >
                        <UsersIcon class="size-4" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Total users</p>
                        <p class="text-xl font-semibold tabular-nums">
                            {{ users.length }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card class="gap-0 py-4 shadow-none">
                <CardContent class="flex items-center gap-3 px-5">
                    <div
                        class="flex size-9 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                    >
                        <ShieldCheck class="size-4" aria-hidden="true" />
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

            <Card class="gap-0 py-4 shadow-none">
                <CardContent class="flex items-center gap-3 px-5">
                    <div
                        class="flex size-9 items-center justify-center rounded-lg bg-muted text-muted-foreground"
                    >
                        <span
                            class="size-2 rounded-full bg-emerald-500"
                            aria-hidden="true"
                        />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Verified</p>
                        <p class="text-xl font-semibold tabular-nums">
                            {{ verifiedCount }}
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card class="gap-0 py-0 shadow-none">
            <CardHeader
                class="flex flex-col gap-4 border-b px-5 py-5 md:flex-row md:items-start md:justify-between"
            >
                <div class="space-y-1">
                    <CardTitle class="text-base">Team directory</CardTitle>
                    <CardDescription>
                        Everyone with access to the dashboard.
                    </CardDescription>
                </div>

                <div
                    class="flex w-full flex-col gap-2 sm:flex-row md:w-auto md:items-center"
                >
                    <div class="relative w-full sm:w-64">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <Input
                            v-model="search"
                            type="search"
                            class="pl-9"
                            placeholder="Search name or email"
                        />
                    </div>

                    <select
                        v-model="roleFilter"
                        class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 sm:w-auto"
                        aria-label="Filter by role"
                    >
                        <option value="all">All roles</option>
                        <option v-for="role in roles" :key="role" :value="role">
                            {{ role }}
                        </option>
                    </select>
                </div>
            </CardHeader>

            <CardContent class="px-0">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead>
                            <tr
                                class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase"
                            >
                                <th class="px-5 py-2.5 font-medium">User</th>
                                <th class="px-5 py-2.5 font-medium">Roles</th>
                                <th class="px-5 py-2.5 font-medium">Status</th>
                                <th class="px-5 py-2.5 font-medium">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="user in filteredUsers"
                                :key="user.id"
                                class="transition-colors hover:bg-muted/40"
                            >
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <Avatar class="size-8">
                                            <AvatarFallback
                                                class="bg-muted text-xs font-semibold"
                                            >
                                                {{ getInitials(user.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div class="min-w-0">
                                            <p class="truncate font-medium">
                                                {{ user.name }}
                                            </p>
                                            <p
                                                class="truncate text-xs text-muted-foreground"
                                            >
                                                {{ user.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        <Badge
                                            v-for="role in user.roles"
                                            :key="role"
                                            :variant="
                                                role === 'super-admin'
                                                    ? 'default'
                                                    : 'secondary'
                                            "
                                        >
                                            {{ role }}
                                        </Badge>
                                        <Badge
                                            v-if="user.roles.length === 0"
                                            variant="outline"
                                        >
                                            No role
                                        </Badge>
                                    </div>
                                </td>

                                <td class="px-5 py-3">
                                    <Badge
                                        :variant="
                                            user.email_verified_at
                                                ? 'secondary'
                                                : 'outline'
                                        "
                                        class="gap-1.5"
                                    >
                                        <span
                                            :class="
                                                user.email_verified_at
                                                    ? 'bg-emerald-500'
                                                    : 'bg-amber-500'
                                            "
                                            class="size-1.5 rounded-full"
                                            aria-hidden="true"
                                        />
                                        {{
                                            user.email_verified_at
                                                ? 'Verified'
                                                : 'Pending'
                                        }}
                                    </Badge>
                                </td>

                                <td
                                    class="px-5 py-3 text-muted-foreground tabular-nums"
                                >
                                    {{ formatDate(user.created_at) }}
                                </td>
                            </tr>

                            <tr v-if="filteredUsers.length === 0">
                                <td
                                    colspan="4"
                                    class="px-5 py-12 text-center text-sm text-muted-foreground"
                                >
                                    No users match your filters.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>

            <div
                class="flex items-center justify-between border-t px-5 py-2.5 text-xs text-muted-foreground"
            >
                <span>
                    Showing {{ filteredUsers.length }} of {{ users.length }}
                    users
                </span>
                <span v-if="isSuperAdmin"
                    >You can create new team members.</span
                >
            </div>
        </Card>
    </div>
</template>
