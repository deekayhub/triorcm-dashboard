<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus } from '@lucide/vue';
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
    employments: Array<any>;
}>();

const { toast } = useToast();

const isSaving = ref(false);
const isDeleting = ref(false);
const openForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    employer: '',
    position: '',
    employment_type: 'full_time',
    address: '',
    start_date: '',
    end_date: '',
    is_current: false,
    gap_explanation: '',
    verification_status: 'unverified',
});

const employmentTypes = [
    { value: 'full_time', label: 'Full Time' },
    { value: 'part_time', label: 'Part Time' },
    { value: 'locum', label: 'Locum' },
    { value: 'contract', label: 'Contract' },
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
        employer: '',
        position: '',
        employment_type: 'full_time',
        address: '',
        start_date: '',
        end_date: '',
        is_current: false,
        gap_explanation: '',
        verification_status: 'unverified',
    }).reset();
    openForm.value = true;
}

function openEditForm(employment: any) {
    editingId.value = employment.id;
    form.defaults({
        employer: employment.employer,
        position: employment.position,
        employment_type: employment.employment_type,
        address: employment.address ?? '',
        start_date: employment.start_date ?? '',
        end_date: employment.end_date ?? '',
        is_current: employment.is_current,
        gap_explanation: employment.gap_explanation ?? '',
        verification_status: employment.verification_status,
    }).reset();
    openForm.value = true;
}

async function save() {
    isSaving.value = true;

    const url = editingId.value
        ? `/providers/${props.provider.id}/employments/${editingId.value}`
        : `/providers/${props.provider.id}/employments`;

    const options = {
        onSuccess: () => {
            toast({
                title: 'Saved',
                description: editingId.value
                    ? 'Employment record updated successfully.'
                    : 'Employment record added successfully.',
                variant: 'success',
            });
            openForm.value = false;
            editingId.value = null;
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to save employment record. Please try again.',
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
            description: 'Failed to save employment record. Please try again.',
            variant: 'error',
        });
    }
}

