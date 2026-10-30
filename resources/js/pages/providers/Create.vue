<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import ProviderController from '@/actions/App/Http/Controllers/ProviderController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { index as providersIndex } from '@/routes/providers';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Providers', href: providersIndex() },
            { title: 'Create', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Add Provider" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Add Provider</h1>
                <p class="text-sm text-muted-foreground">Create a new provider profile</p>
            </div>
            <Button variant="outline" @click="router.visit(providersIndex())">
                <ArrowLeft class="size-4" />
                Back to Providers
            </Button>
        </div>

        <Card class="max-w-4xl shadow-none">
            <CardHeader>
                <CardTitle>Provider Information</CardTitle>
                <CardDescription>Basic provider details</CardDescription>
            </CardHeader>
            <CardContent>
                <Form v-bind="ProviderController.store.form()" class="space-y-6" v-slot="{ errors, processing }">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="first_name">First Name *</Label>
                            <Input id="first_name" name="first_name" required />
                            <InputError :message="errors.first_name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="middle_name">Middle Name</Label>
                            <Input id="middle_name" name="middle_name" />
                            <InputError :message="errors.middle_name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="last_name">Last Name *</Label>
                            <Input id="last_name" name="last_name" required />
                            <InputError :message="errors.last_name" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="email">Email *</Label>
                            <Input id="email" name="email" type="email" required />
                            <InputError :message="errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="npi">NPI</Label>
                            <Input id="npi" name="npi" maxlength="10" />
                            <InputError :message="errors.npi" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="phone">Phone</Label>
                            <Input id="phone" name="phone" />
                            <InputError :message="errors.phone" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="dob">Date of Birth</Label>
                            <Input id="dob" name="dob" type="date" />
                            <InputError :message="errors.dob" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="gender">Gender</Label>
                            <select id="gender" name="gender" class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="errors.gender" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="provider_type">Provider Type *</Label>
                            <select id="provider_type" name="provider_type" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="physician">Physician</option>
                                <option value="nurse_practitioner">Nurse Practitioner</option>
                                <option value="physician_assistant">Physician Assistant</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="errors.provider_type" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="specialty">Specialty</Label>
                            <Input id="specialty" name="specialty" />
                            <InputError :message="errors.specialty" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="status">Status *</Label>
                            <select id="status" name="status" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            <InputError :message="errors.status" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="notes">Notes</Label>
                        <Textarea id="notes" name="notes" rows="4" />
                        <InputError :message="errors.notes" />
                    </div>

                    <div class="flex gap-2">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            Create Provider
                        </Button>
                        <Button type="button" variant="outline" @click="router.visit(providersIndex())">Cancel</Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
