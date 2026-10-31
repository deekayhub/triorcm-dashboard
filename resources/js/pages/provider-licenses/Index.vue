<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ClipboardList, Plus } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { index as providerLicensesIndex } from '@/routes/provider-licenses';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Licenses',
                href: providerLicensesIndex(),
            },
        ],
    },
});

const props = defineProps<{
    licenses: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
}>();
</script>

<template>
    <Head title="Licenses" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase">
                        Credentialing
                    </p>
                </div>
                <h1 class="text-2xl font-semibold tracking-tight">Licenses</h1>
                <p class="text-sm text-muted-foreground">
                    Manage provider licenses
                </p>
            </div>
            <Button @click="router.visit('/provider-licenses/create')">
                <Plus class="size-4" />
                New License
            </Button>
        </div>

        <Card class="gap-0 py-0 shadow-none">
            <CardContent class="px-5 pb-4">
                <p class="text-sm text-muted-foreground">Total: {{ licenses.data.length }}</p>
                <table class="w-full text-sm mt-3">
                    <thead>
                        <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                            <th class="px-5 py-2.5 font-medium">License Number</th>
                            <th class="px-5 py-2.5 font-medium">Provider</th>
                            <th class="px-5 py-2.5 font-medium">Type</th>
                            <th class="px-5 py-2.5 font-medium">State</th>
                            <th class="px-5 py-2.5 font-medium">Status</th>
                            <th class="px-5 py-2.5 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="lic in licenses.data" :key="lic.id" class="border-b last:border-0">
                            <td class="px-5 py-3">
                                <button class="text-left" @click="router.visit(`/provider-licenses/${lic.id}`)">
                                    {{ lic.license_number }}
                                </button>
                            </td>
                            <td class="px-5 py-3 text-muted-foreground">
                                {{ lic.provider ? `${lic.provider.first_name} ${lic.provider.last_name}` : '-' }}
                            </td>
                            <td class="px-5 py-3 text-muted-foreground capitalize">{{ lic.license_type }}</td>
                            <td class="px-5 py-3">{{ lic.state || '-' }}</td>
                            <td class="px-5 py-3">
                                <Badge variant="outline" class="capitalize">{{ lic.status }}</Badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Button variant="ghost" size="sm" @click="router.visit(`/provider-licenses/${lic.id}/edit`)">Edit</Button>
                            </td>
                        </tr>
                        <tr v-if="licenses.data.length === 0">
                            <td colspan="6" class="px-5 py-6 text-center text-muted-foreground">No licenses found</td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>
    </div>
</template>