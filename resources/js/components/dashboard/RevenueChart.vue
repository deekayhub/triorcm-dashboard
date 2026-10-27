<script setup lang="ts">
import { computed, ref } from 'vue';
import { ArrowUpRight } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type Point = {
    x: number;
    y: number;
    value: number;
};

const labels = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
];

const current = [18.4, 22.1, 20.8, 26.4, 24.9, 31.2, 29.7, 35.6, 33.2, 39.8, 37.4, 48.3];
const previous = [14.2, 16.8, 18.5, 19.1, 21.7, 23.4, 22.9, 26.8, 28.4, 30.2, 31.6, 35.1];

const maxValue = 60;
const width = 720;
const height = 300;
const padding = { top: 20, right: 20, bottom: 36, left: 44 };
const plotWidth = width - padding.left - padding.right;
const plotHeight = height - padding.top - padding.bottom;
const baseline = padding.top + plotHeight;

const toX = (index: number) => padding.left + (index * plotWidth) / (labels.length - 1);
const toY = (value: number) => padding.top + plotHeight - (value / maxValue) * plotHeight;

const toPoints = (values: number[]): Point[] =>
    values.map((value, index) => ({ x: toX(index), y: toY(value), value }));

const currentPoints = toPoints(current);
const previousPoints = toPoints(previous);

const smoothPath = (points: Point[]) => {
    let path = `M ${points[0].x.toFixed(2)} ${points[0].y.toFixed(2)}`;

    for (let index = 0; index < points.length - 1; index++) {
        const first = points[index - 1] ?? points[index];
        const second = points[index];
        const third = points[index + 1];
        const fourth = points[index + 2] ?? third;

        const controlOneX = second.x + (third.x - first.x) / 6;
        const controlOneY = second.y + (third.y - first.y) / 6;
        const controlTwoX = third.x - (fourth.x - second.x) / 6;
        const controlTwoY = third.y - (fourth.y - second.y) / 6;

        path += ` C ${controlOneX.toFixed(2)} ${controlOneY.toFixed(2)} ${controlTwoX.toFixed(2)} ${controlTwoY.toFixed(2)} ${third.x.toFixed(2)} ${third.y.toFixed(2)}`;
    }

    return path;
};

const linePath = smoothPath(currentPoints);
const previousPath = smoothPath(previousPoints);
const firstPoint = currentPoints[0];
const lastPoint = currentPoints[currentPoints.length - 1];
const areaPath = `${linePath} L ${lastPoint.x.toFixed(2)} ${baseline.toFixed(2)} L ${firstPoint.x.toFixed(2)} ${baseline.toFixed(2)} Z`;

const gridLines = [0, 15, 30, 45, 60].map((value) => ({
    y: toY(value),
    label: value === 0 ? '$0' : `$${value}k`,
}));

const active = ref<number | null>(null);
const activePoint = computed(() => (active.value === null ? null : currentPoints[active.value] ?? null));
const activeLabel = computed(() =>
    active.value === null ? labels[labels.length - 1] : labels[active.value],
);
const activeValue = computed(() => activePoint.value?.value ?? lastPoint.value);
</script>

