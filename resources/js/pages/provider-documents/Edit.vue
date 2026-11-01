<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, RefreshCw, Save } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
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
    document: any;
    applications: Array<any>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Documents', href: providerDocumentsIndex() },
            { title: 'Document', href: '#' },
            { title: 'Edit', href: '#' },
        ],
    },
});

const { toast } = useToast();
const replacementFileInput = ref<HTMLInputElement | null>(null);

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
    provider_id: props.document.provider_id,
    provider_application_id: props.document.provider_application_id
        ? String(props.document.provider_application_id)
        : 'none',
    type: props.document.type,
    name: props.document.name,
    issue_date: props.document.issue_date ?? '',
    expiration_date: props.document.expiration_date ?? '',
});

const replacementForm = useForm({
    file: null as File | null,
    name: props.document.name,
    issue_date: props.document.issue_date ?? '',
    expiration_date: props.document.expiration_date ?? '',
});

const providerApplications = computed(() =>
    props.applications.filter(
        (application) =>
            String(application.provider_id) === String(form.provider_id),
    ),
);

type BadgeVariant = 'default' | 'outline' | 'destructive' | 'secondary';

function statusBadge(status: string): {
    label: string;
    variant: BadgeVariant;
    class: string;
} {
    switch (status) {
        case 'approved':
            return {
                label: 'Approved',
                variant: 'default',
                class: 'bg-emerald-100 text-emerald-700 border-emerald-200',
            };
        case 'rejected':
            return {
                label: 'Rejected',
                variant: 'destructive',
                class: '',
            };
        default:
            return {
                label: 'Pending review',
                variant: 'outline',
                class: 'bg-amber-100 text-amber-700 border-amber-200',
            };
    }
}

function submit() {
    form.transform((data) => ({
        ...data,
        provider_application_id:
            data.provider_application_id === 'none'
                ? null
                : data.provider_application_id,
    }));

    form.patch(`/provider-documents/${props.document.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: 'Saved',
                description: 'Document updated successfully.',
                variant: 'success',
            });
        },
        onError: () => {
            toast({
                title: 'Update failed',
                description:
                    'Please review the highlighted fields and try again.',
                variant: 'error',
            });
        },
    });
}

function onReplacementFile(event: Event) {
    const input = event.target as HTMLInputElement;
    replacementForm.file = input.files?.[0] ?? null;
}

function submitReplacement() {
    replacementForm.post(
        `/provider-documents/${props.document.id}/replace`,
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    title: 'New version uploaded',
                    description:
                        'The replacement version is pending review.',
                    variant: 'success',
                });
                replacementForm.reset();

                if (replacementFileInput.value) {
                    replacementFileInput.value.value = '';
                }
            },
            onError: () => {
                toast({
                    title: 'Upload failed',
                    description:
                        'Please review the highlighted fields and try again.',
                    variant: 'error',
                });
            },
        },
    );
}
</script>

<template>
    <Head title="Edit Document" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">
                    Edit Document
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ document.name }}
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

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>Document Details</CardTitle>
                    <CardDescription>
                        Update the document metadata. Upload a new version from
                        the panel on the right to replace the file.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="space-y-6" @submit.prevent="submit">
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
                                />
                                <InputError :message="form.errors.name" />
                            </div>
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
                                        v-for="application in providerApplications"
                                        :key="application.id"
                                        :value="String(application.id)"
                                    >
                                        {{ application.application_number }}
                                        ({{ application.type }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError
                                :message="form.errors.provider_application_id"
                            />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="issue_date">Issue Date</Label>
                                <Input
                                    id="issue_date"
                                    v-model="form.issue_date"
                                    type="date"
                                />
                                <InputError
                                    :message="form.errors.issue_date"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="expiration_date">
                                    Expiration Date
                                </Label>
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

                        <div class="flex items-center justify-between border-t pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                @click="
                                    router.visit(
                                        `/provider-documents/${document.id}`,
                                    )
                                "
                            >
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="mr-2 size-4" />
                                {{ form.processing ? 'Saving…' : 'Save Changes' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <div class="space-y-4">
                <Card class="shadow-none">
                    <CardHeader>
                        <CardTitle>Current File</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted-foreground">Version</span>
                            <span class="font-medium">v{{ document.version }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted-foreground">Status</span>
                            <Badge
                                :variant="statusBadge(document.status).variant"
                                :class="statusBadge(document.status).class"
                            >
                                {{ statusBadge(document.status).label }}
                            </Badge>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted-foreground">File</span>
                            <span class="truncate text-right font-medium">
                                {{ document.file_name }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted-foreground">Size</span>
                            <span>{{ document.human_file_size }}</span>
                        </div>
                        <div
                            v-if="document.review_notes"
                            class="rounded-md border bg-neutral-50 px-3 py-2 text-xs text-neutral-600"
                        >
                            {{ document.review_notes }}
                        </div>
                    </CardContent>
                </Card>

                <Card class="shadow-none">
                    <CardHeader>
                        <CardTitle>Upload New Version</CardTitle>
                        <CardDescription>
                            The current file is retained in version history and
                            the new file returns to pending review.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form
                            class="space-y-4"
                            @submit.prevent="submitReplacement"
                        >
                            <div class="grid gap-2">
                                <Label for="replacement_file">File *</Label>
                                <Input
                                    id="replacement_file"
                                    ref="replacementFileInput"
                                    type="file"
                                    accept=".pdf,.jpg,.jpeg,.png,.tif,.tiff,.doc,.docx"
                                    @change="onReplacementFile"
                                />
                                <InputError
                                    :message="replacementForm.errors.file"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="replacement_expiration">
                                    Expiration Date
                                </Label>
                                <Input
                                    id="replacement_expiration"
                                    v-model="replacementForm.expiration_date"
                                    type="date"
                                />
                                <InputError
                                    :message="
                                        replacementForm.errors.expiration_date
                                    "
                                />
                            </div>

                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="replacementForm.processing"
                            >
                                <RefreshCw class="mr-2 size-4" />
                                {{
                                    replacementForm.processing
                                        ? 'Uploading…'
                                        : 'Upload New Version'
                                }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
