<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Bell, CheckCheck } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { index as notificationsIndex } from '@/routes/notifications';

interface NotificationItem {
    id: string;
    type: string;
    data: {
        title?: string;
        message?: string;
        action_url?: string;
    };
    read_at: string | null;
    created_at: string | null;
}

interface PaginatedNotifications {
    data: NotificationItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = defineProps<{
    notifications: PaginatedNotifications;
    unreadCount: number;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Notifications',
                href: notificationsIndex(),
            },
        ],
    },
});

const getNotificationStyle = (notification: NotificationItem) => {
    const title = (notification.data?.title || '').toLowerCase();
    if (title.includes('add') || title.includes('creat')) {
        return 'bg-emerald-500/10 text-emerald-600';
    }
    if (title.includes('updat')) {
        return 'bg-amber-500/10 text-amber-600';
    }
    if (title.includes('remov') || title.includes('delet')) {
        return 'bg-red-500/10 text-red-600';
    }
    return 'bg-muted text-muted-foreground';
};

const formatDate = (date: string | null) => {
    if (!date) {
        return '-';
    }

    return new Date(date).toLocaleString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const markAsRead = (id: string) => {
    router.post(`/notifications/${id}/read`, {}, { preserveScroll: true, preserveState: true });
};

const markAllAsRead = () => {
    router.post('/notifications/read-all', {}, { preserveScroll: true, preserveState: true });
};

const goTo = (notification: NotificationItem) => {
    if (!notification.read_at) {
        markAsRead(notification.id);
    }
    if (notification.data?.action_url) {
        router.visit(notification.data.action_url);
    }
};
</script>

<template>
    <Head title="Notifications" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Notifications</h1>
                <p class="text-sm text-muted-foreground">
                    Stay updated with team activities
                </p>
            </div>
            <Button v-if="unreadCount > 0" variant="outline" @click="markAllAsRead">
                <CheckCheck class="size-4" />
                Mark all as read
            </Button>
        </div>

        <Card class="gap-0 py-0 shadow-none">
            <CardHeader class="border-b px-5 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle class="text-base">All notifications</CardTitle>
                        <CardDescription>
                            {{ unreadCount }} unread notification{{ unreadCount === 1 ? '' : 's' }}
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div v-if="notifications.data.length === 0" class="flex flex-col items-center justify-center gap-2 py-12 text-center">
                    <Bell class="size-8 text-muted-foreground/50" />
                    <p class="text-sm font-medium">No notifications</p>
                    <p class="text-xs text-muted-foreground">You're all caught up</p>
                </div>
                <div v-else class="divide-y">
                    <button
                        v-for="notification in notifications.data"
                        :key="notification.id"
                        class="flex w-full items-start gap-3 px-5 py-4 text-left transition-colors hover:bg-muted/40"
                        :class="{ 'bg-muted/20': !notification.read_at }"
                        @click="goTo(notification)"
                    >
                        <div
                            class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg"
                            :class="getNotificationStyle(notification)"
                        >
                            <Bell class="size-4" />
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-medium">
                                    {{ notification.data?.title || 'Notification' }}
                                </p>
                                <div class="flex items-center gap-2">
                                    <Badge v-if="!notification.read_at" variant="secondary" class="text-[10px]">New</Badge>
                                    <span class="text-xs text-muted-foreground tabular-nums">
                                        {{ formatDate(notification.created_at) }}
                                    </span>
                                </div>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                {{ notification.data?.message }}
                            </p>
                        </div>
                    </button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
