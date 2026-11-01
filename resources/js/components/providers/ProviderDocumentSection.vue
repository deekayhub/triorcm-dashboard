<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Archive,
    CheckCircle2,
    Download,
    FileText,
    History,
    RefreshCw,
    Upload,
    XCircle,
} from '@lucide/vue';
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
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    provider: any;
    documents: Array<any>;
}>();

const { toast } = useToast();
const page = usePage();

type DocumentMode = 'history' | 'replace' | 'review';

const showUpload = ref(false);
const expandedId = ref<number | null>(null);
const expandedMode = ref<DocumentMode | null>(null);
const uploadFileInput = ref<HTMLInputElement | null>(null);
const replaceFileInput = ref<HTMLInputElement | null>(null);

const documentTypes = [
    { value: 'cv', label: 'CV' },
    { value: 'license', label: 'License' },
    { value: 'dea', label: 'DEA' },
    { value: 'malpractice', label: 'Malpractice' },
    { value: 'education', label: 'Education' },
    { value: 'certification', label: 'Certification' },
    { value: 'other', label: 'Other' },
];

const uploadForm = useForm({
    type: 'cv',
    name: '',
    issue_date: '',
    expiration_date: '',
    file: null as File | null,
});

const replaceForm = useForm({
    file: null as File | null,
    name: '',
    issue_date: '',
    expiration_date: '',
});

const reviewForm = useForm({
    status: 'approved',
    review_notes: '',
});

const permissions = computed<string[]>(() => {
    const auth = page.props.auth as { permissions?: Array<string> } | undefined;

    return auth?.permissions ?? [];
});

const canUpdate = computed(() => permissions.value.includes('providers.update'));
const canArchive = computed(() => permissions.value.includes('providers.archive'));
const canReview = computed(() =>
    permissions.value.includes('providers.viewSensitive'),
);

const currentDocuments = computed(() =>
    (props.documents ?? []).filter((document) => document.is_current),
);

const pendingCount = computed(
    () =>
        currentDocuments.value.filter(
            (document) => document.status === 'pending',
        ).length,
);
const expiredCount = computed(
    () => currentDocuments.value.filter((document) => document.is_expired).length,
);
const expiringCount = computed(
    () =>
        currentDocuments.value.filter((document) => document.is_expiring_soon)
            .length,
);

const selectedDocument = computed(
    () =>
        (props.documents ?? []).find(
            (document) => document.id === expandedId.value,
        ) ?? null,
);

const selectedVersions = computed(() => {
    const versions = selectedDocument.value?.versions ?? [];

    return [...versions].sort(
        (a: any, b: any) => (b.version ?? 0) - (a.version ?? 0),
    );
});

function typeLabel(value: string): string {
    return documentTypes.find((type) => type.value === value)?.label ?? value;
}

function openUpload() {
    uploadForm.reset();
    uploadForm.clearErrors();

    if (uploadFileInput.value) {
        uploadFileInput.value.value = '';
    }

    showUpload.value = true;
}

function closeUpload() {
    showUpload.value = false;
    uploadForm.reset();
    uploadForm.clearErrors();
}

function onUploadFile(event: Event) {
    const input = event.target as HTMLInputElement;
    uploadForm.file = input.files?.[0] ?? null;
}

function submitUpload() {
    uploadForm.post(`/providers/${props.provider.id}/documents`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: 'Uploaded',
                description:
                    'Document uploaded successfully and is pending review.',
                variant: 'success',
            });
            showUpload.value = false;
            uploadForm.reset();
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

function togglePanel(document: any, mode: DocumentMode) {
    if (expandedId.value === document.id && expandedMode.value === mode) {
        expandedId.value = null;
        expandedMode.value = null;

        return;
    }

    expandedId.value = document.id;
    expandedMode.value = mode;

    if (mode === 'replace') {
        replaceForm.reset();
        replaceForm.clearErrors();
        replaceForm.name = document.name ?? '';
        replaceForm.issue_date = document.issue_date ?? '';
        replaceForm.expiration_date = document.expiration_date ?? '';

        if (replaceFileInput.value) {
            replaceFileInput.value.value = '';
        }
    }

    if (mode === 'review') {
        reviewForm.reset();
        reviewForm.clearErrors();
        reviewForm.status =
            document.status === 'rejected' ? 'rejected' : 'approved';
        reviewForm.review_notes = document.review_notes ?? '';
    }
}

