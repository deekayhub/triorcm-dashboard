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
                title: 'Create',
                href: '#',
            },
        ],
    },
});

const defaultRole = props.roles.includes('viewer')
    ? 'viewer'
    : (props.roles[0] ?? 'viewer');
</script>

<template>
    <Head title="Create Team Member" />
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="icon" @click="router.visit(usersIndex())">
                <ArrowLeft class="size-4" />
            </Button>
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Create team member</h1>
                <p class="text-sm text-muted-foreground">
                    Add a new team member and assign their role
                </p>
            </div>
        </div>

        <Card class="max-w-2xl">
            <CardHeader>
                <CardTitle>Team member details</CardTitle>
                <CardDescription>Provide personal and access information</CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="UserController.store.form()"
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
                                autocomplete="name"
                                placeholder="John Doe"
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
                                autocomplete="email"
                                placeholder="john@company.com"
                            />
                            <InputError :message="errors.email" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="password">Password</Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Minimum 8 characters"
                            />
                            <InputError :message="errors.password" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="password_confirmation">Confirm password</Label>
                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Repeat password"
                            />
                            <InputError :message="errors.password_confirmation" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="role">Role</Label>
                        <select
                            id="role"
                            name="role"
                            required
                            :value="defaultRole"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        >
                            <option v-for="role in roles" :key="role" :value="role">
                                {{ role }}
                            </option>
                        </select>
                        <InputError :message="errors.role" />
                    </div>

                    <div class="flex items-center gap-2">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            Create team member
                        </Button>
                        <Button type="button" variant="outline" @click="router.visit(usersIndex())">
                            Cancel
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
