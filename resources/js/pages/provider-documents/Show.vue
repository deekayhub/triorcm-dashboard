<script setup lang="ts">
import { computed } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    Archive,
    ArrowLeft,
    CheckCircle2,
    Download,
    Edit,
    FileText,
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
import { index as providerDocumentsIndex } from '@/routes/provider-documents';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    document: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Documents', href: providerDocumentsIndex() },
            { title: 'Document', href: '#' },
        ],
    },
});

const { toast } = useToast();
const page = usePage();

const permissions = computed<string[]>(() => {
    const auth = page.props.auth as { permissions?: Array<string> } | undefined;

    return auth?.permissions ?? [];
});

const canArchive = computed(() => permissions.value.includes('providers.archive'));
const canReview = computed(() =>
    permissions.value.includes('providers.viewSensitive'),
);

const reviewForm = useForm({
    status: props.document.status === 'rejected' ? 'rejected' : 'approved',
    review_notes: props.document.review_notes ?? '',
});

const documentTypes: Record<string, string> = {
    cv: 'CV',
    license: 'License',
    dea: 'DEA',
    malpractice: 'Malpractice',
    education: 'Education',
    certification: 'Certification',
    other: 'Other',
};

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

function expiryBadge(): {
    label: string;
    variant: BadgeVariant;
    class: string;
} {
    if (!props.document.expiration_date) {
        return {
            label: 'No expiry',
            variant: 'secondary',
            class: 'bg-neutral-100 text-neutral-600',
        };
    }

    if (props.document.is_expired) {
        return { label: 'Expired', variant: 'destructive', class: '' };
    }

    if (props.document.is_expiring_soon) {
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

function submitReview() {
    reviewForm.patch(`/provider-documents/${props.document.id}/review`, {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title:
                    reviewForm.status === 'approved' ? 'Approved' : 'Rejected',
                description:
                    reviewForm.status === 'approved'
                        ? 'The document has been approved.'
                        : 'The document has been rejected.',
                variant: 'success',
            });
        },
        onError: () => {
            toast({
                title: 'Review failed',
                description:
                    'Please review the highlighted fields and try again.',
                variant: 'error',
            });
        },
    });
}