async function deleteEmployment(id: number) {
    if (!confirm('Are you sure you want to remove this employment record?')) {
        return;
    }

    isDeleting.value = true;

    try {
        await router.delete(`/providers/${props.provider.id}/employments/${id}`, {
            onSuccess: () => {
                toast({
                    title: 'Removed',
                    description: 'Employment record has been removed.',
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
            description: 'Failed to remove employment record.',
            variant: 'error',
        });
    }
}

function getTypeLabel(type: string): string {
    const found = employmentTypes.find(t => t.value === type);
    return found ? found.label : type;
}

type BadgeVariant = 'default' | 'outline' | 'destructive' | 'secondary';

function getStatusBadge(status: string): {
    variant: BadgeVariant;
    class: string;
} {
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
</script>

<template>
    <Head title="Employment Information" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Employment Information</h1>
                <p class="text-sm text-muted-foreground">
                    Track provider employment history
                </p>
            </div>
            <Button
                variant="outline"
                size="sm"
                @click="openCreateForm"
                :disabled="isSaving"
            >
                <Plus class="size-4 mr-2" />
                Add Employment Record
            </Button>
        </div>

        <!-- Employment Table -->
        <Card class="shadow-none">
            <CardHeader>
                <CardTitle>Employment Records</CardTitle>
                <CardDescription>
                    {{ employments.length }} record{{ employments.length !== 1 ? 's' : '' }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="employments.length === 0" class="p-8 text-center text-neutral-500">
                    <p>No employment records found.</p>
                    <Button variant="outline" @click="openCreateForm">Add Employment Record</Button>
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                            <th class="px-5 py-3 font-medium">Employer</th>
                            <th class="px-5 py-3 font-medium">Position</th>
                            <th class="px-5 py-3 font-medium">Type</th>
                            <th class="px-5 py-3 font-medium">Dates</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="emp in employments"
                            :key="emp.id"
                            class="border-b last:border-0 hover:bg-neutral-50"
                        >
                            <td class="px-5 py-3 font-medium">
                                {{ emp.employer }}
                                <Badge v-if="emp.is_current" variant="destructive" class="ml-2 text-xs">
                                    Current
                                </Badge>
                            </td>
                            <td class="px-5 py-3 text-neutral-500">{{ emp.position }}</td>
                            <td class="px-5 py-3 text-neutral-500 capitalize">{{ getTypeLabel(emp.employment_type) }}</td>
                            <td class="px-5 py-3 text-neutral-500">
                                <span v-if="emp.start_date">{{ new Date(emp.start_date).toLocaleDateString() }}</span>
                                <span v-if="emp.start_date && emp.end_date"> to </span>
                                <span v-if="emp.end_date">{{ new Date(emp.end_date).toLocaleDateString() }}</span>
                                <span v-if="!emp.start_date && !emp.end_date">-</span>
                            </td>
                            <td class="px-5 py-3">
                                <Badge
                                    :variant="getStatusBadge(emp.verification_status).variant"
                                    :class="getStatusBadge(emp.verification_status).class"
                                >
                                    {{ emp.verification_status }}
                                </Badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="openEditForm(emp)"
                                    class="text-primary"
                                >
                                    Edit
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive"
                                    @click="deleteEmployment(emp.id)"
                                    :disabled="isDeleting"
                                >
                                    Remove
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>

        <!-- Employment Form -->
        <div v-if="openForm" class="max-w-2xl">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>
                        {{ editingId ? 'Edit Employment Record' : 'Add Employment Record' }}
                    </CardTitle>
                    <CardDescription>
                        Enter provider employment details
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="save" class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="employer">Employer *</Label>
                                <Input
                                    id="employer"
                                    name="employer"
                                    v-model="form.employer"
                                    required
                                />
                                <InputError :message="form.errors.employer" />
                            </div>
                            <div>
                                <Label for="position">Position *</Label>
                                <Input
                                    id="position"
                                    name="position"
                                    v-model="form.position"
                                    required
                                />
                                <InputError :message="form.errors.position" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="employment_type">Employment Type *</Label>
                                <Select
                                    id="employment_type"
                                    :model-value="form.employment_type"
                                    @update:model-value="form.employment_type = String($event ?? '')"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="type in employmentTypes"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.employment_type" />
                            </div>
                            <div>
                                <Label for="is_current">Current Employment</Label>
                                <div class="flex items-center gap-2">
                                    <input
                                        id="is_current"
                                        type="checkbox"
                                        v-model="form.is_current"
                                        class="rounded border-neutral-300 text-primary focus:ring-primary"
                                    />
                                    <label for="is_current" class="text-sm text-neutral-600">
                                        This is current employment
                                    </label>
                                </div>
                                <p class="text-xs text-neutral-400 mt-1">
                                    Only one employment can be current at a time.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="address">Address</Label>
                                <Input id="address" name="address" v-model="form.address" />
                                <InputError :message="form.errors.address" />
                            </div>
                            <div>
                                <Label for="start_date">Start Date *</Label>
                                <Input
                                    id="start_date"
                                    name="start_date"
                                    type="date"
                                    v-model="form.start_date"
                                    required
                                />
                                <InputError :message="form.errors.start_date" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="end_date">End Date</Label>
                                <Input
                                    id="end_date"
                                    name="end_date"
                                    type="date"
                                    v-model="form.end_date"
                                />
                                <InputError :message="form.errors.end_date" />
                            </div>
                            <div>
                                <Label for="gap_explanation">Gap Explanation</Label>
                                <Textarea
                                    id="gap_explanation"
                                    name="gap_explanation"
                                    v-model="form.gap_explanation"
                                    rows="3"
                                />
                                <InputError :message="form.errors.gap_explanation" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
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
                                {{ editingId ? 'Update Employment Record' : 'Add Employment Record' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
