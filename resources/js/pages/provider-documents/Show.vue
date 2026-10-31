<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Edit } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { index as providerDocumentsIndex } from '@/routes/provider-documents';

const props = defineProps<{
    document: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Documents', href: providerDocumentsIndex() },
            { title: document.name, href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="document.name" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">{{ document.name }}</h1>
                <p class="text-sm text-muted-foreground capitalize">{{ document.type }}</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="router.visit(providerDocumentsIndex())">
                    <ArrowLeft class="size-4" />
                    Back
                </Button>
                <Button @click="router.visit(`/provider-documents/${document.id}/edit`)">
                    <Edit class="size-4" />
                    Edit
                </Button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>Document Details</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Provider</span>
                        <button class="text-primary capitalize">{{ document.provider?.first_name }} {{ document.provider?.last_name }}</button>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Type</span>
                        <span class="capitalize">{{ document.type }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Application</span>
                        <span>{{ document.application?.application_number || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">File Name</span>
                        <span>{{ document.file_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Mime Type</span>
                        <span>{{ document.mime_type || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">File Size</span>
                        <span>{{ document.file_size || 0 }} bytes</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Status</span>
                        <Badge variant="outline" class="capitalize">{{ document.status }}</Badge>
                    </div>
                    <div v-if="document.reviewed_by" class="flex justify-between">
                        <span class="text-muted-foreground">Reviewed By</span>
                        <span>{{ document.reviewedBy?.name || '-' }}</span>
                    </div>
                    <div v-if="document.reviewed_at" class="flex justify-between">
                        <span class="text-muted-foreground">Reviewed At</span>
                        <span>{{ document.reviewed_at ? new Date(document.reviewed_at).toLocaleString() : '-' }}</span>
                    </div>
                    <div v-if="document.review_notes" class="mt-2 text-sm">
                        <span class="text-muted-foreground">Notes</span>
                        <p>{{ document.review_notes }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>Provider</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-2 text-sm">
                    <button class="text-left" @click="router.visit(`/providers/${document.provider.id}`)">
                        <p class="font-medium">{{ document.provider?.first_name }} {{ document.provider?.last_name }}</p>
                        <p class="text-muted-foreground">{{ document.provider?.email }}</p>
                        <p v-if="document.provider?.npi" class="text-muted-foreground">NPI: {{ document.provider?.npi }}</p>
                    </button>
                </CardContent>
            </Card>

            <Card v-if="document.application" class="shadow-none">
                <CardHeader>
                    <CardTitle>Application</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-2 text-sm">
                    <button class="text-left" @click="router.visit(`/provider-applications/${document.application.id}`)">
                        <p class="font-medium">{{ document.application.application_number }}</p>
                        <p class="text-xs text-muted-foreground capitalize">{{ application.type.replace('_', ' ') }}</p>
                    </button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>