function onReplaceFile(event: Event) {
    const input = event.target as HTMLInputElement;
    replaceForm.file = input.files?.[0] ?? null;
}

function submitReplacement() {
    if (!selectedDocument.value) {
        return;
    }

    replaceForm.post(
        `/providers/${props.provider.id}/documents/${selectedDocument.value.id}/replace`,
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
                expandedId.value = null;
                expandedMode.value = null;
                replaceForm.reset();
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

function submitReview() {
    if (!selectedDocument.value) {
        return;
    }

    reviewForm.patch(
        `/providers/${props.provider.id}/documents/${selectedDocument.value.id}/review`,
        {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    title:
                        reviewForm.status === 'approved'
                            ? 'Approved'
                            : 'Rejected',
                    description:
                        reviewForm.status === 'approved'
                            ? 'The document has been approved.'
                            : 'The document has been rejected.',
                    variant: 'success',
                });
                expandedId.value = null;
                expandedMode.value = null;
            },
            onError: () => {
                toast({
                    title: 'Review failed',
                    description:
                        'Please review the highlighted fields and try again.',
                    variant: 'error',
                });
            },
        },
    );
}

function archiveDocument(document: any) {
    if (
        !confirm(
            `Archive "${document.name}"? The file and its audit history will be retained.`,
        )
    ) {
        return;
    }

    router.post(
        `/providers/${props.provider.id}/documents/${document.id}/archive`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    title: 'Archived',
                    description: 'The document has been archived.',
                    variant: 'success',
                });
            },
            onError: () => {
                toast({
                    title: 'Error',
                    description: 'Failed to archive the document.',
                    variant: 'error',
                });
            },
        },
    );
}

type BadgeVariant = 'default' | 'outline' | 'destructive' | 'secondary';

