<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import UserController from '@/actions/App/Http/Controllers/UserController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index as usersIndex } from '@/routes/users';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        roles: string[];
    };
    roles: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Teams',
                href: usersIndex(),
            },
            {
                title: 'Edit',
                href: '#',
            },
        ],
    },
});

const currentRole = props.user.roles[0] ?? (props.roles[0] ?? '');
</script>

<template>
    <Head title="Edit Team Member" />
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="icon" @click="router.visit(usersIndex())">
                <ArrowLeft class="size-4" />
            </Button>
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Edit team member</h1>
                <p class="text-sm text-muted-foreground">
                    Update details and access for {{ user.name }}
                </p>
            </div>
        </div>

        <Card class="max-w-3xl overflow-hidden">
            <div class="grid md:grid-cols-[220px_1fr]">
                <div class="hidden flex-col gap-3 bg-muted/40 p-6 md:flex">
                    <div class="space-y-1">
                        <p class="text-sm font-medium">Team access</p>
                        <p class="text-xs text-muted-foreground">
                            Update role and credentials
                        </p>
                    </div>
                    <div class="mt-auto space-y-1 text-xs text-muted-foreground">
                        <p>Leave password fields blank to keep existing password.</p>
                    </div>
                </div>
                <div>
                    <CardHeader>
                        <CardTitle>Account information</CardTitle>
                        <CardDescription>
                            Edit personal details and permissions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            v-bind="UserController.update.form(user.id)"
                            class="space-y-6"
                            v-slot="{ errors, processing }"
                        >
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="name">Full name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        type="text"
                                        required
                                        :default-value="user.name"
                                        autocomplete="name"
                                        class="h-10"
                                    />
                                    <InputError :message="errors.name" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="email">Email address</Label>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        required
                                        :default-value="user.email"
                                        autocomplete="email"
                                        class="h-10"
                                    />
                                    <InputError :message="errors.email" />
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="password">New password</Label>
                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        autocomplete="new-password"
                                        placeholder="Leave blank to keep current"
                                        class="h-10"
                                    />
                                    <InputError :message="errors.password" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="password_confirmation"
                                        >Confirm new password</Label
                                    >
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        autocomplete="new-password"
                                        placeholder="Leave blank to keep current"
                                        class="h-10"
                                    />
                                    <InputError
                                        :message="errors.password_confirmation"
                                    />
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label for="role">Role</Label>
                                <select
                                    id="role"
                                    name="role"
                                    required
                                    :value="currentRole"
                                    class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
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

                            <div class="flex flex-col gap-2 sm:flex-row">
                                <Button type="submit" :disabled="processing">
                                    <Spinner v-if="processing" />
                                    Update team member
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="router.visit(usersIndex())"
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
