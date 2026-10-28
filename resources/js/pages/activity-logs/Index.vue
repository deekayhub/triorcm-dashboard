<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import $ from 'jquery';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { index as activityLogsIndex } from '@/routes/activity-logs';

DataTable.use(DataTablesCore);
$.fn.dataTable = DataTablesCore;

interface Activity {
    id: number;
    log_name: string | null;
    description: string;
    event: string | null;
    subject_type: string | null;
    subject_id: number | null;
    causer: { id: number; name: string; email: string } | null;
    properties: Record<string, any> | null;
    created_at: string | null;
}

interface Props {
    activities: {
        data: Activity[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Activity Logs',
                href: '#',
            },
        ],
    },
});

const formatDate = (date: string | null) => {
    if (!date) {
        return '-';
    }

    return new Date(date).toLocaleString();
};

const getEventClass = (event: string | null) => {
    if (!event) {
        return 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300';
    }

    const map: Record<string, string> = {
        login: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        logout: 'bg-slate-100 text-slate-700 dark:bg-slate-500/10 dark:text-slate-300',
        created: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
        updated: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        deleted: 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',
    };

    return map[event] ?? 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300';
};

const parseUserAgent = (ua: string | null) => {
    if (!ua) {
        return '-';
    }
    if (ua.includes('Edg/')) {
        return 'Edge';
    }
    if (ua.includes('Chrome/') && !ua.includes('Edg/')) {
        return 'Chrome';
    }
    if (ua.includes('Firefox/')) {
        return 'Firefox';
    }
    if (ua.includes('Safari/') && !ua.includes('Chrome/')) {
        return 'Safari';
    }
    if (ua.includes('Opera/') || ua.includes('OPR/')) {
        return 'Opera';
    }
    return 'Unknown';
};

const dtColumns = [
    { data: 'created_at', name: 'created_at', orderable: true },
    { data: 'causer', name: 'causer', orderable: false },
    { data: 'event', name: 'event', orderable: false },
    { data: 'description', name: 'description', orderable: false },
    { data: 'subject', name: 'subject', orderable: false },
    { data: 'ip_address', name: 'ip_address', orderable: false },
    { data: 'browser', name: 'browser', orderable: false },
];

const dtOptions = computed(() => ({
    serverSide: true,
    processing: true,
    ajax: {
        url: activityLogsIndex().url,
        type: 'GET',
    },
    columns: dtColumns,
    order: [[0, 'desc']],
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
        searchPlaceholder: 'Search logs...',
        processing: 'Loading...',
        emptyTable: 'No activity logs found',
        info: 'Showing _START_ to _END_ of _TOTAL_ logs',
        infoEmpty: 'Showing 0 to 0 of 0 logs',
        paginate: {
            previous: '‹',
            next: '›',
            first: '«',
            last: '»',
        },
    },
    pagingType: 'simple_numbers',
}));
</script>

<template>
    <Head title="Activity Logs" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight">Activity Logs</h1>
            <p class="text-sm text-muted-foreground">
                Track login/logout and user management activities
            </p>
        </div>

        <Card class="gap-0 py-0 shadow-none">
            <CardHeader class="flex flex-col gap-4 border-b px-5 py-5 md:flex-row md:items-start md:justify-between">
                <div class="space-y-1">
                    <CardTitle class="text-base">Recent Activity</CardTitle>
                    <CardDescription>All recorded system activities</CardDescription>
                </div>
            </CardHeader>
            <CardContent class="px-0">
                <div class="px-5 pb-4">
                    <DataTable
                        :columns="dtColumns"
                        :options="dtOptions"
                        class="w-full text-sm"
                    >
                        <thead>
                            <tr
                                class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase"
                            >
                                <th class="px-0 py-2.5 font-medium">Date & Time</th>
                                <th class="px-0 py-2.5 font-medium">User</th>
                                <th class="px-0 py-2.5 font-medium">Event</th>
                                <th class="px-0 py-2.5 font-medium">Description</th>
                                <th class="px-0 py-2.5 font-medium">Subject</th>
                                <th class="px-0 py-2.5 font-medium">IP Address</th>
                                <th class="px-0 py-2.5 font-medium">Browser</th>
                            </tr>
                        </thead>
                        <template #column-created_at="props">
                            <span class="whitespace-nowrap">
                                {{ formatDate(props.rowData.created_at) }}
                            </span>
                        </template>
                        <template #column-causer="props">
                            <div v-if="props.rowData.causer">
                                <p class="font-medium">
                                    {{ props.rowData.causer.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ props.rowData.causer.email }}
                                </p>
                            </div>
                            <span v-else class="text-muted-foreground">System</span>
                        </template>
                        <template #column-event="props">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                :class="getEventClass(props.rowData.event)"
                            >
                                {{ props.rowData.event || '-' }}
                            </span>
                        </template>
                        <template #column-description="props">
                            <span>{{ props.rowData.description }}</span>
                        </template>
                        <template #column-subject="props">
                            <span v-if="props.rowData.subject">
                                {{ props.rowData.subject.type }}
                                <span
                                    v-if="props.rowData.subject.id"
                                    class="text-muted-foreground"
                                    >#{{ props.rowData.subject.id }}</span
                                >
                            </span>
                            <span v-else class="text-muted-foreground">-</span>
                        </template>
                        <template #column-ip_address="props">
                            <span class="text-muted-foreground">{{
                                props.rowData.ip_address || '-'
                            }}</span>
                        </template>
                        <template #column-browser="props">
                            <span class="text-muted-foreground">{{
                                parseUserAgent(props.rowData.browser)
                            }}</span>
                        </template>
                    </DataTable>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
