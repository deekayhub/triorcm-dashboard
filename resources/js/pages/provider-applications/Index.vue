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
import { index as providerApplicationsIndex } from '@/routes/provider-applications';
import { useToast } from '@/composables/useToast';
import { ref } from 'vue';

defineProps<{
    applications: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Applications',
                href: providerApplicationsIndex(),
            },
        ],
    },
});

const { toast } = useToast();

const isDeleteOpen = ref(false);
let currentApplicationId = null;

function confirmDelete(id: number) {
    currentApplicationId = id;
    isDeleteOpen.value = true;
}
</script>

<template>
    <Head title="Applications" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-clip p-4 md:p-6 bg-neutral-50">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-medium tracking-[0.18em] text-muted-foreground uppercase">
                        Credentialing
                    </p>
                </div>
                <h1 class="text-xl font-semibold tracking-tight">Applications</h1>
                <p class="text-xs text-muted-foreground">
                    Track provider credentialing applications
                </p>
            </div>
            <Button
                @click="router.visit('/provider-applications/create')"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm"
            >
                <Plus class="size-3.5" />
                New Application
            </Button>
        </div>

        <Card class="w-full border border-border rounded-md">
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                <th class="px-3 py-2">Application</th>
                                <th class="px-3 py-2">Provider</th>
                                <th class="px-3 py-2">Type</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="app in applications.data"
                                :key="app.id"
                                class="hover:bg-neutral-50 cursor-pointer"
                            >
                                <td class="px-3 py-2">
                                    <button
                                        class="text-left text-primary font-medium hover:underline"
                                        @click="router.visit('/provider-applications/' + app.id)"
                                    >
                                        Application #{{ app.id }}
                                    </button>
                                </td>
                                <td class="px-3 py-2 text-muted-foreground">
                                    {{
                                        app.provider
                                            ? app.provider.first_name + ' ' + app.provider.last_name
                                            : '-'
                                    }}
                                </td>
                                <td class="px-3 py-2 text-muted-foreground capitalize">
                                    {{ app.type }}
                                </td>
                                <td class="px-3 py-2">
                                    <Badge variant="outline" class="text-sm capitalize">
                                        {{ app.status }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="router.visit('/provider-applications/' + app.id + '/edit')"
                                        class="text-primary"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive"
                                        @click="confirmDelete(app.id)"
                                    >
                                        Delete
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="applications.data.length === 0">
                                <td
                                    colspan="5"
                                    class="px-3 py-4 text-center text-muted-foreground"
                                >
                                    No applications found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <section
            v-if="
                applications.meta && applications.data.length > 0
            "
            class="mt-2 flex justify-end"
        >
            <nav class="flex space-x-1">
                <span class="px-2.5 py-1 text-xs text-muted-foreground">
                    Page {{
                        applications.meta.current_page
                    }} of {{ applications.meta.last_page }}
                </span>
                <Button
                    v-if="applications.meta.current_page > 1"
                    variant="outline"
                    size="sm"
                    @click="router.visit(providerApplicationsIndex({ page: applications.meta.current_page - 1 }))"
                >
                    Previous
                </Button>
                <Button
                    v-if="
                        applications.meta.current_page <
                        applications.meta.last_page
                    "
                    variant="outline"
                    size="sm"
                    @click="router.visit(providerApplicationsIndex({ page: applications.meta.current_page + 1 }))"
                >
                    Next
                </Button>
            </nav>
        </section>
    </div>
</template>
