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
import { index as providerLicensesIndex } from '@/routes/provider-licenses';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    license: any;
    providers: Array<any>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Licenses', href: providerLicensesIndex() },
            { title: 'Edit', href: '#' },
        ],
    },
});

const { toast } = useToast();
</script>

<template>
    <Head title="Edit License" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex items-center justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight">Edit License</h1>
                <p class="text-xs text-muted-foreground">
                    {{
                        props.license.license_number
                    }}
                </p>
            </div>
            <Button
                variant="outline"
                @click="router.visit(providerLicensesIndex())"
                class="text-sm"
            >
                <ArrowLeft class="size-3.5" />
                Back to Licenses
            </Button>
        </div>

        <section class="max-w-3xl mx-auto">
            <Card class="w-full border border-border rounded-md">
                <CardHeader>
                    <CardTitle>License Details</CardTitle>
                    <CardDescription>
                        Update license details
                    </CardDescription>
                </CardHeader>

                <CardContent class="pt-0">
                    <Form
                        method="put"
                        :action="
                            '/provider-licenses/' + props.license.id
                        "
                        class="space-y-4"
                        v-slot="{ errors, processing }"
                    >
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="license_type">License Type</Label>
                                <Select
                                    :defaultValue="props.license.license_type"
                                    id="license_type"
                                    name="license_type"
                                    required
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="md">MD</SelectItem>
                                        <SelectItem value="do">DO</SelectItem>
                                        <SelectItem value="np">NP</SelectItem>
                                        <SelectItem value="pa">PA</SelectItem>
                                        <SelectItem value="rn">RN</SelectItem>
                                        <SelectItem value="other">Other</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="errors.license_type"
                                />
                            </div>
                            <div>
                                <Label for="state">State</Label>
                                <Input
                                    id="state"
                                    name="state"
                                    required
                                    maxlength="2"
                                    :value="props.license.state"
                                />
                                <InputError
                                    :message="errors.state"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="issue_date">Issue Date</Label>
                                <Input
                                    id="issue_date"
                                    name="issue_date"
                                    type="date"
                                    :default-value="
                                        props.license.issue_date
                                    "
                                />
                                <InputError
                                    :message="errors.issue_date"
                                />
                            </div>
                            <div>
                                <Label for="expiration_date">
                                    Expiration Date
                                </Label>
                                <Input
                                    id="expiration_date"
                                    name="expiration_date"
                                    type="date"
                                    :default-value="
                                        props.license.expiration_date
                                    "
                                />
                                <InputError
                                    :message="errors.expiration_date"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="status">Status</Label>
                                <Select
                                    :defaultValue="props.license.status"
                                    id="status"
                                    name="status"
                                    required
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">
                                            Active
                                        </SelectItem>
                                        <SelectItem value="expired">
                                            Expired
                                        </SelectItem>
                                        <SelectItem value="suspended">
                                            Suspended
                                        </SelectItem>
                                        <SelectItem value="revoked">
                                            Revoked
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="errors.status"
                                />
                            </div>
                            <div>
                                <Label for="issuing_board">Issuing Board</Label>
                                <Input
                                    id="issuing_board"
                                    name="issuing_board"
                                    :default-value="
                                        props.license.issuing_board
                                    "
                                />
                                <InputError
                                    :message="errors.issuing_board"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <Button
                                type="submit"
                                :disabled="processing"
                            >
                                <Spinner v-if="processing" />
                                Update License
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="router.visit('/provider-licenses/' + props.license.id)"
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
