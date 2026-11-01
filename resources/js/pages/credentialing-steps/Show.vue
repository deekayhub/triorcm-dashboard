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
import { index as credentialingStepsIndex } from '@/routes/credentialing-steps';

const props = defineProps<{
    step: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Credentialing Steps', href: credentialingStepsIndex() },
            { title: 'Step', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="step.name" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight">{{ step.name }}</h1>
                <p class="text-xs text-muted-foreground capitalize">{{
                    step.status
                }}</p>
            </div>
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    @click="router.visit(credentialingStepsIndex())"
                    class="text-sm"
                >
                    <ArrowLeft class="size-3.5" />
                    Back
                </Button>
                <Button
                    @click="router.visit('/credentialing-steps/' + step.id + '/edit')"
                    class="text-sm"
                >
                    <Edit class="size-3.5 mr-1" />
                    Edit
                </Button>
            </div>
        </div>

        <Card class="w-full border border-border rounded-md">
            <CardHeader>
                <CardTitle>Step Details</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-3 md:grid-cols-2">
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Application</span>
                    <span class="font-medium text-sm">{{
                        step.application
                            ? step.application.application_number
                            : '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Order</span>
                    <span class="font-medium text-sm">{{ step.order }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Status</span>
                    <Badge variant="outline" class="text-xs capitalize">
                        {{ step.status }}
                    </Badge>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Assigned To</span>
                    <span class="font-medium text-sm">{{
                        step.assignedTo?.name || '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Started At</span>
                    <span class="font-medium text-sm">{{
                        step.started_at || '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Completed At</span>
                    <span class="font-medium text-sm">{{
                        step.completed_at || '-'
                    }}</span>
                </div>
                <div
                    v-if="step.notes"
                    class="mt-2 border-b border-border pb-2 last:border-0 last:pb-0"
                >
                    <span class="text-xs text-muted-foreground block">Notes</span>
                    <p class="text-sm">{{ step.notes }}</p>
                </div>
                <div class="flex items-center justify-between last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Completed By</span>
                    <span class="font-medium text-sm">{{
                        step.completedBy?.name || '-'
                    }}</span>
                </div>
            </CardContent>
        </Card>

        <Card v-if="step.application" class="w-full border border-border rounded-md">
            <CardHeader>
                <CardTitle>Application</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-2 text-sm">
                <button
                    class="text-left"
                    @click="router.visit('/provider-applications/' + step.application.id)"
                >
                    <p class="font-medium">{{
                        step.application.application_number
                    }}</p>
                    <p class="text-muted-foreground capitalize">{{
                        step.application.type.replace('_', ' ')
                    }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ step.application.status }}
                    </p>
                </button>
            </CardContent>
        </Card>
    </div>
</template>
