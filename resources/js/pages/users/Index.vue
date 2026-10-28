<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { Form, Head, router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Eye,
    MoreHorizontal,
    Pencil,
    Plus,
    Search,
    ShieldCheck,
    Trash2,
    Users as TeamsIcon,
} from '@lucide/vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import $ from 'jquery';
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useInitials } from '@/composables/useInitials';
import { usePermissions } from '@/composables/usePermissions';
import { index as usersIndex } from '@/routes/users';

DataTable.use(DataTablesCore);
$.fn.dataTable = DataTablesCore;

type UserRow = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string | null;
    roles: string[];
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedUsers = {
    data: UserRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
};

const props = defineProps<{
    users: PaginatedUsers;
    roles: string[];
    filters: {
        search: string;
        role: string;
        per_page: number;
    };
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

const search = ref(props.filters.search ?? '');
const roleFilter = ref(props.filters.role ?? 'all');
const perPage = ref(String(props.filters.per_page ?? 10));

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        usersIndex(),
        {
            search: search.value || undefined,
            role: roleFilter.value === 'all' ? undefined : roleFilter.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
};

watch(search, () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(applyFilters, 400);
});

watch(roleFilter, applyFilters);
watch(perPage, applyFilters);

const userRows = computed(() => props.users.data);

const verifiedCount = computed(
    () => userRows.value.filter((user) => user.email_verified_at !== null).length,
);

const superAdminCount = computed(
    () =>
        userRows.value.filter((user) => user.roles.includes('super-admin')).length,
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
    router.reload({ only: ['users'] });
};

const editOpen = ref(false);
const editUser = ref<UserRow | null>(null);
const editRole = ref(defaultRole);

const openEdit = (user: UserRow) => {
    editUser.value = user;
    editRole.value = user.roles?.[0] ?? defaultRole;
    editOpen.value = true;
};

watch(editOpen, (open) => {
    if (!open) {
        editUser.value = null;
    }
});

const deleteOpen = ref(false);
const deleteUser = ref<UserRow | null>(null);

const openDelete = (user: UserRow) => {
    deleteUser.value = user;
    deleteOpen.value = true;
};

watch(deleteOpen, (open) => {
    if (!open) {
        deleteUser.value = null;
    }
});

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

const goToPage = (url: string | null) => {
    if (!url) {
        return;
    }
    router.visit(url, { preserveScroll: true, preserveState: true });
};

const dtColumns = [
    { data: 'user', name: 'user', orderable: true },
    { data: 'roles', name: 'roles', orderable: false },
    { data: 'status', name: 'status', orderable: false },
    { data: 'joined', name: 'created_at', orderable: true },
    ...(isSuperAdmin.value ? [{ data: 'actions', name: 'actions', orderable: false }] : []),
];

const dtOptions = computed(() => ({
    serverSide: true,
    processing: true,
    ajax: {
        url: usersIndex().url,
        type: 'GET',
        data: (d: any) => {
            d.role = roleFilter.value === 'all' ? 'all' : roleFilter.value;
        },
    },
    columns: dtColumns,
    order: [[3, 'desc']],
    pageLength: Number(perPage.value),
    layout: {
        topStart: null,
        topEnd: null,
        bottomStart: 'info',
        bottomEnd: 'paging',
    },
    language: {
        search: '',
        searchPlaceholder: 'Search...',
        processing: 'Loading...',
        emptyTable: 'No users found',
        info: 'Showing _START_ to _END_ of _TOTAL_ users',
        infoEmpty: 'Showing 0 to 0 of 0 users',
        paginate: {
            previous: '‹',
            next: '›',
            first: '«',
            last: '»',
        },
    },
    pagingType: 'simple_numbers',
}));

const dtTable = ref<any>(null);

onMounted(() => {
    if (search.value) {
        dtTable.value?.dt()?.search(search.value).draw();
    }
});

watch(search, (val) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        dtTable.value?.dt()?.search(val).draw();
        applyFilters();
    }, 400);
});

watch(roleFilter, () => {
    dtTable.value?.dt()?.ajax?.reload?.();
});

