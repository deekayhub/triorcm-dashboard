<script setup lang="ts">
import { reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Archive, Download, FileText, Plus, Search } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index as providerDocumentsIndex } from '@/routes/provider-documents';
import { useToast } from '@/composables/useToast';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    documents: {
        data: Array<any>;
        links: Array<PaginationLink>;
    };
    providers: Array<any>;
    applications: Array<any>;
    filters: {
        provider_id: string | number | null;
        application_id: string | number | null;
        type: string | null;
        status: string | null;
        current_only: boolean;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Documents',
                href: providerDocumentsIndex(),
            },
        ],
    },
});

const { toast } = useToast();

const documentTypes = [
    { value: 'cv', label: 'CV' },
    { value: 'license', label: 'License' },
    { value: 'dea', label: 'DEA' },
    { value: 'malpractice', label: 'Malpractice' },
    { value: 'education', label: 'Education' },
    { value: 'certification', label: 'Certification' },
    { value: 'other', label: 'Other' },
];

const filters = reactive({
    provider_id: props.filters.provider_id
        ? String(props.filters.provider_id)
        : 'all',
    application_id: props.filters.application_id
        ? String(props.filters.application_id)
        : 'all',
    type: props.filters.type ?? 'all',
    status: props.filters.status ?? 'all',
    current_only: props.filters.current_only,
});

function applyFilters() {
    const params: Record<string, string> = {};

    if (filters.provider_id !== 'all') {
        params.provider_id = filters.provider_id;
    }

    if (filters.application_id !== 'all') {
        params.application_id = filters.application_id;
    }

    if (filters.type !== 'all') {
        params.type = filters.type;
    }

    if (filters.status !== 'all') {
        params.status = filters.status;
    }

    params.current_only = filters.current_only ? '1' : '0';

    router.get('/provider-documents', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearFilters() {
    filters.provider_id = 'all';
    filters.application_id = 'all';
    filters.type = 'all';
    filters.status = 'all';
    filters.current_only = true;
    applyFilters();
}

function typeLabel(value: string): string {
    return documentTypes.find((type) => type.value === value)?.label ?? value;
}

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
                label: 'Pending',
                variant: 'outline',
                class: 'bg-amber-100 text-amber-700 border-amber-200',
            };
    }
}

