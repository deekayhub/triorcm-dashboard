<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const products = [
    {
        name: 'Aero Runner',
        category: 'Footwear',
        units: '1,284',
        revenue: '$38.5k',
        share: 100,
    },
    {
        name: 'Studio Tote',
        category: 'Accessories',
        units: '964',
        revenue: '$31.2k',
        share: 81,
    },
    {
        name: 'Drift Hoodie',
        category: 'Apparel',
        units: '812',
        revenue: '$24.8k',
        share: 64,
    },
    {
        name: 'Court Low',
        category: 'Footwear',
        units: '689',
        revenue: '$18.4k',
        share: 48,
    },
];
</script>

<template>
    <Card class="gap-0 py-0 shadow-none">
        <CardHeader
            class="flex flex-row items-start justify-between gap-4 border-b px-5 py-5"
        >
            <div class="space-y-1">
                <CardTitle class="text-base">Top products</CardTitle>
                <CardDescription>Best sellers by revenue this month.</CardDescription>
            </div>
            <Badge variant="outline" class="font-normal">Top 4</Badge>
        </CardHeader>

        <CardContent class="px-5 py-5">
            <ul class="space-y-4">
                <li
                    v-for="(product, index) in products"
                    :key="product.name"
                    class="space-y-2"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <span
                                class="mt-0.5 w-4 shrink-0 text-xs font-medium tabular-nums text-muted-foreground"
                            >
                                {{ String(index + 1).padStart(2, '0') }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ product.name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ product.category }} · {{ product.units }} units
                                </p>
                            </div>
                        </div>
                        <span class="shrink-0 text-sm font-medium tabular-nums">
                            {{ product.revenue }}
                        </span>
                    </div>
                    <div class="ml-7 h-1 overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-chart-1"
                            :style="{ width: `${product.share}%` }"
                        />
                    </div>
                </li>
            </ul>
        </CardContent>

        <div class="border-t px-5 py-3 text-xs text-muted-foreground">
            Top 4 products account for 64% of catalog revenue.
        </div>
    </Card>
</template>
