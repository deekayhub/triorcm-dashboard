<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Edit } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { index as providersIndex } from '@/routes/providers';

const props = defineProps<{
    provider: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Providers', href: providersIndex() },
            { title: 'Provider', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="provider.full_name" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">{{ provider.full_name }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ provider.provider_type ? provider.provider_type.replace('_', ' ') : 'Provider' }}
                </p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="router.visit(providersIndex())">
                    <ArrowLeft class="size-4" />
                    Back
                </Button>
                <Button @click="router.visit(`/providers/${provider.id}/edit`)">
                    <Edit class="size-4" />
                    Edit
                </Button>
            </div>
        </div>

        <!-- Provider Header & Status -->
        <Card class="shadow-none">
            <CardHeader>
                <CardTitle>Provider Information</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-4 md:grid-cols-3">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">NPI</span>
                    <span class="font-medium">{{ provider.npi || '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Email</span>
                    <span class="font-medium">{{ provider.email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Phone</span>
                    <span class="font-medium">{{ provider.phone || '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Provider Type</span>
                    <span class="capitalize">{{ provider.provider_type ? provider.provider_type.replace('_', ' ') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Specialty</span>
                    <span class="font-medium">{{ provider.specialty || '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Status</span>
                    <Badge variant="outline" class="capitalize">{{ provider.status }}</Badge>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Assigned User</span>
                    <span class="font-medium">{{ provider.user?.name || '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Team</span>
                    <span class="font-medium">{{ provider.team?.name || '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Created By</span>
                    <span class="font-medium">{{ provider.creator?.name || '-' }}</span>
                </div>
            </CardContent>
        </Card>

        <!-- Summary Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <Card class="shadow-none p-4 border border-border">
                <div class="text-2xl font-semibold text-blue-600">{{ provider.applications_count ?? 0 }}</div>
                <p class="text-sm text-muted-foreground mt-1">Applications</p>
            </Card>
            <Card class="shadow-none p-4 border border-border">
                <div class="text-2xl font-semibold text-green-600">{{ provider.licenses_count ?? 0 }}</div>
                <p class="text-sm text-muted-foreground mt-1">Licenses</p>
            </Card>
            <Card class="shadow-none p-4 border border-border">
                <div class="text-2xl font-semibold text-purple-600">{{ provider.documents_count ?? 0 }}</div>
                <p class="text-sm text-muted-foreground mt-1">Documents</p>
            </Card>
        </div>

        <!-- Quick Actions -->
        <Card class="shadow-none">
            <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
            </CardHeader>
            <CardContent class="flex gap-3">
                <Button @click="router.visit(`/providers/${provider.id}/edit`)">
                    Edit Provider
                </Button>
                <Button variant="outline" @click="router.visit(`/provider-applications/create?provider_id=${provider.id}`)">
                    New Application
                </Button>
                <Button variant="outline" @click="router.visit(`/provider-licenses/create?provider_id=${provider.id}`)">
                    New License
                </Button>
                <Button variant="outline" @click="router.visit(`/provider-documents/create?provider_id=${provider.id}`)">
                    New Document
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
