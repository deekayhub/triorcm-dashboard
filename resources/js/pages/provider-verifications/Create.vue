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
import { index as providerVerificationsIndex } from '@/routes/provider-verifications';
import { useToast } from '@/composables/useToast';

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

const { toast } = useToast();
</script>

<template>
    <Head title="New Verification" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex items-center justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight">New Verification</h1>
                <p class="text-xs text-muted-foreground">
                    Add a credentialing verification
                </p>
            </div>
            <Button
                variant="outline"
                @click="router.visit(providerVerificationsIndex())"
                class="text-sm"
            >
                <ArrowLeft class="size-3.5 mr-2" />
                Back to Verifications
            </Button>
        </div>

        <section class="max-w-4xl mx-auto">
            <Card class="w-full border border-border rounded-md">
                <CardHeader>
                    <CardTitle>Verification Details</CardTitle>
                    <CardDescription>
                        Track provider credentialing verification
                    </CardDescription>
                </CardHeader>

                <CardContent class="pt-0">
                    <Form
                        method="post"
                        action="/provider-verifications"
                        class="space-y-4"
                        v-slot="{ errors, processing }"
                    >
                        <div>
                            <Label for="provider_id">Provider</Label>
                            <Select
                                :defaultValue="provider?.id ?? ''"
                                id="provider_id"
                                name="provider_id"
                                required
                            >
                                <SelectTrigger class="h-9">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value=" ">Select provider</SelectItem>
                                    <SelectItem
                                        v-for="p in providers"
                                        :key="p.id"
                                        :value="p.id"
                                    >
                                        {{ p.first_name }} {{ p.last_name }}
                                        ({{ p.email }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError
                                :message="errors.provider_id"
                            />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="application_id">Application</Label>
                                <Select
                                    :defaultValue="
                                        application?.id ?? ''
                                    "
                                    id="application_id"
                                    name="application_id"
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value=" ">No application</SelectItem>
                                        <SelectItem
                                            v-for="app in applications"
                                            :key="app.id"
                                            :value="app.id"
                                        >
                                            {{
                                                app.application_number
                                            }}
                                            ({{ app.type }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="errors.application_id"
                                />
                            </div>
                            <div>
                                <Label for="type">Verification Type</Label>
                                <Select
                                    defaultValue="primary_source"
                                    id="type"
                                    name="type"
                                    required
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="primary_source">
                                            Primary Source
                                        </SelectItem>
                                        <SelectItem value="education">
                                            Education
                                        </SelectItem>
                                        <SelectItem value="training">
                                            Training
                                        </SelectItem>
                                        <SelectItem value="work_history">
                                            Work History
                                        </SelectItem>
                                        <SelectItem value="malpractice">
                                            Malpractice
                                        </SelectItem>
                                        <SelectItem value="sanctions">
                                            Sanctions
                                        </SelectItem>
                                        <SelectItem value="other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="errors.type"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="verification_source">
                                    Verification Source
                                </Label>
                                <Input
                                    id="verification_source"
                                    name="verification_source"
                                />
                                <InputError
                                    :message="errors.verification_source"
                                />
                            </div>
                            <div>
                                <Label for="status">Status</Label>
                                <Select
                                    defaultValue="pending"
                                    id="status"
                                    name="status"
                                    required
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">
                                            Pending
                                        </SelectItem>
                                        <SelectItem value="in_progress">
                                            In Progress
                                        </SelectItem>
                                        <SelectItem value="verified">Verified</SelectItem>
                                        <SelectItem value="unable_to_verify">
                                            Unable to Verify
                                        </SelectItem>
                                        <SelectItem value="discrepancy">
                                            Discrepancy
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="errors.status"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="requested_at">
                                    Requested Date
                                </Label>
                                <Input
                                    id="requested_at"
                                    name="requested_at"
                                    type="date"
                                />
                                <InputError
                                    :message="errors.requested_at"
                                />
                            </div>
                            <div>
                                <Label for="received_at">
                                    Received Date
                                </Label>
                                <Input
                                    id="received_at"
                                    name="received_at"
                                    type="date"
                                />
                                <InputError
                                    :message="errors.received_at"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="verified_by">Verified By</Label>
                                <Select
                                    id="verified_by"
                                    name="verified_by"
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value=" ">None</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="errors.verified_by"
                                />
                            </div>
                            <div>
                                <Label for="verified_at">Verified At</Label>
                                <Input
                                    id="verified_at"
                                    name="verified_at"
                                    type="date"
                                />
                                <InputError
                                    :message="errors.verified_at"
                                />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                name="notes"
                                rows="3"
                            />
                            <InputError :message="errors.notes" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="findings">Findings</Label>
                            <Textarea
                                id="findings"
                                name="findings"
                                rows="3"
                            />
                            <InputError :message="errors.findings" />
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <Button
                                type="submit"
                                :disabled="processing"
                                class="flex-1"
                            >
                                <Spinner v-if="processing" class="mr-2" />
                                Create Verification
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="router.visit(providerVerificationsIndex())"
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
