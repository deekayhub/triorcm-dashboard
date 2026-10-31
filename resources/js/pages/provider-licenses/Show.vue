<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Edit } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">{{ license.license_number }}</h1>
                <p class="text-sm text-muted-foreground capitalize">{{ license.license_type }} license</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="router.visit(providerLicensesIndex())">
                    <ArrowLeft class="size-4" />
                    Back
                </Button>
                <Button @click="router.visit(`/provider-licenses/${license.id}/edit`)">
                    <Edit class="size-4" />
                    Edit
                </Button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>License Details</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Provider</span>
                        <button class="text-primary capitalize">{{ license.provider?.first_name }} {{ license.provider?.last_name }}</button>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Type</span>
                        <span class="capitalize">{{ license.license_type }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">State</span>
                        <span>{{ license.state }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Issue Date</span>
                        <span>{{ license.issue_date ? new Date(license.issue_date).toLocaleDateString() : '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Expiration Date</span>
                        <span>{{ license.expiration_date ? new Date(license.expiration_date).toLocaleDateString() : '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Status</span>
                        <Badge variant="outline" class="capitalize">{{ license.status }}</Badge>
                    </div>
                    <div v-if="license.issuing_board" class="flex justify-between">
                        <span class="text-muted-foreground">Issuing Board</span>
                        <span>{{ license.issuing_board }}</span>
                    </div>
                </CardContent>
            </Card>

            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>Provider</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-2 text-sm">
                    <button class="text-left" @click="router.visit(`/providers/${license.provider.id}`)">
                        <p class="font-medium">{{ license.provider?.first_name }} {{ license.provider?.last_name }}</p>
                        <p class="text-muted-foreground">{{ license.provider?.email }}</p>
                        <p v-if="license.provider?.npi" class="text-muted-foreground">NPI: {{ license.provider?.npi }}</p>
                    </button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>