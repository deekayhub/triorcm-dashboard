<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { index as providersIndex } from '@/routes/providers';
import { useToast } from '@/composables/useToast';

defineProps<{
    provider?: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Providers', href: providersIndex() },
            { title: 'Create', href: '#' },
        ],
    },
});

const { toast } = useToast();
</script>

<template>
    <Head title="Add Provider" />

    <div class="flex h-full flex-1 flex-col gap-6 bg-neutral-50">

        <!-- Back Button -->
        <div class="mb-6">
            <Button variant="outline" @click="router.visit(providersIndex())">
                <ArrowLeft class="size-4 mr-2" /> Back to Providers
            </Button>
        </div>

        <!-- Provider Form -->
        <section class="max-w-3xl mx-auto">
            <Card class="p-6 lg:p-8">
                <CardHeader>
                    <CardTitle>Provider Information</CardTitle>
                    <CardDescription>Create a new provider profile</CardDescription>
                </CardHeader>

                <Form v-bind="ProviderController.store.form()" class="space-y-6" v-slot="{ errors, processing }">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label for="first_name">First Name *</Label>
                            <Input id="first_name" name="first_name" required />
                            <InputError :message="errors.first_name" />
                        </div>
                        <div>
                            <Label for="middle_name">Middle Name</Label>
                            <Input id="middle_name" name="middle_name" />
                            <InputError :message="errors.middle_name" />
                        </div>
                        <div>
                            <Label for="last_name">Last Name *</Label>
                            <Input id="last_name" name="last_name" required />
                            <InputError :message="errors.last_name" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label for="email">Email *</Label>
                            <Input id="email" name="email" type="email" required />
                            <InputError :message="errors.email" />
                        </div>
                        <div>
                            <Label for="npi">NPI</Label>
                            <Input id="npi" name="npi" maxlength="10" />
                            <InputError :message="errors.npi" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label for="phone">Phone</Label>
                            <Input id="phone" name="phone" />
                            <InputError :message="errors.phone" />
                        </div>
                        <div>
                            <Label for="dob">Date of Birth</Label>
                            <Input id="dob" name="dob" type="date" />
                            <InputError :message="errors.dob" />
                        </div>
                        <div>
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
                        <div>
                            <Label for="provider_type">Provider Type *</Label>
                            <select id="provider_type" name="provider_type" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="physician">Physician</option>
                                <option value="nurse_practitioner">Nurse Practitioner</option>
                                <option value="physician_assistant">Physician Assistant</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="errors.provider_type" />
                        </div>
                        <div>
                            <Label for="specialty">Specialty</Label>
                            <Input id="specialty" name="specialty" />
                            <InputError :message="errors.specialty" />
                        </div>
                        <div>
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

                    <div class="flex items-center justify-between">
                        <Button type="submit" :disabled="processing" class="flex-1">
                            <Spinner v-if="processing" mr-2 /> Create Provider
                        </Button>
                        <Button type="button" variant="outline" @click="router.visit(providersIndex())">
                            Cancel
                        </Button>
                    </div>
                </Form>
            </Card>
        </section>

    </div>
</template>