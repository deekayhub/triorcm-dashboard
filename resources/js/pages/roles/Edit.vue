<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Save } from '@lucide/vue';
import RoleController from '@/actions/App/Http/Controllers/RoleController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Checkbox } from '@/components/ui/checkbox';
import { index as rolesIndex } from '@/routes/roles';
import { computed, ref } from 'vue';

const props = defineProps<{
    role: {
        id: number;
        name: string;
        permissions: string[];
    };
    permissions: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Roles & permissions',
                href: rolesIndex(),
            },
            {
                title: 'Edit',
                href: '#',
            },
        ],
    },
});

const selected = ref<string[]>([...props.role.permissions]);

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

const toggleAll = (perms: string[], checked: boolean) => {
    if (checked) {
        for (const p of perms) {
            if (!selected.value.includes(p)) {
                selected.value.push(p);
            }
        }
    } else {
        selected.value = selected.value.filter((p) => !perms.includes(p));
    }
};

const isAllChecked = (perms: string[]) =>
    perms.length > 0 && perms.every((p) => selected.value.includes(p));
</script>

<template>
    <Head title="Edit role" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">
                    Edit role
                </h1>
                <p class="text-sm text-muted-foreground">
                    Update role name and permissions
                </p>
            </div>
            <Button variant="outline" @click="router.visit(rolesIndex())">
                <ArrowLeft class="size-4" />
                Back to roles
            </Button>
        </div>

        <Card class="max-w-5xl overflow-hidden shadow-none">
            <div class="grid lg:grid-cols-[240px_1fr]">
                <div class="hidden flex-col gap-3 bg-muted/40 p-6 lg:flex">
                    <div class="space-y-1">
                        <p class="text-sm font-medium">Role access</p>
                        <p class="text-xs text-muted-foreground">
                            Changes affect all users with this role.
                        </p>
                    </div>
                </div>
                <div>
                    <CardHeader>
                        <CardTitle>Role information</CardTitle>
                        <CardDescription>
                            Modify permissions for {{ role.name }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            v-bind="RoleController.update['put /roles/{role}']?.form(role.id)"
                            class="space-y-6"
                            v-slot="{ errors, processing }"
                        >
                            <input type="hidden" name="_method" value="put" />
                            <div class="grid gap-2">
                                <Label for="name">Role name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    type="text"
                                    required
                                    :default-value="role.name"
                                    autocomplete="off"
                                    class="h-10 max-w-md"
                                    :disabled="role.name === 'super-admin'"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-medium">
                                        Permissions
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Select the permissions for this role
                                    </p>
                                </div>

                                <input
                                    type="hidden"
                                    name="permissions"
                                    :value="JSON.stringify(selected)"
                                />

                                <div class="grid gap-4">
                                    <Card
                                        v-for="group in groups"
                                        :key="group.name"
                                        class="gap-0 py-0 shadow-none"
                                    >
                                        <CardHeader
                                            class="flex flex-row items-center justify-between border-b px-5 py-3"
                                        >
                                            <div>
                                                <CardTitle
                                                    class="text-sm capitalize"
                                                >
                                                    {{ group.name }}
                                                </CardTitle>
                                                <CardDescription class="text-xs">
                                                    {{
                                                        group.permissions.length
                                                    }}
                                                    permissions
                                                </CardDescription>
                                            </div>
                                            <label
                                                class="flex items-center gap-2 text-sm text-muted-foreground"
                                            >
                                                <Checkbox
                                                    :model-value="
                                                        isAllChecked(
                                                            group.permissions,
                                                        )
                                                    "
                                                    @update:model-value="
                                                        toggleAll(
                                                            group.permissions,
                                                            $event === true,
                                                        )
                                                    "
                                                    :disabled="
                                                        role.name ===
                                                        'super-admin'
                                                    "
                                                />
                                                Select all
                                            </label>
                                        </CardHeader>
                                        <CardContent class="grid gap-2 p-5 sm:grid-cols-2 lg:grid-cols-3">
                                            <label
                                                v-for="permission in group.permissions"
                                                :key="permission"
                                                class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm transition-colors hover:bg-muted/40"
                                            >
                                                <Checkbox
                                                    :model-value="
                                                        selected.includes(
                                                            permission,
                                                        )
                                                    "
                                                    @update:model-value="
                                                        (checked) => {
                                                            if (
                                                                role.name ===
                                                                'super-admin'
                                                            ) {
                                                                return;
                                                            }
                                                            if (
                                                                checked === true
                                                            ) {
                                                                if (
                                                                    !selected.includes(
                                                                        permission,
                                                                    )
                                                                ) {
                                                                    selected.push(
                                                                        permission,
                                                                    );
                                                                }
                                                            } else {
                                                                selected =
                                                                    selected.filter(
                                                                        (p) =>
                                                                            p !==
                                                                            permission,
                                                                    );
                                                            }
                                                        }
                                                    "
                                                    :disabled="
                                                        role.name ===
                                                        'super-admin'
                                                    "
                                                />
                                                <span class="truncate">
                                                    {{
                                                        permission
                                                            .split('.')
                                                            .slice(1)
                                                            .join('.')
                                                    }}
                                                </span>
                                            </label>
                                        </CardContent>
                                    </Card>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row">
                                <Button
                                    type="submit"
                                    :disabled="
                                        processing || role.name === 'super-admin'
                                    "
                                >
                                    <Spinner v-if="processing" />
                                    <Save class="size-4" />
                                    Save changes
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="router.visit(rolesIndex())"
                                >
                                    Cancel
                                </Button>
                            </div>
                        </Form>
                    </CardContent>
                </div>
            </div>
        </Card>
    </div>
</template>
