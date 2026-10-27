<script setup lang="ts">
import { ArrowRight } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type OrderStatus = 'Paid' | 'Pending' | 'Refunded';
type StatusVariant = 'default' | 'secondary' | 'outline';

const orders: {
    id: string;
    customer: string;
    email: string;
    date: string;
    status: OrderStatus;
    amount: string;
}[] = [
    {
        id: '#TR-4821',
        customer: 'Maya Chen',
        email: 'maya@northline.co',
        date: 'Oct 27, 09:42',
        status: 'Paid',
        amount: '$248.00',
    },
    {
        id: '#TR-4820',
        customer: 'Jonas Weber',
        email: 'jonas@fieldwork.de',
        date: 'Oct 27, 08:15',
        status: 'Paid',
        amount: '$112.50',
    },
    {
        id: '#TR-4819',
        customer: 'Priya Nair',
        email: 'priya@studiofmt.in',
        date: 'Oct 26, 22:08',
        status: 'Pending',
        amount: '$76.00',
    },
    {
        id: '#TR-4818',
        customer: 'Theo Martin',
        email: 'theo@atelier.fr',
        date: 'Oct 26, 19:31',
        status: 'Refunded',
        amount: '$189.00',
    },
    {
        id: '#TR-4817',
        customer: 'Sofia Alvarez',
        email: 'sofia@casa.mx',
        date: 'Oct 26, 18:04',
        status: 'Paid',
        amount: '$324.00',
    },
];

const statusVariant = (status: OrderStatus): StatusVariant => {
    if (status === 'Paid') {
        return 'default';
    }

    return status === 'Pending' ? 'secondary' : 'outline';
};

const statusClass = (status: OrderStatus) =>
    status === 'Refunded'
        ? 'gap-1.5 border-destructive/40 text-foreground'
        : 'gap-1.5';
</script>

<template>
    <Card class="gap-0 py-0 shadow-none">
        <CardHeader
            class="flex flex-row items-start justify-between gap-4 border-b px-5 py-5"
        >
            <div class="space-y-1">
                <CardTitle class="text-base">Recent orders</CardTitle>
                <CardDescription
                    >Latest transactions across all channels.</CardDescription
                >
            </div>
            <Button variant="outline" size="sm" class="gap-1.5">
                View all
                <ArrowRight class="size-3.5" aria-hidden="true" />
            </Button>
        </CardHeader>

        <CardContent class="px-0">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[520px] text-sm">
                    <thead>
                        <tr
                            class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase"
                        >
                            <th class="px-5 py-2.5 font-medium">Order</th>
                            <th
                                class="hidden px-5 py-2.5 font-medium md:table-cell"
                            >
                                Customer
                            </th>
                            <th class="px-5 py-2.5 font-medium">Status</th>
                            <th class="px-5 py-2.5 text-right font-medium">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="order in orders"
                            :key="order.id"
                            class="transition-colors hover:bg-muted/40"
                        >
                            <td class="px-5 py-3">
                                <p class="font-medium tabular-nums">
                                    {{ order.id }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ order.date }}
                                </p>
                            </td>
                            <td class="hidden px-5 py-3 md:table-cell">
                                <p class="font-medium">{{ order.customer }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ order.email }}
                                </p>
                            </td>
                            <td class="px-5 py-3">
                                <Badge
                                    :variant="statusVariant(order.status)"
                                    :class="statusClass(order.status)"
                                >
                                    <span
                                        v-if="order.status === 'Refunded'"
                                        class="size-1.5 rounded-full bg-destructive"
                                        aria-hidden="true"
                                    />
                                    {{ order.status }}
                                </Badge>
                            </td>
                            <td
                                class="px-5 py-3 text-right font-medium tabular-nums"
                            >
                                {{ order.amount }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardContent>

        <div
            class="flex items-center justify-between border-t px-5 py-2.5 text-xs text-muted-foreground"
        >
            <span>Showing 5 of 1,284 orders</span>
            <span class="tabular-nums">Updated 2 min ago</span>
        </div>
    </Card>
</template>
