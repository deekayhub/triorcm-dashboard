<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { index as providerDocumentsIndex } from '@/routes/provider-documents';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    document: any;
    providers: Array<any>;
    applications: Array<any>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Documents', href: providerDocumentsIndex() },
            { title: document.name, href: '#' },
            { title: 'Edit', href: '#' },
        ],
    },
});

const { toast } = useToast();
</script>

<template>
    <Head title="Edit Document" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6 bg-white rounded-lg shadow-sm border border-border">

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Edit Document</h1>
                <p class="text-sm text-muted-foreground">{{ document.name }}</p>
            </div>
            <Button variant="outline" @click="router.visit(providerDocumentsIndex())">
                <ArrowLeft class="size-4" />
                Back to Documents
            </Button>
        </div>

        <Card class="max-w-4xl shadow-none">
            <CardHeader>
                <CardTitle>Document Details</CardTitle>
            </CardHeader>
            <CardContent>
                <Form v-bind="ProviderDocumentController.update.form(document.id)" class="space-y-6" v-slot="{ errors, processing }">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="provider_id">Provider *</Label>
                            <select id="provider_id" name="provider_id" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm" :value="document.provider.id">
                                <option value="">Select provider</option>
                                <option v-for="p in providers" :key="p.id" :value="p.id" :selected="document.provider.id === p.id">
                                    {{ p.first_name }} {{ p.last_name }} ({{ p.email }})
                                </option>
                            </select>
                            <InputError :message="errors.provider_id" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="provider_application_id">Application</Label>
                            <select id="provider_application_id" name="provider_application_id" class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm">
                                <option value="">No application</option>
                                <option v-for="app in applications" :key="app.id" :value="app.id">
                                    {{ app.application_number }} ({{ app.type.replace('_', ' ') }})
                                </option>
                            </select>
                            <InputError :message="errors.provider_application_id" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="type">Type *</Label>
                            <select id="type" name="type" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm" :value="document.type">
                                <option value="cv">CV</option>
                                <option value="license">License</option>
                                <option value="dea">DEA</option>
                                <option value="malpractice">Malpractice</option>
                                <option value="education">Education</option>
                                <option value="certification">Certification</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="errors.type" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="name">Document Name *</Label>
                            <Input id="name" name="name" required :value="document.name" />
                            <InputError :message="errors.name" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="file_path">File Path *</Label>
                            <Input id="file_path" name="file_path" required :value="document.file_path" />
                            <InputError :message="errors.file_path" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="file_name">File Name *</Label>
                            <Input id="file_name" name="file_name" required :value="document.file_name" />
                            <InputError :message="errors.file_name" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="mime_type">Mime Type</Label>
                            <Input id="mime_type" name="mime_type" :value="document.mime_type" />
                            <InputError :message="errors.mime_type" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="file_size">File Size</Label>
                            <Input id="file_size" name="file_size" type="number" :value="document.file_size" />
                            <InputError :message="errors.file_size" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="status">Status *</Label>
                            <select id="status" name="status" required class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm" :value="document.status">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <InputError :message="errors.status" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="reviewed_by">Reviewed By</Label>
                            <select id="reviewed_by" name="reviewed_by" class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm" :value="document.reviewed_by ? document.reviewed_by : ''">
                                <option value="">None</option>
                            </select>
                            <InputError :message="errors.reviewed_by" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="reviewed_at">Reviewed Date</Label>
                            <Input id="reviewed_at" name="reviewed_at" type="date" :default-value="document.reviewed_at" />
                            <InputError :message="errors.reviewed_at" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="review_notes">Review Notes</Label>
                            <Textarea id="review_notes" name="review_notes" rows="4" :default-value="document.review_notes" />
                            <InputError :message="errors.review_notes" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" />
                            Update Document
                        </Button>
                        <Button type="button" variant="outline" @click="router.visit(`/provider-documents/${document.id}`)">Cancel</Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>