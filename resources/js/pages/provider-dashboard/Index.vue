<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
defineProps<{ kpis: { providers: number; applications: number; licenses: number; expiring_licenses: number; insurance: number } }>();
</script>
<template>
    <Head title="Provider Dashboard" />
    <div class="p-6 grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="rounded border p-4"><p class="text-xs text-muted-foreground">Providers</p><p class="text-2xl font-semibold">{{ kpis.providers }}</p></div>
        <div class="rounded border p-4"><p class="text-xs text-muted-foreground">Applications</p><p class="text-2xl font-semibold">{{ kpis.applications }}</p></div>
        <div class="rounded border p-4"><p class="text-xs text-muted-foreground">Licenses</p><p class="text-2xl font-semibold">{{ kpis.licenses }}</p></div>
        <div class="rounded border p-4"><p class="text-xs text-muted-foreground">Expiring (30d)</p><p class="text-2xl font-semibold">{{ kpis.expiring_licenses }}</p></div>
        <div class="rounded border p-4"><p class="text-xs text-muted-foreground">Insurance</p><p class="text-2xl font-semibold">{{ kpis.insurance }}</p></div>
    </div>
</template>
