<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, CheckCircle2, AlertCircle } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    provider: any;
    identifiers: Array<any>;
}>();

const { toast } = useToast();

const isSaving = ref(false);
const isDeleting = ref(false);
const openForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    identifier_type: '',
    identifier_value: '',
    issuing_state: '',
    issue_date: '',
    expiration_date: '',
    verification_status: 'unverified',
    verification_notes: '',
});

const identifierTypes = [
    { value: 'dea', label: 'DEA Registration' },
    { value: 'taxonomy', label: 'Taxonomy Code' },
    { value: 'clia', label: 'CLIA Certificate' },
    { value: 'cds', label: 'CDS Registration' },
    { value: 'other', label: 'Other' },
];

const verificationStatuses = [
    { value: 'unverified', label: 'Unverified' },
    { value: 'pending', label: 'Pending' },
    { value: 'verified', label: 'Verified' },
    { value: 'failed', label: 'Failed' },
];

function openCreateForm() {
    editingId.value = null;
    form.defaults({
        identifier_type: '',
        identifier_value: '',
        issuing_state: '',
        issue_date: '',
        expiration_date: '',
        verification_status: 'unverified',
        verification_notes: '',
    }).reset();
    openForm.value = true;
}

function openEditForm(identifier: any) {
    editingId.value = identifier.id;
    form.defaults({
        identifier_type: identifier.identifier_type,
        identifier_value: identifier.identifier_value,
        issuing_state: identifier.issuing_state ?? '',
        issue_date: identifier.issue_date ?? '',
        expiration_date: identifier.expiration_date ?? '',
        verification_status: identifier.verification_status,
        verification_notes: identifier.verification_notes ?? '',
    }).reset();
    openForm.value = true;
}

async function save() {
    isSaving.value = true;

    const url = editingId.value
        ? `/providers/${props.provider.id}/identifiers/${editingId.value}`
        : `/providers/${props.provider.id}/identifiers`;

    const options = {
        onSuccess: () => {
            toast({
                title: 'Saved',
                description: editingId.value
                    ? 'Identifier updated successfully.'
                    : 'Identifier added successfully.',
                variant: 'success',
            });
            openForm.value = false;
            editingId.value = null;
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to save identifier. Please try again.',
                variant: 'error',
            });
        },
        onFinish: () => {
            isSaving.value = false;
        },
    };

    try {
        if (editingId.value) {
            form.patch(url, options);
        } else {
            form.post(url, options);
        }
    } catch (error) {
        isSaving.value = false;
        toast({
            title: 'Error',
            description: 'Failed to save identifier. Please try again.',
            variant: 'error',
        });
    }
}

async function deleteIdentifier(id: number) {
    if (!confirm('Are you sure you want to delete this identifier?')) {
        return;
    }

    isDeleting.value = true;

    try {
        await router.delete(`/providers/${props.provider.id}/identifiers/${id}`, {
            onSuccess: () => {
                toast({
                    title: 'Deleted',
                    description: 'Identifier has been deleted.',
                    variant: 'success',
                });
            },
            onFinish: () => {
                isDeleting.value = false;
            },
        });
    } catch (error) {
        isDeleting.value = false;
        toast({
            title: 'Error',
            description: 'Failed to delete identifier.',
            variant: 'error',
        });
    }
}

type BadgeVariant = 'default' | 'outline' | 'destructive' | 'secondary';

function getStatusBadge(status: string): { variant: BadgeVariant; class: string } {
    switch (status) {
        case 'verified':
            return { variant: 'default', class: 'bg-green-100 text-green-700' };
        case 'pending':
            return { variant: 'outline', class: 'bg-amber-100 text-amber-700 border-amber-200' };
        case 'failed':
            return { variant: 'destructive', class: '' };
        default:
            return { variant: 'secondary', class: 'bg-neutral-100 text-neutral-600' };
    }
}

function isExpired(identifier: any): boolean {
    if (!identifier.expiration_date) return false;
    return new Date(identifier.expiration_date) < new Date();
}
</script>

