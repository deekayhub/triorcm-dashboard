<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { index as providersIndex } from '@/routes/providers';
import { useToast } from '@/composables/useToast';
import { ref } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Providers',
                href: providersIndex(),
            },
        ],
    },
});

const props = defineProps<{
    stats: {
        total: number;
        active: number;
        pending: number;
        inactive: number;
    };
    providers: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
}>();

const { toast } = useToast();

const isDeleteOpen = ref(false);
let currentProviderId = null;

function confirmDelete(id: number) {
    currentProviderId = id;
    isDeleteOpen.value = true;
}

async function deleteProvider(id: number) {
    isDeleteOpen.value = false;
    toast({
        title: 'Provider deleted',
        description: 'The provider has been successfully deleted.',
        variant: 'success',
    });
}
</script>

<template>
    <Head title="Providers" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight">Providers</h1>
                <p class="text-xs text-muted-foreground">
                    Manage provider profiles and credentialing applications
                </p>
            </div>
            <Button
                @click="router.visit('/providers/create')"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm"
            >
                <Plus class="size-3.5" />
                Add Provider
            </Button>
        </div>

        <div class="grid w-full gap-3 md:grid-cols-4">
            <Card class="p-3 border border-border rounded-md">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Providers</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-semibold">{{ stats.total }}</div>
                </CardContent>
            </Card>
            <Card class="p-3 border border-border rounded-md">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Active</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-semibold text-emerald-600">{{
                        stats.active
                    }}</div>
                </CardContent>
            </Card>
            <Card class="p-3 border border-border rounded-md">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Pending</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-semibold text-amber-600">{{
                        stats.pending
                    }}</div>
                </CardContent>
            </Card>
            <Card class="p-3 border border-border rounded-md">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Inactive</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-semibold text-rose-600">{{
                        stats.inactive
                    }}</div>
                </CardContent>
            </Card>
        </div>

        <Card class="w-full border border-border rounded-md">
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                <th class="px-3 py-2">Provider</th>
                                <th class="px-3 py-2">NPI</th>
                                <th class="px-3 py-2">Email</th>
                                <th class="px-3 py-2">Specialty</th>
                                <th class="px-3 py-2">Team</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="provider in props.providers.data" :key="provider.id" class="hover:bg-neutral-50 cursor-pointer">
                                <td class="px-3 py-2">
                                    <button
                                        @click="router.visit('/providers/' + provider.id)"
                                        class="text-primary font-medium hover:underline"
                                    >
                                        {{ provider.full_name }}
                                    </button>
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ provider.npi || '-' }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ provider.email }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ provider.specialty || '-' }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ provider.team?.name || '-' }}
                                </td>
                                <td class="px-3 py-2">
                                    <Badge variant="outline" class="text-sm capitalize">
                                        {{ provider.status }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="router.visit('/providers/' + provider.id)"
                                        class="text-primary"
                                    >
                                        View
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="router.visit('/providers/' + provider.id + '/edit')"
                                        class="text-primary"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive"
                                        @click="confirmDelete(provider.id)"
                                    >
                                        Delete
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="props.providers.data.length === 0">
                                <td colspan="7" class="px-3 py-4 text-center text-muted-foreground">
                                    No providers found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <section
            v-if="props.providers.meta && props.providers.data.length > 0"
            class="mt-2 flex justify-end"
        >
            <nav class="flex space-x-1">
                <span class="px-2.5 py-1 text-xs text-muted-foreground">
                    Page {{ props.providers.meta.current_page }} of {{
                        props.providers.meta.last_page
                    }}
                </span>
                <Button
                    v-if="props.providers.meta.current_page > 1"
                    variant="outline"
                    size="sm"
                    @click="router.visit(providersIndex({ page: props.providers.meta.current_page - 1 }))"
                >
                    Previous
                </Button>
                <Button
                    v-if="props.providers.meta.current_page < props.providers.meta.last_page"
                    variant="outline"
                    size="sm"
                    @click="router.visit(providersIndex({ page: props.providers.meta.current_page + 1 }))"
                >
                    Next
                </Button>
            </nav>
        </section>

        <Dialog
            v-model:open="isDeleteOpen"
            class="modal"
        >
            <DialogTrigger asChild>
                <button />
            </DialogTrigger>
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Delete Provider</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this provider?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="isDeleteOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="deleteProvider(currentProviderId)"
                    >
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
