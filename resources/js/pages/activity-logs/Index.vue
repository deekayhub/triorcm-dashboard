<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';

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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activity Logs',
        href: '#',
    },
];

const formatDate = (date: string | null) => {
    if (!date) {
        return '-';
    }

    return new Date(date).toLocaleString();
};

const getEventBadge = (event: string | null) => {
    if (!event) {
        return 'default';
    }

    const map: Record<string, string> = {
        login: 'success',
        logout: 'secondary',
        created: 'success',
        updated: 'warning',
        deleted: 'destructive',
    };

    return map[event] ?? 'default';
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Activity Logs" />

        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        Activity Logs
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Track login/logout and user management activities
                    </p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Recent Activity</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date & Time</TableHead>
                                <TableHead>User</TableHead>
                                <TableHead>Event</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead>Subject</TableHead>
                                <TableHead>Details</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="activity in props.activities.data"
                                :key="activity.id"
                            >
                                <TableCell class="whitespace-nowrap">
                                    {{ formatDate(activity.created_at) }}
                                </TableCell>
                                <TableCell>
                                    <div v-if="activity.causer">
                                        <p class="font-medium">
                                            {{ activity.causer.name }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ activity.causer.email }}
                                        </p>
                                    </div>
                                    <span v-else class="text-muted-foreground"
                                        >System</span
                                    >
                                </TableCell>
                                <TableCell>
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400':
                                                getEventBadge(activity.event) ===
                                                'success',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400':
                                                getEventBadge(activity.event) ===
                                                'warning',
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400':
                                                getEventBadge(activity.event) ===
                                                'destructive',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300':
                                                getEventBadge(activity.event) ===
                                                'secondary',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400':
                                                getEventBadge(activity.event) ===
                                                'default',
                                        }"
                                    >
                                        {{ activity.event || '-' }}
                                    </span>
                                </TableCell>
                                <TableCell>{{ activity.description }}</TableCell>
                                <TableCell>
                                    <span v-if="activity.subject_type">
                                        {{ activity.subject_type }}
                                        <span
                                            v-if="activity.subject_id"
                                            class="text-muted-foreground"
                                            >#{{ activity.subject_id }}</span
                                        >
                                    </span>
                                    <span v-else class="text-muted-foreground"
                                        >-</span
                                    >
                                </TableCell>
                                <TableCell>
                                    <pre
                                        v-if="activity.properties"
                                        class="max-w-xs overflow-auto text-xs text-muted-foreground"
                                        >{{ JSON.stringify(activity.properties, null, 2) }}</pre
                                    >
                                    <span v-else class="text-muted-foreground"
                                        >-</span
                                    >
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="props.activities.data.length === 0">
                                <TableCell colspan="6" class="text-center">
                                    No activity logs found
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
