<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { FileText, Plus, UserCheck, UserX, Users } from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import $ from 'jquery';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { index as providersIndex } from '@/routes/providers';

DataTable.use(DataTablesCore);
$.fn.dataTable = DataTablesCore;

const props = defineProps<{
    stats: {
        total: number;
        active: number;
        pending: number;
        inactive: number;
    };
}>();

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

const dtColumns = [
    { data: 'provider', name: 'provider', orderable: true },
    { data: 'specialty', name: 'specialty', orderable: true },
    { data: 'status', name: 'status', orderable: true },
    { data: 'applications_count', name: 'applications_count', orderable: false },
    { data: 'actions', name: 'actions', orderable: false },
];

const dtOptions = computed(() => ({
    serverSide: true,
    processing: true,
    ajax: {
        url: providersIndex().url,
        type: 'GET',
    },
    columns: dtColumns,
    order: [[0, 'asc']],
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    layout: {
        topStart: 'pageLength',
        topEnd: 'search',
        bottomStart: 'info',
        bottomEnd: 'paging',
    },
    language: {
        search: 'Search:',
        searchPlaceholder: 'Search providers...',
        processing: 'Loading...',
        emptyTable: 'No providers found',
    },
    pagingType: 'simple_numbers',
}));
</script>

<template>
    <Head title="Providers" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase">
                        Credentialing
                    </p>
                </div>
                <h1 class="text-2xl font-semibold tracking-tight">Providers</h1>
                <p class="text-sm text-muted-foreground">
                    Manage provider profiles and credentialing applications
                </p>
            </div>
            <Button @click="router.visit('/providers/create')">
                <Plus class="size-4" />
                Add Provider
            </Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-4">
            <Card class="gap-0 py-4 shadow-none">
                <CardContent class="flex items-center gap-3 px-5">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                        <Users class="size-4" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Total</p>
                        <p class="text-xl font-semibold tabular-nums">{{ stats.total }}</p>
                    </div>
                </CardContent>
            </Card>
            <Card class="gap-0 py-4 shadow-none">
                <CardContent class="flex items-center gap-3 px-5">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600">
                        <UserCheck class="size-4" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Active</p>
                        <p class="text-xl font-semibold tabular-nums">{{ stats.active }}</p>
                    </div>
                </CardContent>
            </Card>
            <Card class="gap-0 py-4 shadow-none">
                <CardContent class="flex items-center gap-3 px-5">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600">
                        <FileText class="size-4" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Pending</p>
                        <p class="text-xl font-semibold tabular-nums">{{ stats.pending }}</p>
                    </div>
                </CardContent>
            </Card>
            <Card class="gap-0 py-4 shadow-none">
                <CardContent class="flex items-center gap-3 px-5">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-red-500/10 text-red-600">
                        <UserX class="size-4" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Inactive</p>
                        <p class="text-xl font-semibold tabular-nums">{{ stats.inactive }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card class="gap-0 py-0 shadow-none">
            <CardContent class="px-0">
                <div class="px-5 pb-4">
                    <DataTable :columns="dtColumns" :options="dtOptions" class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                                <th class="px-0 py-2.5 font-medium">Provider</th>
                                <th class="px-0 py-2.5 font-medium">Specialty</th>
                                <th class="px-0 py-2.5 font-medium">Status</th>
                                <th class="px-0 py-2.5 font-medium">Applications</th>
                                <th class="px-0 py-2.5 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <template #column-provider="props">
                            <button class="flex flex-col text-left py-2" @click="router.visit(`/providers/${props.rowData.provider.id}`)">
                                <span class="font-medium">{{ props.rowData.provider.name }}</span>
                                <span class="text-xs text-muted-foreground">{{ props.rowData.provider.email }}</span>
                                <span v-if="props.rowData.provider.npi" class="text-xs text-muted-foreground">NPI: {{ props.rowData.provider.npi }}</span>
                            </button>
                        </template>
                        <template #column-specialty="props">
                            <span class="text-muted-foreground">{{ props.rowData.specialty || '-' }}</span>
                        </template>
                        <template #column-status="props">
                            <Badge :variant="props.rowData.status === 'active' ? 'secondary' : props.rowData.status === 'pending' ? 'outline' : 'destructive'" class="gap-1.5">
                                <span class="size-1.5 rounded-full" :class="props.rowData.status === 'active' ? 'bg-emerald-500' : props.rowData.status === 'pending' ? 'bg-amber-500' : 'bg-red-500'"></span>
                                {{ props.rowData.status }}
                            </Badge>
                        </template>
                        <template #column-applications_count="props">
                            <span class="tabular-nums">{{ props.rowData.applications_count }}</span>
                        </template>
                        <template #column-actions="props">
                            <div class="flex justify-end">
                                <Button variant="ghost" size="sm" @click="router.visit(`/providers/${props.rowData.id}/edit`)">Edit</Button>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
