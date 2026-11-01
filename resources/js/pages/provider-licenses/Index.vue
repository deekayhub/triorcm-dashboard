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
import { index as providerLicensesIndex } from '@/routes/provider-licenses';
import { useToast } from '@/composables/useToast';
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

defineProps<{
    licenses: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Licenses',
                href: providerLicensesIndex(),
            },
        ],
    },
});

const { toast } = useToast();

const isDeleteOpen = ref(false);
let currentLicenseId = null;

function confirmDelete(id: number) {
    currentLicenseId = id;
    isDeleteOpen.value = true;
}

async function deleteLicense(id: number) {
    isDeleteOpen.value = false;
    toast({
        title: 'License deleted',
        description: 'The license has been successfully deleted.',
        variant: 'success',
    });
}
</script>

<template>
    <Head title="Licenses" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase">
                        Credentialing
                    </p>
                </div>
                <h1 class="text-xl font-semibold tracking-tight">Licenses</h1>
                <p class="text-xs text-muted-foreground">
                    Manage provider licenses
                </p>
            </div>
            <Button
                @click="router.visit('/provider-licenses/create')"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm"
            >
                <Plus class="size-3.5" />
                New License
            </Button>
        </div>

        <Card class="w-full border border-border rounded-md">
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                <th class="px-3 py-2">License Number</th>
                                <th class="px-3 py-2">Provider</th>
                                <th class="px-3 py-2">Type</th>
                                <th class="px-3 py-2">State</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="lic in licenses.data"
                                :key="lic.id"
                                class="hover:bg-neutral-50 cursor-pointer"
                            >
                                <td class="px-3 py-2">
                                    <button
                                        class="text-left text-primary font-medium hover:underline"
                                        @click="router.visit('/provider-licenses/' + lic.id)"
                                    >
                                        {{ lic.license_number }}
                                    </button>
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{
                                        lic.provider
                                            ? lic.provider.first_name +
                                                  ' ' +
                                                  lic.provider.last_name
                                            : '-'
                                    }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground capitalize">
                                    {{ lic.license_type }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ lic.state || '-' }}
                                </td>
                                <td class="px-3 py-2">
                                    <Badge variant="outline" class="text-sm capitalize">
                                        {{ lic.status }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="router.visit('/provider-licenses/' + lic.id + '/edit')"
                                        class="text-primary"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive"
                                        @click="confirmDelete(lic.id)"
                                    >
                                        Delete
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="licenses.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-3 py-4 text-center text-muted-foreground"
                                >
                                    No licenses found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <section
            v-if="licenses.meta && licenses.data.length > 0"
            class="mt-2 flex justify-end"
        >
            <nav class="flex space-x-1">
                <span class="px-2.5 py-1 text-xs text-muted-foreground">
                    Page {{
                        licenses.meta.current_page
                    }} of {{ licenses.meta.last_page }}
                </span>
                <Button
                    v-if="licenses.meta.current_page > 1"
                    variant="outline"
                    size="sm"
                    @click="router.visit(providerLicensesIndex({ page: licenses.meta.current_page - 1 }))"
                >
                    Previous
                </Button>
                <Button
                    v-if="licenses.meta.current_page < licenses.meta.last_page"
                    variant="outline"
                    size="sm"
                    @click="router.visit(providerLicensesIndex({ page: licenses.meta.current_page + 1 }))"
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
                    <DialogTitle>Delete License</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this license?
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
                        @click="deleteLicense(currentLicenseId)"
                    >
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
