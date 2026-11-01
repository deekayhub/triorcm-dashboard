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
import { index as providerVerificationsIndex } from '@/routes/provider-verifications';
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
    verifications: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Verifications',
                href: providerVerificationsIndex(),
            },
        ],
    },
});

const { toast } = useToast();

const isDeleteOpen = ref(false);
let currentVerificationId = null;

function confirmDelete(id: number) {
    currentVerificationId = id;
    isDeleteOpen.value = true;
}

async function deleteVerification(id: number) {
    isDeleteOpen.value = false;
    toast({
        title: 'Verification deleted',
        description: 'The verification has been successfully deleted.',
        variant: 'success',
    });
}
</script>

<template>
    <Head title="Verifications" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase">
                        Credentialing
                    </p>
                </div>
                <h1 class="text-xl font-semibold tracking-tight">Verifications</h1>
                <p class="text-xs text-muted-foreground">
                    Track provider credentialing verifications
                </p>
            </div>
            <Button
                @click="router.visit('/provider-verifications/create')"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm"
            >
                <Plus class="size-3.5" />
                New Verification
            </Button>
        </div>

        <Card class="w-full border border-border rounded-md">
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                <th class="px-3 py-2">Verification</th>
                                <th class="px-3 py-2">Provider</th>
                                <th class="px-3 py-2">Type</th>
                                <th class="px-3 py-2">Application</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="ver in verifications.data"
                                :key="ver.id"
                                class="hover:bg-neutral-50 cursor-pointer"
                            >
                                <td class="px-3 py-2">
                                    <button
                                        class="text-left text-primary font-medium hover:underline"
                                        @click="router.visit('/provider-verifications/' + ver.id)"
                                    >
                                        {{
                                            ver.verification_source ||
                                            'Primary Source'
                                        }}
                                    </button>
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{
                                        ver.provider
                                            ? ver.provider.first_name +
                                                  ' ' +
                                                  ver.provider.last_name
                                            : '-'
                                    }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground capitalize">
                                    {{ ver.type }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{
                                        ver.application
                                            ? ver.application.application_number
                                            : '-'
                                    }}
                                </td>
                                <td class="px-3 py-2">
                                    <Badge variant="outline" class="text-sm capitalize">
                                        {{ ver.status }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="router.visit('/provider-verifications/' + ver.id + '/edit')"
                                        class="text-primary"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive"
                                        @click="confirmDelete(ver.id)"
                                    >
                                        Delete
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="verifications.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-3 py-4 text-center text-muted-foreground"
                                >
                                    No verifications found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <section
            v-if="verifications.meta && verifications.data.length > 0"
            class="mt-2 flex justify-end"
        >
            <nav class="flex space-x-1">
                <span class="px-2.5 py-1 text-xs text-muted-foreground">
                    Page {{
                        verifications.meta.current_page
                    }} of {{ verifications.meta.last_page }}
                </span>
                <Button
                    v-if="verifications.meta.current_page > 1"
                    variant="outline"
                    size="sm"
                    @click="router.visit(providerVerificationsIndex({ page: verifications.meta.current_page - 1 }))"
                >
                    Previous
                </Button>
                <Button
                    v-if="
                        verifications.meta.current_page <
                        verifications.meta.last_page
                    "
                    variant="outline"
                    size="sm"
                    @click="router.visit(providerVerificationsIndex({ page: verifications.meta.current_page + 1 }))"
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
                    <DialogTitle>Delete Verification</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this verification?
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
                        @click="deleteVerification(currentVerificationId)"
                    >
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
