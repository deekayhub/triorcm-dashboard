<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex items-center justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight">Edit Provider</h1>
                <p class="text-xs text-muted-foreground">
                    {{ provider.full_name }}
                </p>
            </div>
            <Button
                variant="outline"
                @click="router.visit(providersIndex())"
                class="text-sm"
            >
                <ArrowLeft class="size-3.5" />
                Back to Providers
            </Button>
        </div>

        <section class="max-w-3xl mx-auto">
            <Card class="w-full border border-border rounded-md">
                <CardHeader>
                    <CardTitle>Provider Information</CardTitle>
                    <CardDescription>
                        Update provider profile details
                    </CardDescription>
                </CardHeader>

                <CardContent class="pt-0">
                    <Form
                        method="put"
                        :action="`/providers/${provider.id}`"
                        class="space-y-4"
                        v-slot="{ errors, processing }"
                    >
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <Label for="first_name">First Name</Label>
                                <Input
                                    id="first_name"
                                    name="first_name"
                                    :value="provider.first_name"
                                    required
                                />
                                <InputError
                                    :message="errors.first_name"
                                />
                            </div>
                            <div>
                                <Label for="middle_name">Middle Name</Label>
                                <Input
                                    id="middle_name"
                                    name="middle_name"
                                    :value="provider.middle_name"
                                />
                                <InputError
                                    :message="errors.middle_name"
                                />
                            </div>
                            <div>
                                <Label for="last_name">Last Name</Label>
                                <Input
                                    id="last_name"
                                    name="last_name"
                                    :value="provider.last_name"
                                    required
                                />
                                <InputError
                                    :message="errors.last_name"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    :value="provider.email"
                                    required
                                />
                                <InputError :message="errors.email" />
                            </div>
                            <div>
                                <Label for="npi">NPI</Label>
                                <Input
                                    id="npi"
                                    name="npi"
                                    :value="provider.npi"
                                    maxlength="10"
                                />
                                <InputError :message="errors.npi" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <Label for="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    name="phone"
                                    :value="provider.phone"
                                />
                                <InputError :message="errors.phone" />
                            </div>
                            <div>
                                <Label for="fax">Fax</Label>
                                <Input id="fax" name="fax" :value="provider.fax" />
                                <InputError :message="errors.fax" />
                            </div>
                            <div>
                                <Label for="dob">Date of Birth</Label>
                                <Input
                                    id="dob"
                                    name="dob"
                                    type="date"
                                    :default-value="provider.dob"
                                />
                                <InputError :message="errors.dob" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <Label for="gender">Gender</Label>
                                <Select
                                    :defaultValue="provider.gender ?? ''"
                                    name="gender"
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value=" ">Select</SelectItem>
                                        <SelectItem value="male">Male</SelectItem>
                                        <SelectItem value="female">
                                            Female
                                        </SelectItem>
                                        <SelectItem value="other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors.gender" />
                            </div>
                            <div>
                                <Label for="provider_type">Provider Type</Label>
                                <Select
                                    :defaultValue="provider.provider_type ?? 'physician'"
                                    name="provider_type"
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="physician">
                                            Physician
                                        </SelectItem>
                                        <SelectItem value="nurse_practitioner">
                                            Nurse Practitioner
                                        </SelectItem>
                                        <SelectItem value="physician_assistant">
                                            Physician Assistant
                                        </SelectItem>
                                        <SelectItem value="other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="errors.provider_type"
                                />
                            </div>
                            <div>
                                <Label for="specialty">Specialty</Label>
                                <Input
                                    id="specialty"
                                    name="specialty"
                                    :value="provider.specialty"
                                />
                                <InputError :message="errors.specialty" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <Label for="taxonomy_code">Taxonomy Code</Label>
                                <Input
                                    id="taxonomy_code"
                                    name="taxonomy_code"
                                    :value="provider.taxonomy_code"
                                    maxlength="10"
                                />
                                <InputError
                                    :message="errors.taxonomy_code"
                                />
                            </div>
                            <div>
                                <Label for="team_id">Team</Label>
                                <Select
                                    :defaultValue="provider.team_id ?? ''"
                                    name="team_id"
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value=" ">No team</SelectItem>
                                        <SelectItem
                                            v-for="team in teams"
                                            :key="team.id"
                                            :value="team.id"
                                        >
                                            {{ team.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors.team_id" />
                            </div>
                            <div>
                                <Label for="status">Status</Label>
                                <Select
                                    :defaultValue="provider.status ?? 'pending'"
                                    name="status"
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="inactive">
                                            Inactive
                                        </SelectItem>
                                        <SelectItem value="suspended">
                                            Suspended
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors.status" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                :default-value="provider.notes"
                            />
                            <InputError :message="errors.notes" />
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <Button
                                type="submit"
                                :disabled="processing"
                            >
                                <Spinner v-if="processing" />
                                Update Provider
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="router.visit('/providers/' + provider.id)"
                            >
                                Cancel
                            </Button>
                        </div>
                    </Form>
                </CardContent>
            </Card>
        </section>
    </div>
</template>
