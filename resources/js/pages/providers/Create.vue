<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import ProviderController from '@/actions/App/Http/Controllers/ProviderController';
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

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex items-center justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight">Add Provider</h1>
                <p class="text-xs text-muted-foreground">
                    Create a new provider profile
                </p>
            </div>
            <Button
                variant="outline"
                @click="router.visit(providersIndex())"
                class="text-sm"
            >
                <ArrowLeft class="size-3.5 mr-2" />
                Back to Providers
            </Button>
        </div>

        <section class="max-w-3xl mx-auto">
            <Card class="w-full border border-border rounded-md">
                <CardHeader>
                    <CardTitle>Provider Information</CardTitle>
                    <CardDescription>
                        Complete the provider profile details below
                    </CardDescription>
                </CardHeader>

                <CardContent class="pt-0">
                    <Form
                        v-bind="ProviderController.store.form()"
                        class="space-y-4"
                        v-slot="{ errors, processing }"
                    >
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <Label for="first_name">First Name</Label>
                                <Input
                                    id="first_name"
                                    name="first_name"
                                    required
                                />
                                <InputError
                                    :message="errors.first_name"
                                />
                            </div>
                            <div>
                                <Label for="middle_name">Middle Name</Label>
                                <Input id="middle_name" name="middle_name" />
                                <InputError
                                    :message="errors.middle_name"
                                />
                            </div>
                            <div>
                                <Label for="last_name">Last Name</Label>
                                <Input id="last_name" name="last_name" required />
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
                                    required
                                />
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
                                <Select defaultvalue=" " name="gender">
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value=" ">Select</SelectItem>
                                        <SelectItem value="male">Male</SelectItem>
                                        <SelectItem value="female">Female</SelectItem>
                                        <SelectItem value="other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors.gender" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <Label for="provider_type">Provider Type</Label>
                                <Select defaultValue="physician" name="provider_type">
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
                                <Input id="specialty" name="specialty" />
                                <InputError :message="errors.specialty" />
                            </div>
                            <div>
                                <Label for="status">Status</Label>
                                <Select defaultValue="pending" name="status">
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
                            <Textarea id="notes" name="notes" rows="3" />
                            <InputError :message="errors.notes" />
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <Button
                                type="submit"
                                :disabled="processing"
                                class="flex-1"
                            >
                                <Spinner v-if="processing" class="mr-2" />
                                Create Provider
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="router.visit(providersIndex())"
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
