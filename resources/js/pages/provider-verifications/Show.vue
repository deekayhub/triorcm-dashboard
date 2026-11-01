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
import { index as providerVerificationsIndex } from '@/routes/provider-verifications';

const props = defineProps<{
    verification: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Verifications', href: providerVerificationsIndex() },
            { title: 'Verification', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="verification.verification_source || 'Primary Source'" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight">{{
                    verification.verification_source || 'Primary Source'
                }}</h1>
                <p class="text-xs text-muted-foreground capitalize">{{
                    verification.type
                }}</p>
            </div>
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    @click="router.visit(providerVerificationsIndex())"
                    class="text-sm"
                >
                    <ArrowLeft class="size-3.5" />
                    Back
                </Button>
                <Button
                    @click="router.visit('/provider-verifications/' + verification.id + '/edit')"
                    class="text-sm"
                >
                    <Edit class="size-3.5 mr-1" />
                    Edit
                </Button>
            </div>
        </div>

        <Card class="w-full border border-border rounded-md">
            <CardHeader>
                <CardTitle>Verification Details</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-3 md:grid-cols-2">
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Provider</span>
                    <span class="font-medium text-sm capitalize">{{
                        verification.provider
                            ? verification.provider.first_name +
                                  ' ' +
                                  verification.provider.last_name
                            : '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Type</span>
                    <span class="capitalize text-xs font-medium">{{
                        verification.type
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Application</span>
                    <span class="font-medium text-sm">{{
                        verification.application
                            ? verification.application.application_number
                            : '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Source</span>
                    <span class="font-medium text-sm">{{
                        verification.verification_source || '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Status</span>
                    <Badge variant="outline" class="text-xs capitalize">
                        {{ verification.status }}
                    </Badge>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Requested At</span>
                    <span class="font-medium text-sm">{{
                        verification.requested_at ||
                        '-'
                    }}</span>
                </div>
                <div v-if="verification.received_at" class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Received At</span>
                    <span class="font-medium text-sm">{{
                        verification.received_at
                    }}</span>
                </div>
                <div v-if="verification.verified_at" class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Verified At</span>
                    <span class="font-medium text-sm">{{
                        verification.verified_at
                    }}</span>
                </div>
                <div
                    v-if="verification.notes"
                    class="mt-2 border-b border-border pb-2 last:border-0 last:pb-0"
                >
                    <span class="text-xs text-muted-foreground block">Notes</span>
                    <p class="text-sm">{{ verification.notes }}</p>
                </div>
                <div
                    v-if="verification.findings"
                    class="mt-2 border-b border-border pb-2 last:border-0 last:pb-0"
                >
                    <span class="text-xs text-muted-foreground block">Findings</span>
                    <p class="text-sm">{{ verification.findings }}</p>
                </div>
                <div
                    v-if="verification.verified_by"
                    class="flex items-center justify-between last:border-0 last:pb-0"
                >
                    <span class="text-xs text-muted-foreground">Verified By</span>
                    <span class="font-medium text-sm">{{
                        verification.verifiedBy?.name || '-'
                    }}</span>
                </div>
            </CardContent>
        </Card>

        <div class="grid gap-3 md:grid-cols-2">
            <Card v-if="verification.provider" class="w-full border border-border rounded-md">
                <CardHeader>
                    <CardTitle>Provider</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-2 text-sm">
                    <button
                        class="text-left"
                        @click="router.visit('/providers/' + verification.provider.id)"
                    >
                        <p class="font-medium">{{
                            verification.provider.first_name +
                                ' ' +
                                verification.provider.last_name
                        }}</p>
                        <p class="text-muted-foreground">{{
                            verification.provider.email
                        }}</p>
                        <p
                            v-if="verification.provider.npi"
                            class="text-xs text-muted-foreground"
                        >
                            NPI: {{ verification.provider.npi }}
                        </p>
                    </button>
                </CardContent>
            </Card>

            <Card v-if="verification.application" class="w-full border border-border rounded-md">
                <CardHeader>
                    <CardTitle>Application</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-2 text-sm">
                    <button
                        class="text-left"
                        @click="router.visit('/provider-applications/' + verification.application.id)"
                    >
                        <p class="font-medium">{{
                            verification.application.application_number
                        }}</p>
                        <p class="text-xs text-muted-foreground capitalize">{{
                            verification.application.type.replace(
                                '_',
                                ' '
                            )
                        }}</p>
                    </button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
