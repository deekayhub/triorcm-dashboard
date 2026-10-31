<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import ProviderLicenseController from '@/actions/App/Http/Controllers/ProviderLicenseController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { index as providerLicensesIndex } from '@/routes/provider-licenses';

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
</script>

<template>
    <Head title="Edit License" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Edit License</h1>
                <p class="text-sm text-muted-foreground">{{ license.license_number }}</p>
            </div>
            <Button variant="outline" @click="router.visit(providerLicensesIndex())">
                <ArrowLeft class="size-4" />
                Back to Licenses
            </Button>
        </div>

        <Card class="max-w-3xl shadow-none">
            <CardHeader>
                <CardTitle>License Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form v-bind="ProviderLicenseController.update.form(license.id)" class="space-y-6" v-slot="{ errors, processing }">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="license_type">License Type *</Label>
                            <select id="license_type" name="license_type" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm" :value="license.license_type">
                                <option value="md">MD</option>
                                <option value="do">DO</option>
                                <option value="np">NP</option>
                                <option value="pa">PA</option>
                                <option value="rn">RN</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="errors.license_type" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="state">State *</Label>
                            <Input id="state" name="state" required maxlength="2" :value="license.state" />
                            <InputError :message="errors.state" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="issue_date">Issue Date</Label>
                            <Input id="issue_date" name="issue_date" type="date" :default-value="license.issue_date" />
                            <InputError :message="errors.issue_date" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="expiration_date">Expiration Date</Label>
                            <Input id="expiration_date" name="expiration_date" type="date" :default-value="license.expiration_date" />
                            <InputError :message="errors.expiration_date" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="status">Status *</Label>
                            <select id="status" name="status" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm" :value="license.status">
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                                <option value="suspended">Suspended</option>
                                <option value="revoked">Revoked</option>
                            </select>
                            <InputError :message="errors.status" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="issuing_board">Issuing Board</Label>
                            <Input id="issuing_board" name="issuing_board" :default-value="license.issuing_board" />
                            <InputError :message="errors.issuing_board" />
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            Update License
                        </Button>
                        <Button type="button" variant="outline" @click="router.visit(`/provider-licenses/${license.id}`)">Cancel</Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>