function statusBadge(status: string): { label: string; variant: BadgeVariant; class: string } {
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

function expiryBadge(document: any): {
    label: string;
    variant: BadgeVariant;
    class: string;
} {
    if (!document.expiration_date) {
        return {
            label: 'No expiry',
            variant: 'secondary',
            class: 'bg-neutral-100 text-neutral-600',
        };
    }

    if (document.is_expired) {
        return { label: 'Expired', variant: 'destructive', class: '' };
    }

    if (document.is_expiring_soon) {
        return {
            label: 'Expires soon',
            variant: 'outline',
            class: 'bg-amber-100 text-amber-700 border-amber-200',
        };
    }

    return {
        label: 'Valid',
        variant: 'default',
        class: 'bg-emerald-100 text-emerald-700 border-emerald-200',
    };
}

function formatDate(value?: string | null): string {
    return value ? new Date(value).toLocaleDateString() : '—';
}

function formatDateTime(value?: string | null): string {
    return value ? new Date(value).toLocaleString() : '—';
}
</script>

<template>
    <Head title="Documents" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Documents</h1>
                <p class="text-sm text-muted-foreground">
                    Upload, version, review and track credentialing documents
                </p>
            </div>
            <Button v-if="canUpdate" size="sm" @click="openUpload">
                <Upload class="mr-2 size-4" />
                Upload Document
            </Button>
        </div>

        <div
            class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4"
            aria-label="Document summary"
        >
            <div class="rounded-lg border bg-white px-4 py-3">
                <p class="text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                    Current
                </p>
                <p class="mt-1 text-2xl font-semibold">{{ currentDocuments.length }}</p>
            </div>
            <div class="rounded-lg border bg-white px-4 py-3">
                <p class="text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                    Pending review
                </p>
                <p class="mt-1 text-2xl font-semibold">{{ pendingCount }}</p>
            </div>
            <div class="rounded-lg border bg-white px-4 py-3">
                <p class="text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                    Expiring soon
                </p>
                <p class="mt-1 text-2xl font-semibold">{{ expiringCount }}</p>
            </div>
            <div class="rounded-lg border bg-white px-4 py-3">
                <p class="text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                    Expired
                </p>
                <p class="mt-1 text-2xl font-semibold text-destructive">{{ expiredCount }}</p>
            </div>
        </div>

        <Card v-if="showUpload && canUpdate" class="max-w-3xl shadow-none">
            <CardHeader>
                <CardTitle>Upload Document</CardTitle>
                <CardDescription>
                    PDF, JPG, PNG, TIFF or Word · maximum 10 MB
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form class="space-y-5" @submit.prevent="submitUpload">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="document_type">Document Type *</Label>
                            <Select
                                id="document_type"
                                :model-value="uploadForm.type"
                                @update:model-value="
                                    uploadForm.type = String($event ?? '')
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
                            <InputError :message="uploadForm.errors.type" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="document_name">Document Name *</Label>
                            <Input
                                id="document_name"
                                v-model="uploadForm.name"
                                maxlength="255"
                                placeholder="e.g. Medical license — Ohio"
                            />
                            <InputError :message="uploadForm.errors.name" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="issue_date">Issue Date</Label>
                            <Input
                                id="issue_date"
                                v-model="uploadForm.issue_date"
                                type="date"
                            />
                            <InputError :message="uploadForm.errors.issue_date" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="expiration_date">Expiration Date</Label>
                            <Input
                                id="expiration_date"
                                v-model="uploadForm.expiration_date"
                                type="date"
                            />
                            <InputError
                                :message="uploadForm.errors.expiration_date"
                            />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="document_file">File *</Label>
                        <Input
                            id="document_file"
                            ref="uploadFileInput"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png,.tif,.tiff,.doc,.docx"
                            @change="onUploadFile"
                        />
                        <p
                            v-if="uploadForm.file"
                            class="text-xs text-muted-foreground"
                        >
                            {{ uploadForm.file.name }} ·
                            {{ Math.max(1, Math.round(uploadForm.file.size / 1024)) }} KB
                        </p>
                        <InputError :message="uploadForm.errors.file" />
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-2 border-t pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="uploadForm.processing"
                            @click="closeUpload"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="uploadForm.processing">
                            <Upload class="mr-2 size-4" />
                            {{ uploadForm.processing ? 'Uploading…' : 'Upload' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>

        <Card class="shadow-none">
            <CardHeader class="gap-1">
                <CardTitle>Credentialing Documents</CardTitle>
                <CardDescription>
                    {{ currentDocuments.length }} current document{{
                        currentDocuments.length === 1 ? '' : 's'
                    }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    v-if="currentDocuments.length === 0"
                    class="flex flex-col items-center gap-2 rounded-lg border border-dashed p-10 text-center"
                >
                    <FileText class="size-8 text-neutral-300" />
                    <p class="text-sm font-medium text-neutral-700">
                        No documents uploaded
                    </p>
                    <p class="text-sm text-neutral-500">
                        Upload licenses, certifications and supporting documents
                        to start the credentialing file.
                    </p>
                    <Button
                        v-if="canUpdate"
                        variant="outline"
                        size="sm"
                        class="mt-2"
                        @click="openUpload"
                    >
                        <Upload class="mr-2 size-4" />
                        Upload Document
                    </Button>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-sm">
                        <thead>
                            <tr
                                class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase"
                            >
                                <th scope="col" class="px-5 py-3 font-medium">
                                    Document
                                </th>
                                <th scope="col" class="px-5 py-3 font-medium">
                                    Version
                                </th>
                                <th scope="col" class="px-5 py-3 font-medium">
                                    Issued
                                </th>
                                <th scope="col" class="px-5 py-3 font-medium">
                                    Expires
                                </th>
                                <th scope="col" class="px-5 py-3 font-medium">
                                    Status
                                </th>
                                <th
                                    scope="col"
                                    class="px-5 py-3 text-right font-medium"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="document in currentDocuments"
                                :key="document.id"
                                class="align-top hover:bg-neutral-50"
                            >
                                <td class="px-5 py-3">
                                    <p class="font-medium">{{ document.name }}</p>
                                    <p class="text-xs text-neutral-500">
                                        {{ typeLabel(document.type) }} ·
                                        {{ document.file_name }} ·
                                        {{ document.human_file_size }}
                                    </p>
                                    <p
                                        v-if="!document.file_exists"
                                        class="mt-1 inline-flex items-center gap-1 text-xs text-amber-700"
                                    >
                                        <AlertTriangle class="size-3.5" />
                                        File unavailable
                                    </p>
                                </td>
                                <td class="px-5 py-3 text-neutral-500">
                                    v{{ document.version }}
                                </td>
                                <td class="px-5 py-3 text-neutral-500">
                                    {{ formatDate(document.issue_date) }}
                                </td>
                                <td class="px-5 py-3">
                                    <p class="text-neutral-500">
                                        {{ formatDate(document.expiration_date) }}
                                    </p>
                                    <Badge
                                        :variant="expiryBadge(document).variant"
                                        :class="expiryBadge(document).class"
                                        class="mt-1"
                                    >
                                        {{ expiryBadge(document).label }}
                                    </Badge>
                                </td>
                                <td class="px-5 py-3">
                                    <Badge
                                        :variant="statusBadge(document.status).variant"
                                        :class="statusBadge(document.status).class"
                                    >
                                        {{ statusBadge(document.status).label }}
                                    </Badge>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex flex-wrap justify-end gap-1">
                                        <a
                                            v-if="document.download_url && document.file_exists"
                                            :href="document.download_url"
                                            class="inline-flex h-8 items-center justify-center rounded-md px-2 text-sm font-medium text-primary hover:bg-neutral-100"
                                            title="Download document"
                                        >
                                            <Download class="size-4" />
                                        </a>
                                        <Button
                                            v-if="
                                                (document.versions?.length ?? 0) > 1
                                            "
                                            variant="ghost"
                                            size="sm"
                                            :class="{
                                                'bg-neutral-100':
                                                    expandedId === document.id &&
                                                    expandedMode === 'history',
                                            }"
                                            @click="togglePanel(document, 'history')"
                                        >
                                            <History class="mr-1.5 size-4" />
                                            History
                                        </Button>
                                        <Button
                                            v-if="canUpdate"
                                            variant="ghost"
                                            size="sm"
                                            :class="{
                                                'bg-neutral-100':
                                                    expandedId === document.id &&
                                                    expandedMode === 'replace',
                                            }"
                                            @click="togglePanel(document, 'replace')"
                                        >
                                            <RefreshCw class="mr-1.5 size-4" />
                                            New version
                                        </Button>
                                        <Button
                                            v-if="canReview"
                                            variant="ghost"
                                            size="sm"
                                            :class="{
                                                'bg-neutral-100':
                                                    expandedId === document.id &&
                                                    expandedMode === 'review',
                                            }"
                                            @click="togglePanel(document, 'review')"
                                        >
                                            <CheckCircle2 class="mr-1.5 size-4" />
                                            Review
                                        </Button>
                                        <Button
                                            v-if="canArchive"
                                            variant="ghost"
                                            size="sm"
                                            class="text-destructive"
                                            @click="archiveDocument(document)"
                                        >
                                            <Archive class="mr-1.5 size-4" />
                                            Archive
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <Card v-if="selectedDocument && expandedMode" class="shadow-none">
            <CardHeader class="gap-1">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <CardTitle>
                            <template v-if="expandedMode === 'history'">
                                Version History
                            </template>
                            <template v-else-if="expandedMode === 'replace'">
                                Upload New Version
                            </template>
                            <template v-else>Review Document</template>
                        </CardTitle>
                        <CardDescription>
                            {{ selectedDocument.name }} · v{{ selectedDocument.version }}
                        </CardDescription>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="
                            expandedId = null;
                            expandedMode = null;
                        "
                    >
                        Close
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <div v-if="expandedMode === 'history'" class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead>
                            <tr
                                class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase"
                            >
                                <th class="px-4 py-3 font-medium">Version</th>
                                <th class="px-4 py-3 font-medium">File</th>
                                <th class="px-4 py-3 font-medium">Uploaded</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Expires</th>
                                <th class="px-4 py-3 text-right font-medium">
                                    Download
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="version in selectedVersions"
                                :key="version.id"
                            >
                                <td class="px-4 py-3">
                                    v{{ version.version }}
                                    <Badge
                                        v-if="version.is_current"
                                        variant="outline"
                                        class="ml-1 bg-emerald-100 text-emerald-700"
                                    >
                                        Current
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-neutral-500">
                                    {{ version.file_name }} ·
                                    {{ version.human_file_size }}
                                </td>
                                <td class="px-4 py-3 text-neutral-500">
                                    {{ formatDateTime(version.uploaded_at) }}
                                </td>
                                <td class="px-4 py-3">
                                    <Badge
                                        :variant="statusBadge(version.status).variant"
                                        :class="statusBadge(version.status).class"
                                    >
                                        {{ statusBadge(version.status).label }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-neutral-500">
                                    {{ formatDate(version.expiration_date) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a
                                        :href="version.download_url"
                                        class="inline-flex h-8 items-center justify-center rounded-md px-2 text-primary hover:bg-neutral-100"
                                        title="Download version"
                                    >
                                        <Download class="size-4" />
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <form
                    v-else-if="expandedMode === 'replace'"
                    class="max-w-2xl space-y-5"
                    @submit.prevent="submitReplacement"
                >
                    <div class="grid gap-2">
                        <Label for="replacement_file">Replacement File *</Label>
                        <Input
                            id="replacement_file"
                            ref="replaceFileInput"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png,.tif,.tiff,.doc,.docx"
                            @change="onReplaceFile"
                        />
                        <p class="text-xs text-muted-foreground">
                            The current version is retained in history. PDF, JPG,
                            PNG, TIFF or Word · maximum 10 MB.
                        </p>
                        <InputError :message="replaceForm.errors.file" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="replacement_name">Document Name</Label>
                            <Input
                                id="replacement_name"
                                v-model="replaceForm.name"
                                maxlength="255"
                            />
                            <InputError :message="replaceForm.errors.name" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="replacement_expiration">
                                Expiration Date
                            </Label>
                            <Input
                                id="replacement_expiration"
                                v-model="replaceForm.expiration_date"
                                type="date"
                            />
                            <InputError
                                :message="replaceForm.errors.expiration_date"
                            />
                        </div>
                    </div>

                    <div class="grid max-w-xs gap-2">
                        <Label for="replacement_issue">Issue Date</Label>
                        <Input
                            id="replacement_issue"
                            v-model="replaceForm.issue_date"
                            type="date"
                        />
                        <InputError :message="replaceForm.errors.issue_date" />
                    </div>

                    <div class="flex justify-end gap-2 border-t pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="replaceForm.processing"
                            @click="
                                expandedId = null;
                                expandedMode = null;
                            "
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            :disabled="replaceForm.processing"
                        >
                            <RefreshCw class="mr-2 size-4" />
                            {{
                                replaceForm.processing
                                    ? 'Uploading…'
                                    : 'Upload New Version'
                            }}
                        </Button>
                    </div>
                </form>

                <form
                    v-else
                    class="max-w-2xl space-y-5"
                    @submit.prevent="submitReview"
                >
                    <div
                        class="rounded-md border bg-neutral-50 px-4 py-3 text-sm text-neutral-600"
                    >
                        <p class="font-medium text-neutral-900">
                            Current decision: {{ statusBadge(selectedDocument.status).label }}
                        </p>
                        <p v-if="selectedDocument.reviewer" class="mt-1 text-xs">
                            Reviewed by {{ selectedDocument.reviewer.name }} on
                            {{ formatDateTime(selectedDocument.reviewed_at) }}
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="review_status">Decision *</Label>
                            <Select
                                id="review_status"
                                :model-value="reviewForm.status"
                                @update:model-value="
                                    reviewForm.status = String($event ?? '')
                                "
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select decision" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="approved">
                                        Approve
                                    </SelectItem>
                                    <SelectItem value="rejected">
                                        Reject
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="reviewForm.errors.status" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="review_notes">
                            Review Notes
                            <span
                                v-if="reviewForm.status === 'rejected'"
                                class="text-destructive"
                            >
                                *
                            </span>
                        </Label>
                        <Textarea
                            id="review_notes"
                            v-model="reviewForm.review_notes"
                            rows="3"
                            placeholder="Add review notes for the credentialing record"
                        />
                        <InputError :message="reviewForm.errors.review_notes" />
                    </div>

                    <div class="flex flex-wrap justify-end gap-2 border-t pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="reviewForm.processing"
                            @click="
                                expandedId = null;
                                expandedMode = null;
                            "
                        >
                            Cancel
                        </Button>
                        <Button
                            v-if="reviewForm.status === 'rejected'"
                            type="submit"
                            variant="destructive"
                            :disabled="reviewForm.processing"
                        >
                            <XCircle class="mr-2 size-4" />
                            {{ reviewForm.processing ? 'Saving…' : 'Reject' }}
                        </Button>
                        <Button
                            v-else
                            type="submit"
                            :disabled="reviewForm.processing"
                        >
                            <CheckCircle2 class="mr-2 size-4" />
                            {{ reviewForm.processing ? 'Saving…' : 'Approve' }}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