<template>
    <Head title="Professional IDs" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Professional IDs</h1>
                <p class="text-sm text-muted-foreground">
                    Manage provider identification and professional registration details
                </p>
            </div>
            <Button
                variant="outline"
                size="sm"
                @click="openCreateForm"
                :disabled="isSaving"
            >
                <Plus class="size-4 mr-2" />
                Add Identifier
            </Button>
        </div>

        <!-- Identifiers Table -->
        <Card class="shadow-none">
            <CardHeader>
                <CardTitle>Identifiers</CardTitle>
                <CardDescription>
                    {{ identifiers.length }} identifier{{ identifiers.length !== 1 ? 's' : '' }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="identifiers.length === 0" class="p-8 text-center text-neutral-500">
                    <p>No identifiers found.</p>
                    <Button variant="outline" @click="openCreateForm">Add Identifier</Button>
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                            <th class="px-5 py-3 font-medium">Type</th>
                            <th class="px-5 py-3 font-medium">Value</th>
                            <th class="px-5 py-3 font-medium">State</th>
                            <th class="px-5 py-3 font-medium">Expires</th>
                            <th class="px-5 py-3 font-medium">Verification</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="identifier in identifiers"
                            :key="identifier.id"
                            class="border-b last:border-0 hover:bg-neutral-50"
                        >
                            <td class="px-5 py-3 text-neutral-500 capitalize">
                                {{ identifier.identifier_type.replace('_', ' ') }}
                            </td>
                            <td class="px-5 py-3 font-medium">{{ identifier.identifier_value }}</td>
                            <td class="px-5 py-3 text-neutral-500">
                                {{ identifier.issuing_state || '-' }}
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    :class="
                                        isExpired(identifier)
                                            ? 'text-red-600 font-medium'
                                            : 'text-neutral-500'
                                    "
                                >
                                    {{ identifier.expiration_date ? new Date(identifier.expiration_date).toLocaleDateString() : '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <Badge
                                    :variant="getStatusBadge(identifier.verification_status).variant"
                                    :class="getStatusBadge(identifier.verification_status).class"
                                >
                                    {{ identifier.verification_status }}
                                </Badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="openEditForm(identifier)"
                                    class="text-primary"
                                >
                                    Edit
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive"
                                    @click="deleteIdentifier(identifier.id)"
                                    :disabled="isDeleting"
                                >
                                    Delete
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>

        <!-- Identifier Form -->
        <div v-if="openForm" class="max-w-2xl">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>
                        {{ editingId ? 'Edit Identifier' : 'Add Identifier' }}
                    </CardTitle>
                    <CardDescription>
                        Enter professional identification details
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="save" class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="identifier_type">Identifier Type *</Label>
                                <Select
                                    id="identifier_type"
                                    :model-value="form.identifier_type"
                                    @update:model-value="form.identifier_type = String($event ?? '')"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="type in identifierTypes"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.identifier_type" />
                            </div>
                            <div>
                                <Label for="identifier_value">Identifier Value *</Label>
                                <Input
                                    id="identifier_value"
                                    name="identifier_value"
                                    v-model="form.identifier_value"
                                    required
                                />
                                <InputError :message="form.errors.identifier_value" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="issuing_state">Issuing State</Label>
                                <Input
                                    id="issuing_state"
                                    name="issuing_state"
                                    v-model="form.issuing_state"
                                    maxlength="2"
                                />
                                <InputError :message="form.errors.issuing_state" />
                            </div>
                            <div>
                                <Label for="issue_date">Issue Date</Label>
                                <Input id="issue_date" name="issue_date" type="date" v-model="form.issue_date" />
                                <InputError :message="form.errors.issue_date" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="expiration_date">Expiration Date</Label>
                                <Input
                                    id="expiration_date"
                                    name="expiration_date"
                                    type="date"
                                    v-model="form.expiration_date"
                                />
                                <InputError :message="form.errors.expiration_date" />
                            </div>
                            <div>
                                <Label for="verification_status">Verification Status *</Label>
                                <Select
                                    id="verification_status"
                                    :model-value="form.verification_status"
                                    @update:model-value="form.verification_status = String($event ?? '')"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="status in verificationStatuses"
                                            :key="status.value"
                                            :value="status.value"
                                        >
                                            {{ status.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.verification_status" />
                            </div>
                        </div>

                        <div>
                            <Label for="verification_notes">Verification Notes</Label>
                            <Textarea
                                id="verification_notes"
                                name="verification_notes"
                                v-model="form.verification_notes"
                                rows="3"
                            />
                            <InputError :message="form.errors.verification_notes" />
                        </div>

                        <div class="flex items-center justify-between pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                @click="openForm = false"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="isSaving"
                            >
                                {{ editingId ? 'Update Identifier' : 'Add Identifier' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
