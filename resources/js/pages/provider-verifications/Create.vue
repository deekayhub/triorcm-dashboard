<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import ProviderVerificationController from '@/actions/App/Http/Controllers/ProviderVerificationController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { index as providerVerificationsIndex } from '@/routes/provider-verifications';

defineProps<{
    provider: any;
    application: any;
    providers: Array<any>;
    applications: Array<any>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Verifications', href: providerVerificationsIndex() },
            { title: 'Create', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="New Verification" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">New Verification</h1>
                <p class="text-sm text-muted-foreground">Add a credentialing verification</p>
            </div>
            <Button variant="outline" @click="router.visit(providerVerificationsIndex())">
                <ArrowLeft class="size-4" />
                Back to Verifications
            </Button>
        </div>

        <Card class="max-w-4xl shadow-none">
            <CardHeader>
                <CardTitle>Verification Details</CardTitle>
                <CardDescription>Track provider credentialing verification</CardDescription>
            </CardHeader>
            <CardContent>
                <Form v-bind="ProviderVerificationController.store.form()" class="space-y-6" v-slot="{ errors, processing }">
                    <div class="grid gap-2">
                        <Label for="provider_id">Provider *</Label>
                        <select id="provider_id" name="provider_id" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                            <option value="">Select provider</option>
                            <option v-for="p in providers" :key="p.id" :value="p.id" :selected="provider && provider.id === p.id">
                                {{ p.first_name }} {{ p.last_name }} ({{ p.email }})
                            </option>
                        </select>
                        <InputError :message="errors.provider_id" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="application_id">Application</Label>
                            <select id="application_id" name="application_id" class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="">No application</option>
                                <option v-for="app in applications" :key="app.id" :value="app.id">
                                    {{ app.application_number }} ({{ app.type.replace('_', ' ') }})
                                </option>
                            </select>
                            <InputError :message="errors.application_id" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="type">Verification Type *</Label>
                            <select id="type" name="type" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="primary_source">Primary Source</option>
                                <option value="education">Education</option>
                                <option value="training">Training</option>
                                <option value="work_history">Work History</option>
                                <option value="malpractice">Malpractice</option>
                                <option value="sanctions">Sanctions</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="errors.type" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="verification_source">Verification Source</Label>
                            <Input id="verification_source" name="verification_source" />
                            <InputError :message="errors.verification_source" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="status">Status *</Label>
                            <select id="status" name="status" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="verified">Verified</option>
                                <option value="unable_to_verify">Unable to Verify</option>
                                <option value="discrepancy">Discrepancy</option>
                            </select>
                            <InputError :message="errors.status" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="requested_at">Requested Date</Label>
                            <Input id="requested_at" name="requested_at" type="date" />
                            <InputError :message="errors.requested_at" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="received_at">Received Date</Label>
                            <Input id="received_at" name="received_at" type="date" />
                            <InputError :message="errors.received_at" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="verified_by">Verified By</Label>
                            <select id="verified_by" name="verified_by" class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="">None</option>
                            </select>
                            <InputError :message="errors.verified_by" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="verified_at">Verified At</Label>
                            <Input id="verified_at" name="verified_at" type="date" />
                            <InputError :message="errors.verified_at" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="notes">Notes</Label>
                        <Textarea id="notes" name="notes" rows="3" />
                        <InputError :message="errors.notes" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="findings">Findings</Label>
                        <Textarea id="findings" name="findings" rows="3" />
                        <InputError :message="errors.findings" />
                    </div>

                    <div class="flex gap-2">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            Create Verification
                        </Button>
                        <Button type="button" variant="outline" @click="router.visit(providerVerificationsIndex())">Cancel</Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>