function archiveDocument() {
    if (
        !confirm(
            `Archive "${props.document.name}"? The file and its audit history will be retained.`,
        )
    ) {
        return;
    }

    router.post(
        `/provider-documents/${props.document.id}/archive`,
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
</script>

<template>
    <Head :title="document.name" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-semibold tracking-tight">
                        {{ document.name }}
                    </h1>
                    <Badge
                        :variant="statusBadge(document.status).variant"
                        :class="statusBadge(document.status).class"
                    >
                        {{ statusBadge(document.status).label }}
                    </Badge>
                    <Badge
                        v-if="document.archived_at"
                        variant="outline"
                        class="bg-neutral-100 text-neutral-600"
                    >
                        Archived
                    </Badge>
                </div>
                <p class="text-sm text-muted-foreground">
                    {{ documentTypes[document.type] ?? document.type }} ·
                    version {{ document.version }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button
                    variant="outline"
                    @click="router.visit(providerDocumentsIndex())"
                >
                    <ArrowLeft class="size-4" />
                    Back
                </Button>
                <a
                    v-if="document.file_exists && document.download_url"
                    :href="document.download_url"
                    class="inline-flex h-9 items-center rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent"
                >
                    <Download class="mr-2 size-4" />
                    Download
                </a>
                <Button
                    variant="outline"
                    @click="
                        router.visit(
                            `/provider-documents/${document.id}/edit`,
                        )
                    "
                >
                    <Edit class="size-4" />
                    Edit
                </Button>
                <Button
                    v-if="canArchive && !document.archived_at"
                    variant="outline"
                    class="text-destructive"
                    @click="archiveDocument"
                >
                    <Archive class="size-4" />
                    Archive
                </Button>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="space-y-4">
                <Card class="shadow-none">
                    <CardHeader>
                        <CardTitle>Document Details</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Provider</span>
                            <button
                                class="text-right font-medium text-primary"
                                @click="
                                    router.visit(
                                        `/providers/${document.provider_id}/profile`,
                                    )
                                "
                            >
                                {{ document.provider?.full_name || '—' }}
                            </button>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">
                                Application
                            </span>
                            <button
                                v-if="document.application"
                                class="text-right font-medium text-primary"
                                @click="
                                    router.visit(
                                        `/provider-applications/${document.application.id}`,
                                    )
                                "
                            >
                                {{ document.application.application_number }}
                            </button>
                            <span v-else>—</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Type</span>
                            <span class="capitalize">
                                {{
                                    documentTypes[document.type] ??
                                    document.type
                                }}
                            </span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">File Name</span>
                            <span class="truncate text-right">
                                {{ document.file_name }}
                            </span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Mime Type</span>
                            <span>{{ document.mime_type || '—' }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">File Size</span>
                            <span>{{ document.human_file_size }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Version</span>
                            <span>v{{ document.version }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">
                                Issue Date
                            </span>
                            <span>{{ formatDate(document.issue_date) }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">
                                Expiration Date
                            </span>
                            <div class="flex items-center gap-2">
                                <span>
                                    {{ formatDate(document.expiration_date) }}
                                </span>
                                <Badge
                                    :variant="expiryBadge().variant"
                                    :class="expiryBadge().class"
                                >
                                    {{ expiryBadge().label }}
                                </Badge>
                            </div>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Uploaded By</span>
                            <span>{{ document.uploader?.name || '—' }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Uploaded At</span>
                            <span>{{ formatDateTime(document.created_at) }}</span>
                        </div>
                        <div
                            v-if="document.checksum"
                            class="flex justify-between gap-4"
                        >
                            <span class="text-muted-foreground">
                                SHA-256
                            </span>
                            <span
                                class="max-w-[60%] truncate font-mono text-xs"
                                :title="document.checksum"
                            >
                                {{ document.checksum }}
                            </span>
                        </div>
                        <div
                            v-if="document.review_notes"
                            class="rounded-md border bg-neutral-50 px-3 py-2 text-xs text-neutral-600"
                        >
                            <p class="font-medium text-neutral-900">
                                Review notes
                            </p>
                            <p class="mt-1">{{ document.review_notes }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    v-if="(document.versions?.length ?? 0) > 1"
                    class="shadow-none"
                >
                    <CardHeader>
                        <CardTitle>Version History</CardTitle>
                        <CardDescription>
                            {{ document.versions.length }} versions of this
                            document
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="overflow-x-auto">
                        <table class="w-full min-w-[640px] text-sm">
                            <thead>
                                <tr
                                    class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase"
                                >
                                    <th class="px-3 py-3 font-medium">
                                        Version
                                    </th>
                                    <th class="px-3 py-3 font-medium">File</th>
                                    <th class="px-3 py-3 font-medium">Status</th>
                                    <th class="px-3 py-3 font-medium">
                                        Uploaded
                                    </th>
                                    <th class="px-3 py-3 font-medium">
                                        Expires
                                    </th>
                                    <th class="px-3 py-3 text-right font-medium">
                                        Download
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="version in [...document.versions].sort(
                                        (a: any, b: any) =>
                                            b.version - a.version,
                                    )"
                                    :key="version.id"
                                >
                                    <td class="px-3 py-3">
                                        v{{ version.version }}
                                        <Badge
                                            v-if="version.is_current"
                                            variant="outline"
                                            class="ml-1 bg-emerald-100 text-emerald-700"
                                        >
                                            Current
                                        </Badge>
                                    </td>
                                    <td class="px-3 py-3 text-neutral-500">
                                        {{ version.file_name }} ·
                                        {{ version.human_file_size }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <Badge
                                            :variant="
                                                statusBadge(version.status)
                                                    .variant
                                            "
                                            :class="
                                                statusBadge(version.status).class
                                            "
                                        >
                                            {{ statusBadge(version.status).label }}
                                        </Badge>
                                    </td>
                                    <td class="px-3 py-3 text-neutral-500">
                                        {{ formatDateTime(version.uploaded_at) }}
                                    </td>
                                    <td class="px-3 py-3 text-neutral-500">
                                        {{ formatDate(version.expiration_date) }}
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <a
                                            :href="version.download_url"
                                            class="inline-flex size-8 items-center justify-center rounded-md text-primary hover:bg-neutral-100"
                                            title="Download version"
                                        >
                                            <Download class="size-4" />
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-4">
                <Card class="shadow-none">
                    <CardHeader>
                        <CardTitle>Review</CardTitle>
                        <CardDescription>
                            {{
                                document.reviewer
                                    ? `Last reviewed by ${document.reviewer.name}`
                                    : 'This document has not been reviewed yet'
                            }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="mb-4 rounded-md border bg-neutral-50 px-3 py-2 text-sm">
                            <p class="text-xs text-muted-foreground">
                                Current decision
                            </p>
                            <p class="font-medium">
                                {{ statusBadge(document.status).label }}
                            </p>
                            <p
                                v-if="document.reviewed_at"
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                {{ formatDateTime(document.reviewed_at) }}
                            </p>
                        </div>

                        <form
                            v-if="canReview && !document.archived_at"
                            class="space-y-4"
                            @submit.prevent="submitReview"
                        >
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
                                <InputError
                                    :message="reviewForm.errors.status"
                                />
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
                                    placeholder="Add review notes"
                                />
                                <InputError
                                    :message="reviewForm.errors.review_notes"
                                />
                            </div>

                            <Button
                                v-if="reviewForm.status === 'rejected'"
                                type="submit"
                                variant="destructive"
                                class="w-full"
                                :disabled="reviewForm.processing"
                            >
                                <XCircle class="mr-2 size-4" />
                                {{
                                    reviewForm.processing
                                        ? 'Saving…'
                                        : 'Reject Document'
                                }}
                            </Button>
                            <Button
                                v-else
                                type="submit"
                                class="w-full"
                                :disabled="reviewForm.processing"
                            >
                                <CheckCircle2 class="mr-2 size-4" />
                                {{
                                    reviewForm.processing
                                        ? 'Saving…'
                                        : 'Approve Document'
                                }}
                            </Button>
                        </form>

                        <div
                            v-else
                            class="flex items-center gap-2 rounded-md border border-dashed px-3 py-4 text-sm text-neutral-500"
                        >
                            <FileText class="size-4" />
                            {{
                                document.archived_at
                                    ? 'Archived documents cannot be reviewed.'
                                    : 'You do not have permission to review documents.'
                            }}
                        </div>
                    </CardContent>
                </Card>

                <Card
                    v-if="document.provider"
                    class="shadow-none"
                >
                    <CardHeader>
                        <CardTitle>Provider</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-1 text-sm">
                        <button
                            class="text-left"
                            @click="
                                router.visit(
                                    `/providers/${document.provider.id}/profile`,
                                )
                            "
                        >
                            <p class="font-medium text-primary">
                                {{ document.provider.full_name }}
                            </p>
                            <p class="text-muted-foreground">
                                {{ document.provider.email }}
                            </p>
                            <p
                                v-if="document.provider.npi"
                                class="text-muted-foreground"
                            >
                                NPI: {{ document.provider.npi }}
                            </p>
                        </button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