<template>
    <Card class="gap-0 py-0 shadow-none">
        <CardHeader
            class="flex flex-col gap-3 border-b px-5 py-5 sm:flex-row sm:items-start sm:justify-between"
        >
            <div class="space-y-1">
                <CardTitle class="text-base">Revenue</CardTitle>
                <CardDescription>Gross revenue over the last 12 months.</CardDescription>
            </div>
            <div class="flex items-center gap-4 text-xs text-muted-foreground">
                <span class="flex items-center gap-1.5">
                    <span class="size-2 rounded-full bg-chart-1" aria-hidden="true" />
                    This year
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-4 border-t border-dashed border-muted-foreground" aria-hidden="true" />
                    Last year
                </span>
            </div>
        </CardHeader>

        <CardContent class="px-5 pb-5 pt-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.16em] text-muted-foreground">
                        Year to date
                    </p>
                    <p class="mt-1 text-2xl font-semibold tracking-tight tabular-nums">$391.6k</p>
                </div>
                <Badge variant="secondary" class="gap-1 font-medium tabular-nums">
                    <ArrowUpRight class="size-3.5 text-chart-1" aria-hidden="true" />
                    18.2% vs last year
                </Badge>
            </div>

            <div class="mt-2 flex h-5 items-center gap-1.5 text-xs text-muted-foreground">
                <span class="size-1.5 rounded-full bg-chart-1" aria-hidden="true" />
                <span v-if="activePoint" class="tabular-nums">
                    {{ activeLabel }} · ${{ activeValue.toFixed(1) }}k
                </span>
                <span v-else>Hover the line to inspect a month</span>
            </div>

            <div class="-mx-2 mt-1 overflow-x-auto px-2">
                <svg
                    :viewBox="`0 0 ${width} ${height}`"
                    class="h-auto w-full min-w-[560px] text-chart-1"
                    role="img"
                    aria-labelledby="revenue-chart-title"
                >
                    <title id="revenue-chart-title">
                        Monthly revenue compared with the previous year
                    </title>

                    <defs>
                        <linearGradient id="revenue-fill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="currentColor" stop-opacity="0.22" />
                            <stop offset="100%" stop-color="currentColor" stop-opacity="0" />
                        </linearGradient>
                    </defs>

                    <g class="text-border">
                        <line
                            v-for="line in gridLines"
                            :key="`grid-${line.label}`"
                            :x1="padding.left"
                            :x2="width - padding.right"
                            :y1="line.y"
                            :y2="line.y"
                            stroke="currentColor"
                            stroke-width="1"
                        />
                    </g>

                    <g class="text-muted-foreground">
                        <text
                            v-for="line in gridLines"
                            :key="`axis-${line.label}`"
                            :x="padding.left - 10"
                            :y="line.y + 3.5"
                            text-anchor="end"
                            fill="currentColor"
                            class="text-[11px] tabular-nums"
                        >
                            {{ line.label }}
                        </text>
                        <text
                            v-for="(label, index) in labels"
                            :key="`month-${label}`"
                            :x="toX(index)"
                            :y="height - 12"
                            text-anchor="middle"
                            fill="currentColor"
                            class="text-[11px]"
                        >
                            {{ label }}
                        </text>
                    </g>

                    <path :d="areaPath" fill="url(#revenue-fill)" />
                    <path
                        :d="previousPath"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-dasharray="4 5"
                        class="text-muted-foreground/60"
                    />
                    <path
                        :d="linePath"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.25"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />

                    <g
                        :transform="`translate(${lastPoint.x}, ${lastPoint.y})`"
                        class="text-chart-1"
                    >
                        <circle r="4.5" fill="currentColor" />
                        <circle r="2" fill="currentColor" class="text-background" />
                    </g>

                    <text
                        :x="lastPoint.x - 10"
                        :y="lastPoint.y - 12"
                        text-anchor="end"
                        fill="currentColor"
                        class="text-[11px] font-semibold text-foreground"
                    >
                        Dec · $48.3k
                    </text>

                    <g v-if="activePoint" :transform="`translate(${activePoint.x}, ${activePoint.y})`">
                        <line
                            :y1="0"
                            :y2="baseline - activePoint.y"
                            stroke="currentColor"
                            stroke-width="1"
                            stroke-dasharray="3 4"
                            class="text-chart-1/40"
                        />
                        <circle r="5" fill="currentColor" class="text-chart-1" />
                        <circle r="2" fill="currentColor" class="text-background" />
                    </g>

                    <g v-for="(point, index) in currentPoints" :key="`hit-${index}`">
                        <circle
                            :cx="point.x"
                            :cy="point.y"
                            r="12"
                            fill="transparent"
                            class="cursor-crosshair"
                            @pointerenter="active = index"
                            @pointerleave="active = null"
                        >
                            <title>{{ labels[index] }}: ${{ point.value.toFixed(1) }}k</title>
                        </circle>
                    </g>
                </svg>
            </div>
        </CardContent>
    </Card>
</template>
