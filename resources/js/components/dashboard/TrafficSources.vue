<script setup lang="ts">
import { TrendingUp } from '@lucide/vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const sources = [
    { label: 'Direct', value: 38, color: 'bg-chart-1', stroke: 'text-chart-1' },
    { label: 'Organic search', value: 27, color: 'bg-chart-2', stroke: 'text-chart-2' },
    { label: 'Referral', value: 21, color: 'bg-chart-3', stroke: 'text-chart-3' },
    { label: 'Social', value: 14, color: 'bg-chart-4', stroke: 'text-chart-4' },
];

let offset = 0;

const segments = sources.map((source) => {
    const visible = Math.max(source.value - 1.5, 0);
    const segment = {
        ...source,
        dash: `${visible} ${100 - visible}`,
        dashOffset: -offset,
    };

    offset += source.value;

    return segment;
});
</script>

<template>
    <Card class="gap-0 py-0 shadow-none">
        <CardHeader class="border-b px-5 py-5">
            <CardTitle class="text-base">Traffic sources</CardTitle>
            <CardDescription>Where sessions came from this month.</CardDescription>
        </CardHeader>

        <CardContent class="px-5 py-5">
            <div class="relative mx-auto size-44">
                <svg
                    viewBox="0 0 160 160"
                    class="size-full -rotate-90"
                    role="img"
                    aria-labelledby="traffic-chart-title"
                >
                    <title id="traffic-chart-title">
                        Sessions by acquisition channel
                    </title>

                    <circle
                        cx="80"
                        cy="80"
                        r="56"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="14"
                        class="text-muted"
                    />

                    <circle
                        v-for="segment in segments"
                        :key="segment.label"
                        cx="80"
                        cy="80"
                        r="56"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="14"
                        pathLength="100"
                        :stroke-dasharray="segment.dash"
                        :stroke-dashoffset="segment.dashOffset"
                        :class="segment.stroke"
                    />
                </svg>

                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="text-2xl font-semibold tracking-tight tabular-nums">48.2k</span>
                    <span class="text-[11px] uppercase tracking-[0.16em] text-muted-foreground">
                        sessions
                    </span>
                </div>
            </div>

            <ul class="mt-6 space-y-3">
                <li
                    v-for="source in segments"
                    :key="`legend-${source.label}`"
                    class="flex items-center gap-3"
                >
                    <span class="size-2 shrink-0 rounded-full" :class="source.color" aria-hidden="true" />
                    <span class="flex-1 text-sm">{{ source.label }}</span>
                    <span class="text-sm tabular-nums text-muted-foreground">{{ source.value }}%</span>
                </li>
            </ul>
        </CardContent>

        <div
            class="flex items-center gap-2 border-t px-5 py-3 text-xs text-muted-foreground"
        >
            <TrendingUp class="size-3.5 text-chart-1" aria-hidden="true" />
            Organic search is up 6.4% week over week.
        </div>
    </Card>
</template>
