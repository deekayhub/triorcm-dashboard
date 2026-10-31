<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import ProviderApplicationController from '@/actions/App/Http/Controllers/ProviderApplicationController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { index as providerApplicationsIndex } from '@/routes/provider-applications';

defineProps<{
    provider: any;
    providers: Array<any>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Applications', href: providerApplicationsIndex() },
            { title: 'Create', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="New Application" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">New Application</h1>
                <p class="text-sm text-muted-foreground">Create a credentialing application</p>
            </div>
            <Button variant="outline" @click="router.visit(providerApplicationsIndex())">
                <ArrowLeft class="size-4" />
                Back to Applications
            </Button>
        </div>

        <Card class="max-w-3xl shadow-none">
            <CardHeader>
                <CardTitle>Application Details</CardTitle>
                <CardDescription>Basic application information</CardDescription>
            </CardHeader>
            <CardContent>
                <Form v-bind="ProviderApplicationController.store.form()" class="space-y-6" v-slot="{ errors, processing }">
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
                            <Label for="type">Type *</Label>
                            <select id="type" name="type" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="initial">Initial</option>
                                <option value="recredentialing">Recredentialing</option>
                                <option value="modification">Modification</option>
                            </select>
                            <InputError :message="errors.type" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="status">Status *</Label>
                            <select id="status" name="status" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                                <option value="in_review">In Review</option>
                                <option value="approved">Approved</option>
                                <option value="denied">Denied</option>
                                <option value="withdrawn">Withdrawn</option>
                            </select>
                            <InputError :message="errors.status" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="effective_date">Effective Date</Label>
                            <Input id="effective_date" name="effective_date" type="date" />
                            <InputError :message="errors.effective_date" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="expiration_date">Expiration Date</Label>
                            <Input id="expiration_date" name="expiration_date" type="date" />
                            <InputError :message="errors.expiration_date" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="review_notes">Review Notes</Label>
                        <Textarea id="review_notes" name="review_notes" rows="3" />
                        <InputError :message="errors.review_notes" />
                    </div>

                    <div class="flex gap-2">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            Create Application
                        </Button>
                        <Button type="button" variant="outline" @click="router.visit(providerApplicationsIndex())">Cancel</Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
