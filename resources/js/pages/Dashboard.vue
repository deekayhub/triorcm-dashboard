<script setup lang="ts">
import { computed, ref } from 'vue';
import type { Component } from 'vue';
import { Head } from '@inertiajs/vue3';
import { DollarSign, Download, Percent, ShoppingCart, Users } from '@lucide/vue';
import KpiCard from '@/components/dashboard/KpiCard.vue';
import RecentOrders from '@/components/dashboard/RecentOrders.vue';
import RevenueChart from '@/components/dashboard/RevenueChart.vue';
import TopProducts from '@/components/dashboard/TopProducts.vue';
import TrafficSources from '@/components/dashboard/TrafficSources.vue';
import { Button } from '@/components/ui/button';
import { usePermissions } from '@/composables/usePermissions';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

type Kpi = {
    label: string;
    value: string;
    change: string;
    detail: string;
    icon: Component;
    spark: number[];
    trend: 'up' | 'down';
};

const kpis: Kpi[] = [
    {
        label: 'Total revenue',
        value: '$48,290',
        change: '+12.4%',
        detail: 'vs last month',
        icon: DollarSign,
        spark: [12, 14, 13, 17, 16, 19, 18, 23],
        trend: 'up',
    },
    {
        label: 'Active users',
        value: '12,847',
        change: '+8.2%',
        detail: 'vs last month',
        icon: Users,
        spark: [8, 10, 9, 12, 11, 14, 15, 17],
        trend: 'up',
    },
    {
        label: 'Conversion rate',
        value: '3.62%',
        change: '-0.4%',
        detail: 'vs last month',
        icon: Percent,
        spark: [22, 20, 21, 18, 19, 17, 16, 15],
        trend: 'down',
    },
    {
        label: 'Avg. order value',
        value: '$86.40',
        change: '+2.1%',
        detail: 'vs last month',
        icon: ShoppingCart,
        spark: [20, 21, 20, 22, 21, 23, 22, 24],
        trend: 'up',
    },
];

const ranges = [
    { value: '7d', label: '7 days' },
    { value: '30d', label: '30 days' },
    { value: '90d', label: '90 days' },
];

const selectedRange = ref('30d');
const rangeLabel = computed(
    () => ranges.find((range) => range.value === selectedRange.value)?.label ?? '30 days',
);

const { can } = usePermissions();
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-muted-foreground">
                        Dashboard
                    </p>
                    <span class="inline-flex items-center gap-1.5 text-[11px] text-muted-foreground">
                        <span class="size-1.5 rounded-full bg-chart-1" aria-hidden="true" />
                        Live
                    </span>
                </div>
                <h1 class="text-2xl font-semibold tracking-tight">Performance overview</h1>
                <p class="text-sm text-muted-foreground">
                    Revenue, acquisition, and orders for the last {{ rangeLabel }}.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div
                    class="inline-flex rounded-md border bg-muted/40 p-0.5"
                    role="group"
                    aria-label="Date range"
                >
                    <button
                        v-for="range in ranges"
                        :key="range.value"
                        type="button"
                        :aria-pressed="selectedRange === range.value"
                        :class="
                            cn(
                                'rounded px-2.5 py-1 text-xs font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50 active:scale-95',
                                selectedRange === range.value
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground',
                            )
                        "
                        @click="selectedRange = range.value"
                    >
                        {{ range.label }}
                    </button>
                </div>

                <Button
                    v-if="can('dashboard.export')"
                    variant="outline"
                    size="sm"
                    class="gap-1.5"
                >
                    <Download class="size-3.5" aria-hidden="true" />
                    Export
                </Button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <KpiCard
                v-for="kpi in kpis"
                :key="kpi.label"
                class="min-w-0"
                :label="kpi.label"
                :value="kpi.value"
                :change="kpi.change"
                :detail="kpi.detail"
                :icon="kpi.icon"
                :spark="kpi.spark"
                :trend="kpi.trend"
            />
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <RevenueChart class="min-w-0 lg:col-span-2" />
            <TrafficSources class="min-w-0" />
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <RecentOrders class="min-w-0 lg:col-span-2" />
            <TopProducts class="min-w-0" />
        </div>
    </div>
</template>
