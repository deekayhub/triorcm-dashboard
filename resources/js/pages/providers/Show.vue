<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Edit } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight">{{
                    provider.full_name
                }}</h1>
                <p class="text-xs text-muted-foreground">
                    {{
                        provider.provider_type
                            ? provider.provider_type.replace('_', ' ')
                            : 'Provider'
                    }}
                </p>
            </div>
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    @click="router.visit(providersIndex())"
                    class="text-sm"
                >
                    <ArrowLeft class="size-3.5" />
                    Back
                </Button>
                <Button
                    @click="router.visit('/providers/' + provider.id + '/edit')"
                    class="text-sm"
                >
                    <Edit class="size-3.5 mr-1" />
                    Edit
                </Button>
            </div>
        </div>

        <Card class="w-full border border-border rounded-md">
            <CardHeader>
                <CardTitle>Provider Information</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-3 md:grid-cols-3">
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">NPI</span>
                    <span class="font-medium text-sm">{{
                        provider.npi || '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Email</span>
                    <span class="font-medium text-sm">{{
                        provider.email
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Phone</span>
                    <span class="font-medium text-sm">{{
                        provider.phone || '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Provider Type</span>
                    <span class="capitalize text-xs font-medium">
                        {{
                            provider.provider_type
                                ? provider.provider_type.replace('_', ' ')
                                : '-'
                        }}
                    </span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Specialty</span>
                    <span class="font-medium text-sm">{{
                        provider.specialty || '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Status</span>
                    <Badge variant="outline" class="text-xs capitalize">
                        {{ provider.status }}
                    </Badge>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Assigned User</span>
                    <span class="font-medium text-sm">{{
                        provider.user?.name || '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Team</span>
                    <span class="font-medium text-sm">{{
                        provider.team?.name || '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Created By</span>
                    <span class="font-medium text-sm">{{
                        provider.creator?.name || '-'
                    }}</span>
                </div>
            </CardContent>
        </Card>

        <div class="grid gap-3 md:grid-cols-3">
            <Card class="p-3 border border-border rounded-md">
                <div class="text-2xl font-semibold text-blue-600">{{
                    provider.applications_count ?? 0
                }}</div>
                <p class="text-xs text-muted-foreground mt-1">Applications</p>
            </Card>
            <Card class="p-3 border border-border rounded-md">
                <div class="text-2xl font-semibold text-emerald-600">{{
                    provider.licenses_count ?? 0
                }}</div>
                <p class="text-xs text-muted-foreground mt-1">Licenses</p>
            </Card>
            <Card class="p-3 border border-border rounded-md">
                <div class="text-2xl font-semibold text-purple-600">{{
                    provider.documents_count ?? 0
                }}</div>
                <p class="text-xs text-muted-foreground mt-1">Documents</p>
            </Card>
        </div>

        <Card class="w-full border border-border rounded-md">
            <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
            </CardHeader>
            <CardContent class="flex flex-wrap gap-2">
                <Button
                    @click="router.visit('/providers/' + provider.id + '/edit')"
                >
                    Edit Provider
                </Button>
                <Button
                    variant="outline"
                    @click="router.visit('/provider-applications/create?provider_id=' + provider.id)"
                >
                    New Application
                </Button>
                <Button
                    variant="outline"
                    @click="router.visit('/provider-licenses/create?provider_id=' + provider.id)"
                >
                    New License
                </Button>
                <Button
                    variant="outline"
                    @click="router.visit('/provider-documents/create?provider_id=' + provider.id)"
                >
                    New Document
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
