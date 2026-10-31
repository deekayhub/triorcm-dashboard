<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle, Plus, FileText, LayoutDashboard } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { index as providerApplicationsIndex } from '@/routes/provider-applications';
import { useToast } from '@/composables/useToast';
import { ref } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Applications',
                href: providerApplicationsIndex(),
            },
        ],
    },
});

const { toast } = useToast();

const isDeleteOpen = ref(false);
let currentApplicationId = null;

function confirmDelete(id: number) {
    currentApplicationId = id;
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
    <Head title="Applications" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6 bg-white rounded-lg shadow-sm border border-border">

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase">
                        Credentialing
                    </p>
                </div>
                <h1 class="text-2xl font-semibold tracking-tight">Applications</h1>
                <p class="text-sm text-muted-foreground">
                    Track provider credentialing applications
                </p>
            </div>
            <Button @click="router.visit('/provider-applications/create')">
                <Plus class="size-4" />
                New Application
            </Button>
        </div>

        <Card class="gap-0 rounded-lg shadow-sm border border-border">
            <CardContent class="px-5 pb-4">
                <p class="text-sm text-muted-foreground">Total: {{ providerApplicationsIndex.data.length }}</p>
                <table class="w-full text-sm mt-3">
                    <thead>
                        <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                            <th class="px-5 py-2.5 font-medium">Application</th>
                            <th class="px-5 py-2.5 font-medium">Provider</th>
                            <th class="px-5 py-2.5 font-medium">Type</th>
                            <th class="px-5 py-2.5 font-medium">Status</th>
                            <th class="px-5 py-2.5 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="app in providerApplicationsIndex.data" :key="app.id" class="border-b last:border-0 hover:bg-neutral-50 cursor-pointer">
                            <td class="px-5 py-3">
                                <button class="text-left" @click="router.visit(`/provider-applications/${app.id}`)">
                                    <p class="font-medium">Application #{{ app.id }}</p>
                                </button>
                            </td>
                            <td class="px-5 py-3 text-neutral-500">
                                {{ app.provider ? `${app.provider.first_name} ${app.provider.last_name}` : '-' }}
                            </td>
                            <td class="px-5 py-3 text-neutral-500 capitalize">{{ app.type }}</td>
                            <td class="px-5 py-3">
                                <Badge variant="outline" class="capitalize">{{ app.status }}</Badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Button variant="ghost" size="sm" @click="router.visit(`/provider-applications/${app.id}/edit`)">Edit</Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="confirmDelete(app.id)">Delete</Button>
                            </td>
                        </tr>
                        <tr v-if="providerApplicationsIndex.data.length === 0">
                            <td colspan="5" class="px-5 py-6 text-center text-muted-foreground">No applications found</td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>
    </div>
</template>