watch(perPage, () => {
    dtTable.value?.dt()?.page.len(Number(perPage.value)).draw();
    applyFilters();
});
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

            <div v-if="isSuperAdmin">
                <Button
                    class="gap-2"
                    @click="router.visit('/users/create')"
                >
                    <Plus class="size-4" aria-hidden="true" />
                    Add team member
                </Button>
            </div>
            <Dialog v-model:open="editOpen">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Edit user</DialogTitle>
                        <DialogDescription>
                            Update user details and role.
                        </DialogDescription>
                    </DialogHeader>

                    <Form
                        v-if="editUser"
                        v-bind="UserController.update.form(editUser.id)"
                        :reset-on-success="['password', 'password_confirmation']"
                        class="space-y-4"
                        v-slot="{ errors, processing }"
                        @success="
                            editOpen = false;
                            router.reload({ only: ['users'] });
                        "
                    >
                        <div class="grid gap-2">
                            <Label for="edit-name">Name</Label>
                            <Input
                                id="edit-name"
                                name="name"
                                type="text"
                                required
                                :default-value="editUser.name"
                                autocomplete="name"
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-email">Email address</Label>
                            <Input
                                id="edit-email"
                                name="email"
                                type="email"
                                required
                                :default-value="editUser.email"
                                autocomplete="email"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-password">New password</Label>
                            <PasswordInput
                                id="edit-password"
                                name="password"
                                autocomplete="new-password"
                                placeholder="Leave blank to keep current"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-password-confirmation">
                                Confirm new password
                            </Label>
                            <PasswordInput
                                id="edit-password-confirmation"
                                name="password_confirmation"
                                autocomplete="new-password"
                                placeholder="Leave blank to keep current"
                            />
                            <InputError
                                :message="errors.password_confirmation"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="edit-role">Role</Label>
                            <select
                                id="edit-role"
                                v-model="editRole"
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
                                Save changes
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>

            <Dialog v-model:open="deleteOpen">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Delete user</DialogTitle>
                        <DialogDescription>
                            This action cannot be undone. This will permanently
                            remove
                            <span class="font-medium text-foreground">{{
                                deleteUser?.name
                            }}</span>
                            from the team.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="pt-2">
                        <DialogClose as-child>
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </DialogClose>
                        <Form
                            v-if="deleteUser"
                            v-bind="UserController.destroy.form(deleteUser.id)"
                            @success="
                                deleteOpen = false;
                                router.reload({ only: ['users'] });
                            "
                        >
                            <Button type="submit" variant="destructive">
                                Delete user
                            </Button>
                        </Form>
                    </DialogFooter>
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

                    <select
                        v-model="perPage"
                        class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 sm:w-auto"
                        aria-label="Rows per page"
                    >
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                        <option value="100">100 / page</option>
                    </select>
                </div>
            </CardHeader>

            <CardContent class="px-0">
                <div class="px-5 pb-4">
                    <DataTable
                        ref="dtTable"
                        :columns="dtColumns"
                        :options="dtOptions"
                        class="w-full text-sm"
                    >
                        <thead>
                            <tr
                                class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase"
                            >
                                <th class="px-0 py-2.5 font-medium">User</th>
                                <th class="px-0 py-2.5 font-medium">Roles</th>
                                <th class="px-0 py-2.5 font-medium">Status</th>
                                <th class="px-0 py-2.5 font-medium">Joined</th>
                                <th
                                    v-if="isSuperAdmin"
                                    class="px-0 py-2.5 text-right font-medium"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <template #column-user="props">
                            <div class="flex items-center gap-3 py-2">
                                <Avatar class="size-8">
                                    <AvatarFallback
                                        class="bg-muted text-xs font-semibold"
                                    >
                                        {{ getInitials(props.rowData.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0">
                                    <p class="truncate font-medium">
                                        {{ props.rowData.user.name }}
                                    </p>
                                    <p class="truncate text-xs text-muted-foreground">
                                        {{ props.rowData.user.email }}
                                    </p>
                                </div>
                            </div>
                        </template>
                        <template #column-roles="props">
                            <div class="flex flex-wrap gap-1.5">
                                <Badge
                                    v-for="role in props.rowData.roles"
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
                                    v-if="
                                        !props.rowData.roles ||
                                        props.rowData.roles.length === 0
                                    "
                                    variant="outline"
                                >
                                    No role
                                </Badge>
                            </div>
                        </template>
                        <template #column-status="props">
                            <Badge
                                :variant="
                                    props.rowData.status === 'verified'
                                        ? 'secondary'
                                        : 'outline'
                                "
                                class="gap-1.5"
                            >
                                <span
                                    :class="
                                        props.rowData.status === 'verified'
                                            ? 'bg-emerald-500'
                                            : 'bg-amber-500'
                                    "
                                    class="size-1.5 rounded-full"
                                    aria-hidden="true"
                                />
                                {{
                                    props.rowData.status === 'verified'
                                        ? 'Verified'
                                        : 'Pending'
                                }}
                            </Badge>
                        </template>
                        <template #column-joined="props">
                            <span class="text-muted-foreground tabular-nums">
                                {{ formatDate(props.rowData.joined) }}
                            </span>
                        </template>
                        <template v-if="isSuperAdmin" #column-actions="props">
                            <div class="flex justify-end">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="size-8"
                                        >
                                            <MoreHorizontal class="size-4" />
                                            <span class="sr-only"
                                                >Open actions</span
                                            >
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem
                                            @click="router.visit(`/users/${props.rowData.id}/edit`)"
                                        >
                                            <Pencil class="mr-2 size-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            class="text-destructive focus:text-destructive"
                                            @click="
                                                openDelete({
                                                    id: props.rowData.id,
                                                    name: props.rowData.user.name,
                                                    email: props.rowData.user.email,
                                                    email_verified_at:
                                                        props.rowData.status ===
                                                        'verified'
                                                            ? new Date().toISOString()
                                                            : null,
                                                    created_at:
                                                        props.rowData.joined,
                                                    roles: props.rowData.roles || [],
                                                })
                                            "
                                        >
                                            <Trash2 class="mr-2 size-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
