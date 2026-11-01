<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { index as providersIndex } from '@/routes/providers';

const props = defineProps<{
    provider: any;
    teams: Array<any>;
    users: Array<any>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Providers', href: providersIndex() },
            { title: 'Edit', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Edit Provider" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Edit Provider</h1>
                <p class="text-sm text-muted-foreground">{{ provider.full_name }}</p>
            </div>
            <Button variant="outline" @click="router.visit(providersIndex())">
                <ArrowLeft class="size-4" />
                Back to Providers
            </Button>
        </div>

        <Card class="max-w-3xl shadow-none">
            <CardHeader>
                <CardTitle>Provider Information</CardTitle>
                <CardDescription>Update provider profile details</CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    method="put"
                    :action="`/providers/${provider.id}`"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label for="first_name">First Name *</Label>
                            <Input id="first_name" name="first_name" :value="provider.first_name" required />
                            <InputError :message="errors.first_name" />
                        </div>
                        <div>
                            <Label for="middle_name">Middle Name</Label>
                            <Input id="middle_name" name="middle_name" :value="provider.middle_name" />
                            <InputError :message="errors.middle_name" />
                        </div>
                        <div>
                            <Label for="last_name">Last Name *</Label>
                            <Input id="last_name" name="last_name" :value="provider.last_name" required />
                            <InputError :message="errors.last_name" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label for="display_name">Display Name</Label>
                            <Input id="display_name" name="display_name" :value="provider.display_name" />
                            <InputError :message="errors.display_name" />
                        </div>
                        <div>
                            <Label for="email">Email *</Label>
                            <Input id="email" name="email" type="email" :value="provider.email" required />
                            <InputError :message="errors.email" />
                        </div>
                        <div>
                            <Label for="npi">NPI</Label>
                            <Input id="npi" name="npi" :value="provider.npi" maxlength="10" />
                            <InputError :message="errors.npi" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label for="phone">Phone</Label>
                            <Input id="phone" name="phone" :value="provider.phone" />
                            <InputError :message="errors.phone" />
                        </div>
                        <div>
                            <Label for="fax">Fax</Label>
                            <Input id="fax" name="fax" :value="provider.fax" />
                            <InputError :message="errors.fax" />
                        </div>
                        <div>
                            <Label for="dob">Date of Birth</Label>
                            <Input id="dob" name="dob" type="date" :default-value="provider.dob" />
                            <InputError :message="errors.dob" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label for="gender">Gender</Label>
                            <select id="gender" name="gender" class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="">Select</option>
                                <option value="male" :selected="provider.gender === 'male'">Male</option>
                                <option value="female" :selected="provider.gender === 'female'">Female</option>
                                <option value="other" :selected="provider.gender === 'other'">Other</option>
                            </select>
                            <InputError :message="errors.gender" />
                        </div>
                        <div>
                            <Label for="provider_type">Provider Type *</Label>
                            <select id="provider_type" name="provider_type" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="physician" :selected="provider.provider_type === 'physician'">Physician</option>
                                <option value="nurse_practitioner" :selected="provider.provider_type === 'nurse_practitioner'">Nurse Practitioner</option>
                                <option value="physician_assistant" :selected="provider.provider_type === 'physician_assistant'">Physician Assistant</option>
                                <option value="other" :selected="provider.provider_type === 'other'">Other</option>
                            </select>
                            <InputError :message="errors.provider_type" />
                        </div>
                        <div>
                            <Label for="specialty">Specialty</Label>
                            <Input id="specialty" name="specialty" :value="provider.specialty" />
                            <InputError :message="errors.specialty" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label for="taxonomy_code">Taxonomy Code</Label>
                            <Input id="taxonomy_code" name="taxonomy_code" :value="provider.taxonomy_code" maxlength="10" />
                            <InputError :message="errors.taxonomy_code" />
                        </div>
                        <div>
                            <Label for="team_id">Team</Label>
                            <select id="team_id" name="team_id" class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="">No team</option>
                                <option v-for="team in teams" :key="team.id" :value="team.id" :selected="provider.team?.id === team.id">
                                    {{ team.name }}
                                </option>
                            </select>
                            <InputError :message="errors.team_id" />
                        </div>
                        <div>
                            <Label for="status">Status *</Label>
                            <select id="status" name="status" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="pending" :selected="provider.status === 'pending'">Pending</option>
                                <option value="active" :selected="provider.status === 'active'">Active</option>
                                <option value="inactive" :selected="provider.status === 'inactive'">Inactive</option>
                                <option value="suspended" :selected="provider.status === 'suspended'">Suspended</option>
                            </select>
                            <InputError :message="errors.status" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="notes">Notes</Label>
                        <Textarea id="notes" name="notes" rows="4" :default-value="provider.notes" />
                        <InputError :message="errors.notes" />
                    </div>

                    <div class="flex items-center justify-between">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            Update Provider
                        </Button>
                        <Button type="button" variant="outline" @click="router.visit(`/providers/${provider.id}`)">
                            Cancel
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
