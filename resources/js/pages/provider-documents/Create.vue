<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Upload } from '@lucide/vue';
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
import InputError from '@/components/InputError.vue';
import { index as providerDocumentsIndex } from '@/routes/provider-documents';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    provider: any;
    application: any;
    providers: Array<any>;
    applications: Array<any>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Documents', href: providerDocumentsIndex() },
            { title: 'Create', href: '#' },
        ],
    },
});

const { toast } = useToast();
const fileInput = ref<HTMLInputElement | null>(null);

const documentTypes = [
    { value: 'cv', label: 'CV' },
    { value: 'license', label: 'License' },
    { value: 'dea', label: 'DEA' },
    { value: 'malpractice', label: 'Malpractice' },
    { value: 'education', label: 'Education' },
    { value: 'certification', label: 'Certification' },
    { value: 'other', label: 'Other' },
];

const form = useForm({
    provider_id: props.provider ? String(props.provider.id) : '',
    provider_application_id: props.application
        ? String(props.application.id)
        : 'none',
    type: 'cv',
    name: '',
    issue_date: '',
    expiration_date: '',
    file: null as File | null,
});

const selectedProvider = computed(() =>
    props.providers.find(
        (provider) => String(provider.id) === form.provider_id,
    ),
);

const providerApplications = computed(() =>
    props.applications.filter(
        (application) =>
            !form.provider_id ||
            String(application.provider_id) === form.provider_id,
    ),
);

function onFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    form.file = input.files?.[0] ?? null;
}

function submit() {
    form.transform((data) => ({
        ...data,
        provider_application_id:
            data.provider_application_id === 'none'
                ? null
                : data.provider_application_id,
    }));

    form.post('/provider-documents', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: 'Uploaded',
                description:
                    'Document uploaded successfully and is pending review.',
                variant: 'success',
            });
        },
        onError: () => {
            toast({
                title: 'Upload failed',
                description:
                    'Please review the highlighted fields and try again.',
                variant: 'error',
            });
        },
    });
}
</script>

<template>
    <Head title="New Document" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">
                    New Document
                </h1>
                <p class="text-sm text-muted-foreground">
                    Upload a credentialing document for a provider
                </p>
            </div>
            <Button
                variant="outline"
                @click="router.visit(providerDocumentsIndex())"
            >
                <ArrowLeft class="size-4" />
                Back to Documents
            </Button>
        </div>

        <Card class="max-w-3xl shadow-none">
            <CardHeader>
                <CardTitle>Document Information</CardTitle>
                <CardDescription>
                    PDF, JPG, PNG, TIFF or Word · maximum 10 MB. Uploads are
                    pending review by default.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form class="space-y-6" @submit.prevent="submit">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="provider_id">Provider *</Label>
                            <Select
                                id="provider_id"
                                :model-value="form.provider_id"
                                @update:model-value="
                                    form.provider_id = String($event ?? '')
                                "
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select provider" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="providerOption in providers"
                                        :key="providerOption.id"
                                        :value="String(providerOption.id)"
                                    >
                                        {{ providerOption.first_name }}
                                        {{ providerOption.last_name }}
                                        ({{ providerOption.email }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.provider_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="provider_application_id">
                                Application
                            </Label>
                            <Select
                                id="provider_application_id"
                                :model-value="form.provider_application_id"
                                @update:model-value="
                                    form.provider_application_id = String(
                                        $event ?? '',
                                    )
                                "
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="No application" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">
                                        No application
                                    </SelectItem>
                                    <SelectItem
                                        v-for="applicationOption in providerApplications"
                                        :key="applicationOption.id"
                                        :value="String(applicationOption.id)"
                                    >
                                        {{ applicationOption.application_number }}
                                        ({{ applicationOption.type }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError
                                :message="form.errors.provider_application_id"
                            />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="type">Type *</Label>
                            <Select
                                id="type"
                                :model-value="form.type"
                                @update:model-value="
                                    form.type = String($event ?? '')
                                "
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="type in documentTypes"
                                        :key="type.value"
                                        :value="type.value"
                                    >
                                        {{ type.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.type" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="name">Document Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                maxlength="255"
                                placeholder="e.g. DEA registration certificate"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="issue_date">Issue Date</Label>
                            <Input
                                id="issue_date"
                                v-model="form.issue_date"
                                type="date"
                            />
                            <InputError :message="form.errors.issue_date" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="expiration_date">Expiration Date</Label>
                            <Input
                                id="expiration_date"
                                v-model="form.expiration_date"
                                type="date"
                            />
                            <InputError
                                :message="form.errors.expiration_date"
                            />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="file">File *</Label>
                        <Input
                            id="file"
                            ref="fileInput"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png,.tif,.tiff,.doc,.docx"
                            @change="onFileChange"
                        />
                        <p
                            v-if="form.file"
                            class="text-xs text-muted-foreground"
                        >
                            {{ form.file.name }} ·
                            {{ Math.max(1, Math.round(form.file.size / 1024)) }}
                            KB
                        </p>
                        <InputError :message="form.errors.file" />
                    </div>

                    <div
                        v-if="selectedProvider"
                        class="rounded-md border bg-neutral-50 px-4 py-3 text-sm text-neutral-600"
                    >
                        Uploading for
                        <span class="font-medium text-neutral-900">
                            {{ selectedProvider.first_name }}
                            {{ selectedProvider.last_name }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between border-t pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            @click="router.visit(providerDocumentsIndex())"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Upload class="mr-2 size-4" />
                            {{ form.processing ? 'Uploading…' : 'Upload Document' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
