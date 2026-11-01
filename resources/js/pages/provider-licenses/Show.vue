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
import { index as providerLicensesIndex } from '@/routes/provider-licenses';

const props = defineProps<{
    license: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Licenses', href: providerLicensesIndex() },
            { title: 'License', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="license.license_number" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight">{{
                    license.license_number
                }}</h1>
                <p class="text-xs text-muted-foreground capitalize">{{
                    license.license_type
                }} license</p>
            </div>
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    @click="router.visit(providerLicensesIndex())"
                    class="text-sm"
                >
                    <ArrowLeft class="size-3.5" />
                    Back
                </Button>
                <Button
                    @click="router.visit('/provider-licenses/' + license.id + '/edit')"
                    class="text-sm"
                >
                    <Edit class="size-3.5 mr-1" />
                    Edit
                </Button>
            </div>
        </div>

        <Card class="w-full border border-border rounded-md">
            <CardHeader>
                <CardTitle>License Details</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-3 md:grid-cols-2">
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Provider</span>
                    <span class="font-medium text-sm capitalize">{{
                        license.provider
                            ? license.provider.first_name +
                                  ' ' +
                                  license.provider.last_name
                            : '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Type</span>
                    <span class="capitalize text-xs font-medium">{{
                        license.license_type
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">State</span>
                    <span class="font-medium text-sm">{{
                        license.state || '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Issue Date</span>
                    <span class="font-medium text-sm">{{
                        license.issue_date || '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Expiration Date</span>
                    <span class="font-medium text-sm">{{
                        license.expiration_date || '-'
                    }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0">
                    <span class="text-xs text-muted-foreground">Status</span>
                    <Badge variant="outline" class="text-xs capitalize">
                        {{ license.status }}
                    </Badge>
                </div>
                <div
                    v-if="license.issuing_board"
                    class="flex items-center justify-between last:border-0 last:pb-0"
                >
                    <span class="text-xs text-muted-foreground">Issuing Board</span>
                    <span class="font-medium text-sm">{{
                        license.issuing_board
                    }}</span>
                </div>
            </CardContent>
        </Card>

        <Card v-if="license.provider" class="w-full border border-border rounded-md">
            <CardHeader>
                <CardTitle>Provider</CardTitle>
            </CardHeader>
            <CardContent class="grid gap-2 text-sm">
                <button
                    class="text-left"
                    @click="router.visit('/providers/' + license.provider.id)"
                >
                    <p class="font-medium">{{
                        license.provider.first_name +
                            ' ' +
                            license.provider.last_name
                    }}</p>
                    <p class="text-muted-foreground">{{
                        license.provider.email
                    }}</p>
                    <p
                        v-if="license.provider.npi"
                        class="text-xs text-muted-foreground"
                    >
                        NPI: {{ license.provider.npi }}
                    </p>
                </button>
            </CardContent>
        </Card>
    </div>
</template>