function expiryBadge(documentRecord: any): {
    label: string;
    variant: BadgeVariant;
    class: string;
} {
    if (!documentRecord.expiration_date) {
        return {
            label: 'No expiry',
            variant: 'secondary',
            class: 'bg-neutral-100 text-neutral-600',
        };
    }

    if (documentRecord.is_expired) {
        return { label: 'Expired', variant: 'destructive', class: '' };
    }

    if (documentRecord.is_expiring_soon) {
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

function archiveDocument(documentRecord: any) {
    if (
        !confirm(
            `Archive "${documentRecord.name}"? The file and its audit history will be retained.`,
        )
    ) {
        return;
    }

    router.delete(`/provider-documents/${documentRecord.id}`, {
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
    });
}
</script>

<template>
    <Head title="Documents" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Documents</h1>
                <p class="text-sm text-muted-foreground">
                    Manage provider credentialing documents, versions and review
                    decisions
                </p>
            </div>
            <Button @click="router.visit('/provider-documents/create')">
                <Plus class="size-4" />
                New Document
            </Button>
        </div>

        <Card class="shadow-none">
            <CardContent class="space-y-4 pt-6">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                    <div class="grid gap-1.5">
                        <Label for="filter-provider" class="text-xs">Provider</Label>
                        <Select
                            id="filter-provider"
                            :model-value="filters.provider_id"
                            @update:model-value="
                                filters.provider_id = String($event ?? 'all');
                                applyFilters();
                            "
                        >
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="All providers" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All providers</SelectItem>
                                <SelectItem
                                    v-for="provider in providers"
                                    :key="provider.id"
                                    :value="String(provider.id)"
                                >
                                    {{ provider.first_name }} {{ provider.last_name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="filter-application" class="text-xs">
                            Application
                        </Label>
                        <Select
                            id="filter-application"
                            :model-value="filters.application_id"
                            @update:model-value="
                                filters.application_id = String($event ?? 'all');
                                applyFilters();
                            "
                        >
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="All applications" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All applications
                                </SelectItem>
                                <SelectItem
                                    v-for="application in applications"
                                    :key="application.id"
                                    :value="String(application.id)"
                                >
                                    {{ application.application_number }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="filter-type" class="text-xs">Type</Label>
                        <Select
                            id="filter-type"
                            :model-value="filters.type"
                            @update:model-value="
                                filters.type = String($event ?? 'all');
                                applyFilters();
                            "
                        >
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="All types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All types</SelectItem>
                                <SelectItem
                                    v-for="type in documentTypes"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="filter-status" class="text-xs">Status</Label>
                        <Select
                            id="filter-status"
                            :model-value="filters.status"
                            @update:model-value="
                                filters.status = String($event ?? 'all');
                                applyFilters();
                            "
                        >
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="All statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All statuses</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="approved">Approved</SelectItem>
                                <SelectItem value="rejected">Rejected</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="flex flex-col justify-end gap-2">
                        <label
                            class="flex h-9 items-center gap-2 text-sm text-neutral-600"
                        >
                            <input
                                v-model="filters.current_only"
                                type="checkbox"
                                class="size-4 rounded border-neutral-300"
                                @change="applyFilters"
                            />
                            Current versions only
                        </label>
                        <Button variant="ghost" size="sm" @click="clearFilters">
                            <Search class="mr-1.5 size-3.5" />
                            Reset filters
                        </Button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[940px] text-sm">
                        <thead>
                            <tr
                                class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase"
                            >
                                <th scope="col" class="px-3 py-3 font-medium">
                                    Document
                                </th>
                                <th scope="col" class="px-3 py-3 font-medium">
                                    Provider
                                </th>
                                <th scope="col" class="px-3 py-3 font-medium">
                                    Application
                                </th>
                                <th scope="col" class="px-3 py-3 font-medium">
                                    Version
                                </th>
                                <th scope="col" class="px-3 py-3 font-medium">
                                    Expires
                                </th>
                                <th scope="col" class="px-3 py-3 font-medium">
                                    Status
                                </th>
                                <th scope="col" class="px-3 py-3 text-right font-medium">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="documentRecord in documents.data"
                                :key="documentRecord.id"
                                class="hover:bg-neutral-50"
                            >
                                <td class="px-3 py-3">
                                    <button
                                        class="text-left"
                                        @click="
                                            router.visit(
                                                `/provider-documents/${documentRecord.id}`,
                                            )
                                        "
                                    >
                                        <p class="font-medium">
                                            {{ documentRecord.name }}
                                        </p>
                                        <p class="text-xs text-neutral-500">
                                            {{ typeLabel(documentRecord.type) }} ·
                                            {{ documentRecord.human_file_size }}
                                        </p>
                                    </button>
                                </td>
                                <td class="px-3 py-3 text-neutral-500">
                                    {{
                                        documentRecord.provider
                                            ? documentRecord.provider.full_name
                                            : '—'
                                    }}
                                </td>
                                <td class="px-3 py-3 text-neutral-500">
                                    {{
                                        documentRecord.application
                                            ?.application_number || '—'
                                    }}
                                </td>
                                <td class="px-3 py-3 text-neutral-500">
                                    v{{ documentRecord.version }}
                                    <Badge
                                        v-if="!documentRecord.is_current"
                                        variant="outline"
                                        class="ml-1"
                                    >
                                        Superseded
                                    </Badge>
                                </td>
                                <td class="px-3 py-3">
                                    <p class="text-neutral-500">
                                        {{
                                            formatDate(
                                                documentRecord.expiration_date,
                                            )
                                        }}
                                    </p>
                                    <Badge
                                        :variant="
                                            expiryBadge(documentRecord).variant
                                        "
                                        :class="expiryBadge(documentRecord).class"
                                        class="mt-1"
                                    >
                                        {{ expiryBadge(documentRecord).label }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-3">
                                    <Badge
                                        :variant="
                                            statusBadge(documentRecord.status)
                                                .variant
                                        "
                                        :class="
                                            statusBadge(documentRecord.status)
                                                .class
                                        "
                                    >
                                        {{
                                            statusBadge(documentRecord.status)
                                                .label
                                        }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a
                                            v-if="
                                                documentRecord.download_url &&
                                                documentRecord.file_exists
                                            "
                                            :href="documentRecord.download_url"
                                            class="inline-flex size-8 items-center justify-center rounded-md text-primary hover:bg-neutral-100"
                                            title="Download document"
                                        >
                                            <Download class="size-4" />
                                        </a>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="
                                                router.visit(
                                                    `/provider-documents/${documentRecord.id}`,
                                                )
                                            "
                                        >
                                            View
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="
                                                router.visit(
                                                    `/provider-documents/${documentRecord.id}/edit`,
                                                )
                                            "
                                        >
                                            Edit
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="text-destructive"
                                            @click="
                                                archiveDocument(documentRecord)
                                            "
                                        >
                                            <Archive class="mr-1.5 size-4" />
                                            Archive
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="documents.data.length === 0">
                                <td
                                    colspan="7"
                                    class="px-3 py-12 text-center text-muted-foreground"
                                >
                                    <FileText
                                        class="mx-auto mb-2 size-8 text-neutral-300"
                                    />
                                    No documents found for the selected filters.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="documents.links.length > 3"
                    class="flex flex-wrap items-center justify-end gap-1 border-t pt-4"
                >
                    <template
                        v-for="link in documents.links"
                        :key="link.label"
                    >
                        <Button
                            v-if="link.url"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            @click="router.get(link.url)"
                        >
                            <span v-html="link.label" />
                        </Button>
                        <Button
                            v-else
                            variant="outline"
                            size="sm"
                            disabled
                        >
                            <span v-html="link.label" />
                        </Button>
                    </template>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
