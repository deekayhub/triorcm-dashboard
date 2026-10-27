<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';
import { ArrowDownRight, ArrowUpRight } from '@lucide/vue';
import { Card, CardContent } from '@/components/ui/card';

const props = withDefaults(
    defineProps<{
        label: string;
        value: string;
        change: string;
        detail: string;
        icon: Component;
        spark: number[];
        trend?: 'up' | 'down';
    }>(),
    {
        trend: 'up',
    },
);

const sparkPoints = computed(() => {
    const width = 80;
    const height = 32;
    const pad = 3;
    const min = Math.min(...props.spark);
    const max = Math.max(...props.spark);
    const range = max - min || 1;

    return props.spark.map((value, index) => ({
        x: pad + (index * (width - pad * 2)) / (props.spark.length - 1),
        y: height - pad - ((value - min) / range) * (height - pad * 2),
    }));
});

const sparkPath = computed(() =>
    sparkPoints.value
        .map(
            (point, index) =>
                `${index === 0 ? 'M' : 'L'} ${point.x.toFixed(2)} ${point.y.toFixed(2)}`,
        )
        .join(' '),
);

const lastPoint = computed(
    () => sparkPoints.value[sparkPoints.value.length - 1],
);
</script>

<template>
    <Card class="gap-0 py-0 shadow-none">
        <CardContent class="p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p
                        class="text-xs font-medium tracking-[0.16em] text-muted-foreground uppercase"
                    >
                        {{ label }}
                    </p>
                    <p
                        class="mt-3 text-3xl font-semibold tracking-tight tabular-nums"
                    >
                        {{ value }}
                    </p>
                </div>
                <div
                    class="flex size-9 shrink-0 items-center justify-center rounded-md border bg-muted/40 text-muted-foreground"
                >
                    <component :is="icon" class="size-4" aria-hidden="true" />
                </div>
            </div>

            <div class="mt-4 flex items-end justify-between gap-4">
                <div class="flex min-w-0 items-center gap-1.5 text-xs">
                    <span
                        class="inline-flex items-center"
                        :class="
                            trend === 'up' ? 'text-chart-1' : 'text-destructive'
                        "
                        aria-hidden="true"
                    >
                        <component
                            :is="trend === 'up' ? ArrowUpRight : ArrowDownRight"
                            class="size-3.5"
                        />
                    </span>
                    <span class="font-medium tabular-nums">{{ change }}</span>
                    <span class="truncate text-muted-foreground">{{
                        detail
                    }}</span>
                </div>

                <svg
                    viewBox="0 0 80 32"
                    class="h-8 w-20 shrink-0 overflow-visible"
                    :class="
                        trend === 'up' ? 'text-chart-1' : 'text-destructive'
                    "
                    role="img"
                    :aria-label="`${label} trend`"
                >
                    <path
                        :d="sparkPath"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <circle
                        :cx="lastPoint.x"
                        :cy="lastPoint.y"
                        r="2.5"
                        fill="currentColor"
                    />
                </svg>
            </div>
        </CardContent>
    </Card>
</template>
