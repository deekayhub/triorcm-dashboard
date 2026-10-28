<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

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
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-sm">
                        <thead>
                            <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                                <th class="px-5 py-2.5 font-medium">Date & Time</th>
                                <th class="px-5 py-2.5 font-medium">User</th>
                                <th class="px-5 py-2.5 font-medium">Event</th>
                                <th class="px-5 py-2.5 font-medium">Description</th>
                                <th class="px-5 py-2.5 font-medium">Subject</th>
                                <th class="px-5 py-2.5 font-medium">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="activity in props.activities.data"
                                :key="activity.id"
                                class="hover:bg-muted/50"
                            >
                                <td class="whitespace-nowrap px-5 py-2.5">
                                    {{ formatDate(activity.created_at) }}
                                </td>
                                <td class="px-5 py-2.5">
                                    <div v-if="activity.causer">
                                        <p class="font-medium">
                                            {{ activity.causer.name }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ activity.causer.email }}
                                        </p>
                                    </div>
                                    <span v-else class="text-muted-foreground">System</span>
                                </td>
                                <td class="px-5 py-2.5">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                        :class="{
                                            'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300':
                                                getEventBadge(activity.event) === 'success',
                                            'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300':
                                                getEventBadge(activity.event) === 'warning',
                                            'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300':
                                                getEventBadge(activity.event) === 'destructive',
                                            'bg-slate-100 text-slate-700 dark:bg-slate-500/10 dark:text-slate-300':
                                                getEventBadge(activity.event) === 'secondary',
                                            'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300':
                                                getEventBadge(activity.event) === 'default',
                                        }"
                                    >
                                        {{ activity.event || '-' }}
                                    </span>
                                </td>
                                <td class="px-5 py-2.5">{{ activity.description }}</td>
                                <td class="px-5 py-2.5">
                                    <span v-if="activity.subject_type">
                                        {{ activity.subject_type.replace('App\\Models\\', '') }}
                                        <span v-if="activity.subject_id" class="text-muted-foreground"
                                            >#{{ activity.subject_id }}</span
                                        >
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>
                                <td class="px-5 py-2.5">
                                    <pre
                                        v-if="activity.properties"
                                        class="max-w-xs overflow-auto text-xs text-muted-foreground"
                                        >{{ JSON.stringify(activity.properties, null, 2) }}</pre
                                    >
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>
                            </tr>
                            <tr v-if="props.activities.data.length === 0">
                                <td colspan="6" class="px-5 py-6 text-center text-muted-foreground">
                                    No activity logs found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
