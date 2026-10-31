<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Edit } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { index as providerApplicationsIndex } from '@/routes/provider-applications';

const props = defineProps<{
    application: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Applications', href: providerApplicationsIndex() },
            { title: 'Application', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="application.application_number" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">{{ application.application_number }}</h1>
                <p class="text-sm text-muted-foreground capitalize">
                    {{ application.type.replace('_', ' ') }} application
                </p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="router.visit(providerApplicationsIndex())">
                    <ArrowLeft class="size-4" />
                    Back
                </Button>
                <Button @click="router.visit(`/provider-applications/${application.id}/edit`)">
                    <Edit class="size-4" />
                    Edit
                </Button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>Application Info</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Status</span>
                        <Badge variant="outline" class="capitalize">{{ application.status.replace('_', ' ') }}</Badge>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Type</span>
                        <span class="capitalize">{{ application.type.replace('_', ' ') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Submitted</span>
                        <span>{{ application.submitted_at ? new Date(application.submitted_at).toLocaleString() : '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Reviewed</span>
                        <span>{{ application.reviewed_at ? new Date(application.reviewed_at).toLocaleString() : '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Effective Date</span>
                        <span>{{ application.effective_date || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Expiration Date</span>
                        <span>{{ application.expiration_date || '-' }}</span>
                    </div>
                </CardContent>
            </Card>

            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>Provider</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-2 text-sm">
                    <button class="text-left" @click="router.visit(`/providers/${application.provider?.id}`)">
                        <p class="font-medium">{{ application.provider?.first_name }} {{ application.provider?.last_name }}</p>
                        <p class="text-muted-foreground">{{ application.provider?.email }}</p>
                        <p v-if="application.provider?.npi" class="text-muted-foreground">NPI: {{ application.provider?.npi }}</p>
                    </button>
                </CardContent>
            </Card>
        </div>

        <Card class="shadow-none">
            <CardHeader>
                <CardTitle>Credentialing Steps</CardTitle>
                <CardDescription>{{ application.steps?.length || 0 }} steps</CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="application.steps?.length" class="space-y-2">
                    <div v-for="step in application.steps" :key="step.id" class="flex items-center justify-between rounded-lg border p-3">
                        <div>
                            <p class="font-medium">{{ step.name }}</p>
                            <p class="text-xs text-muted-foreground">Order: {{ step.order }}</p>
                        </div>
                        <Badge variant="outline" class="capitalize">{{ step.status.replace('_', ' ') }}</Badge>
                    </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">No steps yet</p>
            </CardContent>
        </Card>
    </div>
</template>
