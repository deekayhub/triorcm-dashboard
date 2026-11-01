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
import { index as credentialingStepsIndex } from '@/routes/credentialing-steps';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    step: any;
    applications: Array<any>;
    users: Array<any>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Credentialing Steps', href: credentialingStepsIndex() },
            { title: 'Edit', href: '#' },
        ],
    },
});

const { toast } = useToast();
</script>

<template>
    <Head title="Edit Credentialing Step" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex items-center justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight">Edit Credentialing Step</h1>
                <p class="text-xs text-muted-foreground">{{
                    props.step.name
                }}</p>
            </div>
            <Button
                variant="outline"
                @click="router.visit(credentialingStepsIndex())"
                class="text-sm"
            >
                <ArrowLeft class="size-3.5" />
                Back to Steps
            </Button>
        </div>

        <section class="max-w-3xl mx-auto">
            <Card class="w-full border border-border rounded-md">
                <CardHeader>
                    <CardTitle>Step Details</CardTitle>
                    <CardDescription>
                        Update credentialing step details
                    </CardDescription>
                </CardHeader>

                <CardContent class="pt-0">
                    <Form
                        method="put"
                        :action="/credentialing-steps/" + props.step.id
                        class="space-y-4"
                        v-slot="{ errors, processing }"
                    >
                        <div>
                            <Label for="provider_application_id">Application</Label>
                            <Select
                                :defaultValue="
                                    props.step.provider_application_id ??
                                    ''
                                "
                                id="provider_application_id"
                                name="provider_application_id"
                                required
                            >
                                <SelectTrigger class="h-9">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value=" ">Select application</SelectItem>
                                    <SelectItem
                                        v-for="app in props.applications"
                                        :key="app.id"
                                        :value="app.id"
                                    >
                                        {{
                                            app.application_number
                                        }}
                                        ({{ app.provider?.first_name }} {{ app.provider?.last_name }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError
                                :message="errors.provider_application_id"
                            />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="name">Step Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    :value="props.step.name"
                                    required
                                />
                                <InputError
                                    :message="errors.name"
                                />
                            </div>
                            <div>
                                <Label for="order">Order</Label>
                                <Input
                                    id="order"
                                    name="order"
                                    type="number"
                                    min="0"
                                    :value="props.step.order"
                                />
                                <InputError
                                    :message="errors.order"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="status">Status</Label>
                                <Select
                                    :defaultValue="props.step.status"
                                    id="status"
                                    name="status"
                                    required
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="in_progress">
                                            In Progress
                                        </SelectItem>
                                        <SelectItem value="completed">
                                            Completed
                                        </SelectItem>
                                        <SelectItem value="skipped">Skipped</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="errors.status"
                                />
                            </div>
                            <div>
                                <Label for="assigned_to">Assigned To</Label>
                                <Select
                                    :defaultValue="
                                        props.step.assigned_to ?? ''
                                    "
                                    id="assigned_to"
                                    name="assigned_to"
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value=" ">None</SelectItem>
                                        <SelectItem
                                            v-for="user in props.users"
                                            :key="user.id"
                                            :value="user.id"
                                        >
                                            {{ user.name }}
                                            ({{ user.email }})
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="errors.assigned_to"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="started_at">Started At</Label>
                                <Input
                                    id="started_at"
                                    name="started_at"
                                    type="datetime-local"
                                    :default-value="
                                        props.step.started_at
                                    "
                                />
                                <InputError
                                    :message="errors.started_at"
                                />
                            </div>
                            <div>
                                <Label for="completed_at">Completed At</Label>
                                <Input
                                    id="completed_at"
                                    name="completed_at"
                                    type="datetime-local"
                                    :default-value="
                                        props.step.completed_at
                                    "
                                />
                                <InputError
                                    :message="errors.completed_at"
                                />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                :default-value="props.step.notes"
                            />
                            <InputError :message="errors.notes" />
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <Button
                                type="submit"
                                :disabled="processing"
                            >
                                <Spinner v-if="processing" />
                                Update Step
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="router.visit('/credentialing-steps/' + props.step.id)"
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
