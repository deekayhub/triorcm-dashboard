<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { index as providersIndex } from '@/routes/providers';
import { useToast } from '@/composables/useToast';
import { ref, watch } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Providers',
                href: providersIndex(),
            },
        ],
    },
});

const props = defineProps<{
    stats: {
        total: number;
        active: number;
        pending: number;
        inactive: number;
    };
    verifications: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
}>();

const { toast } = useToast();

const isDeleteOpen = ref(false);
let currentProviderId = null;

function confirmDelete(id: number) {
    currentProviderId = id;
    isDeleteOpen.value = true;
}

async function deleteProvider(id: number) {
    // TODO: Implement delete API call
    isDeleteOpen.value = false;
    toast({
        title: 'Provider deleted',
        description: 'The provider has been successfully deleted.',
        variant: 'success',
    });
}
</script>

<template>
    <Head title="Providers" />

    <div class="flex h-full flex-1 flex-col gap-6 bg-neutral-50">

        <!-- Hero Section -->
        <section class="mb-8">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8 md:py-12 bg-white rounded-xl shadow-sm border border-border">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-neutral-900">Providers</h1>
                        <p class="mt-2 text-lg text-neutral-500">Manage provider profiles and credentialing applications</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <Button @click="router.visit('/providers/create')" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            <Plus class="size-4" />
                            Add Provider
                        </Button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats KPI Cards -->
        <section class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
            <Card class="p-4 border border-emerald-100 rounded-lg">
                <div class="text-2xl font-semibold text-emerald-600">{{ stats.total }}</div>
                <p class="text-sm text-emerald-500 mt-1">Total Providers</p>
            </Card>
            <Card class="p-4 border border-emerald-100 rounded-lg">
                <div class="text-2xl font-semibold text-emerald-400">{{ stats.active }}</div>
                <p class="text-sm text-emerald-500 mt-1">Active</p>
            </Card>
            <Card class="p-4 border border-amber-100 rounded-lg">
                <div class="text-2xl font-semibold text-amber-600">{{ stats.pending }}</div>
                <p class="text-sm text-amber-500 mt-1">Pending</p>
            </Card>
            <Card class="p-4 border border-rose-100 rounded-lg">
                <div class="text-2xl font-semibold text-rose-600">{{ stats.inactive }}</div>
                <p class="text-sm text-rose-500 mt-1">Inactive</p>
            </Card>
        </section>

        <!-- Data Table Section -->
        <section>
            <div class="overflow-x-auto rounded-lg shadow-sm bg-white border border-border">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-neutral-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Provider</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Specialty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">NPI</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-border">
                        <tr v-for="provider in props.verifications.data" :key="provider.id" class="hover:bg-neutral-50 cursor-pointer">
                            <td class="px-6 py-4 font-medium">{{ provider.first_name }} {{ provider.last_name }}</td>
                            <td class="px-6 py-4 text-neutral-500">{{ provider.specialty || '-' }}</td>
                            <td class="px-6 py-4">
                                <Badge variant="outline" class="text-sm capitalize">{{ provider.status }}</Badge>
                            </td>
                            <td class="px-6 py-4 text-neutral-500">{{ provider.npi || '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <Button variant="ghost" size="sm" class="text-primary">Edit</Button>
                                <Button variant="ghost" size="sm" class="text-destructive" @click="confirmDelete(provider.id)">Delete</Button>
                            </td>
                        </tr>
                        <tr v-if="props.verifications.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-neutral-500">No providers found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <!-- Delete Confirmation -->
    <dialog :open="isDeleteOpen" class="modal modal-sm">
        <form method="dialog" class="modal-box">
            <h3 class="text-lg font-medium">Delete Provider</h3>
            <p>Are you sure you want to delete this provider?</p>
            <div class="flex justify-end gap-3 pt-4">
                <button class="btn btn-ghost" @click="isDeleteOpen = false">Cancel</button>
                <button class="btn btn-danger" @click="deleteProvider(provider.id)">Delete</button>
            </div>
        </form>
    </dialog>
</template>