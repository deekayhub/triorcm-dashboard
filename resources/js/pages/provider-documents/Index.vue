<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle, Plus, FileText, LayoutDashboard } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { index as providerDocumentsIndex } from '@/routes/provider-documents';
import { useToast } from '@/composables/useToast';
import { ref } from 'vue';

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

const isDeleteOpen = ref(false);
let currentDocumentId = null;

function confirmDelete(id: number) {
    currentDocumentId = id;
    isDeleteOpen.value = true;
}

// Close dialog when clicking outside or on ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        isDeleteOpen.value = false;
    }
});
</script>

<template>
    <Head title="Documents" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6 bg-white rounded-lg shadow-sm border border-border">

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase">
                        Documentation
                    </p>
                </div>
                <h1 class="text-2xl font-semibold tracking-tight">Documents</h1>
                <p class="text-sm text-muted-foreground">
                    Manage provider credentialing documents
                </p>
            </div>
            <Button @click="router.visit('/provider-documents/create')">
                <Plus class="size-4" />
                New Document
            </Button>
        </div>

        <Card class="gap-0 rounded-lg shadow-sm border border-border">
            <CardContent class="px-5 pb-4">
                <p class="text-sm text-muted-foreground">Total: {{ providerDocumentsIndex.data.length }}</p>
                <table class="w-full text-sm mt-3">
                    <thead>
                        <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                            <th class="px-5 py-2.5 font-medium">Document</th>
                            <th class="px-5 py-2.5 font-medium">Provider</th>
                            <th class="px-5 py-2.5 font-medium">Application</th>
                            <th class="px-5 py-2.5 font-medium">Type</th>
                            <th class="px-5 py-2.5 font-medium">Status</th>
                            <th class="px-5 py-2.5 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="doc in providerDocumentsIndex.data" :key="doc.id" class="border-b last:border-0 hover:bg-neutral-50 cursor-pointer">
                            <td class="px-5 py-3">
                                <button class="text-left" @click="router.visit(`/provider-documents/${doc.id}`)">
                                    <p class="font-medium">{{ doc.document_type }}</p>
                                </button>
                            </td>
                            <td class="px-5 py-3 text-neutral-500">
                                {{ doc.provider ? `${doc.provider.first_name} ${doc.provider.last_name}` : '-' }}
                            </td>
                            <td class="px-5 py-3 text-neutral-500">
                                {{ doc.application?.application_number || '-' }}
                            </td>
                            <td class="px-5 py-3 text-neutral-500 capitalize">{{ doc.document_type }}</td>
                            <td class="px-5 py-3">
                                <Badge variant="outline" class="capitalize">{{ doc.status }}</Badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Button variant="ghost" size="sm" @click="router.visit(`/provider-documents/${doc.id}/edit`)">Edit</Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="confirmDelete(doc.id)">Delete</Button>
                            </td>
                        </tr>
                        <tr v-if="providerDocumentsIndex.data.length === 0">
                            <td colspan="6" class="px-5 py-6 text-center text-muted-foreground">No documents found</td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </card>
    </div>
</template>