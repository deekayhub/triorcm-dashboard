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
import { index as credentialingStepsIndex } from '@/routes/credentialing-steps';
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
    steps: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Credentialing Steps',
                href: credentialingStepsIndex(),
            },
        ],
    },
});

const { toast } = useToast();

const isDeleteOpen = ref(false);
let currentStepId = null;

function confirmDelete(id: number) {
    currentStepId = id;
    isDeleteOpen.value = true;
}

async function deleteStep(id: number) {
    isDeleteOpen.value = false;
    toast({
        title: 'Credentialing step deleted',
        description: 'The credentialing step has been successfully deleted.',
        variant: 'success',
    });
}
</script>

<template>
    <Head title="Credentialing Steps" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase">
                        Credentialing
                    </p>
                </div>
                <h1 class="text-xl font-semibold tracking-tight">Credentialing Steps</h1>
                <p class="text-xs text-muted-foreground">
                    Track provider application credentialing steps
                </p>
            </div>
            <Button
                @click="router.visit('/credentialing-steps/create')"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm"
            >
                <Plus class="size-3.5" />
                New Step
            </Button>
        </div>

        <Card class="w-full border border-border rounded-md">
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                <th class="px-3 py-2">Step Name</th>
                                <th class="px-3 py-2">Application</th>
                                <th class="px-3 py-2">Order</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2">Assigned To</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="step in steps.data"
                                :key="step.id"
                                class="hover:bg-neutral-50 cursor-pointer"
                            >
                                <td class="px-3 py-2">
                                    <button
                                        class="text-left text-primary font-medium hover:underline"
                                        @click="router.visit('/credentialing-steps/' + step.id)"
                                    >
                                        {{ step.name }}
                                    </button>
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{
                                        step.application
                                            ? step.application.application_number
                                            : '-'
                                    }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ step.order }}
                                </td>
                                <td class="px-3 py-2">
                                    <Badge variant="outline" class="text-sm capitalize">
                                        {{ step.status }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{ step.assignedTo?.name || '-' }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="router.visit('/credentialing-steps/' + step.id + '/edit')"
                                        class="text-primary"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive"
                                        @click="confirmDelete(step.id)"
                                    >
                                        Delete
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="steps.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-3 py-4 text-center text-muted-foreground"
                                >
                                    No credentialing steps found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <section
            v-if="steps.meta && steps.data.length > 0"
            class="mt-2 flex justify-end"
        >
            <nav class="flex space-x-1">
                <span class="px-2.5 py-1 text-xs text-muted-foreground">
                    Page {{
                        steps.meta.current_page
                    }} of {{ steps.meta.last_page }}
                </span>
                <Button
                    v-if="steps.meta.current_page > 1"
                    variant="outline"
                    size="sm"
                    @click="router.visit(credentialingStepsIndex({ page: steps.meta.current_page - 1 }))"
                >
                    Previous
                </Button>
                <Button
                    v-if="steps.meta.current_page < steps.meta.last_page"
                    variant="outline"
                    size="sm"
                    @click="router.visit(credentialingStepsIndex({ page: steps.meta.current_page + 1 }))"
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
                    <DialogTitle>Delete Credentialing Step</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this credentialing step?
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
                        @click="deleteStep(currentStepId)"
                    >
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
