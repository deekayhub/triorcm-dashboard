<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Edit } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">{{ verification.verification_source || 'Primary Source' }}</h1>
                <p class="text-sm text-muted-foreground capitalize">{{ verification.type }}</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="router.visit(providerVerificationsIndex())">
                    <ArrowLeft class="size-4" />
                    Back
                </Button>
                <Button @click="router.visit(`/provider-verifications/${verification.id}/edit`)">
                    <Edit class="size-4" />
                    Edit
                </Button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>Verification Details</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Provider</span>
                        <button class="text-primary capitalize">{{ verification.provider?.first_name }} {{ verification.provider?.last_name }}</button>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Type</span>
                        <span class="capitalize">{{ verification.type }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Application</span>
                        <span>{{ verification.application?.application_number || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Verification Source</span>
                        <span>{{ verification.verification_source || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Status</span>
                        <Badge variant="outline" class="capitalize">{{ verification.status }}</Badge>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Requested At</span>
                        <span>{{ verification.requested_at ? new Date(verification.requested_at).toLocaleDateString() : '-' }}</span>
                    </div>
                    <div v-if="verification.received_at" class="flex justify-between">
                        <span class="text-muted-foreground">Received At</span>
                        <span>{{ new Date(verification.received_at).toLocaleDateString() }}</span>
                    </div>
                    <div v-if="verification.verified_at" class="flex justify-between">
                        <span class="text-muted-foreground">Verified At</span>
                        <span>{{ new Date(verification.verified_at).toLocaleDateString() }}</span>
                    </div>
                    <div v-if="verification.notes" class="mt-2 text-sm">
                        <span class="text-muted-foreground">Notes</span>
                        <p>{{ verification.notes }}</p>
                    </div>
                    <div v-if="verification.findings" class="mt-2 text-sm">
                        <span class="text-muted-foreground">Findings</span>
                        <p>{{ verification.findings }}</p>
                    </div>
                    <div v-if="verification.verified_by" class="mt-2 flex justify-between">
                        <span class="text-muted-foreground">Verified By</span>
                        <span>{{ verification.verifiedBy?.name || '-' }}</span>
                    </div>
                </CardContent>
            </Card>

            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>Provider</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-2 text-sm">
                    <button class="text-left" @click="router.visit(`/providers/${verification.provider.id}`)">
                        <p class="font-medium">{{ verification.provider?.first_name }} {{ verification.provider?.last_name }}</p>
                        <p class="text-muted-foreground">{{ verification.provider?.email }}</p>
                        <p v-if="verification.provider?.npi" class="text-muted-foreground">NPI: {{ verification.provider?.npi }}</p>
                    </button>
                </CardContent>
            </Card>

            <Card v-if="verification.application" class="shadow-none">
                <CardHeader>
                    <CardTitle>Application</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-2 text-sm">
                    <button class="text-left" @click="router.visit(`/provider-applications/${verification.application.id}`)">
                        <p class="font-medium">{{ verification.application.application_number }}</p>
                        <p class="text-xs text-muted-foreground capitalize">{{ verification.application.type.replace('_', ' ') }}</p>
                    </button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>