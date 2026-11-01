<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, BookOpen, CheckCircle2, AlertCircle } from '@lucide/vue';
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
    educations: Array<any>;
}>();

const { toast } = useToast();

const isSaving = ref(false);
const isDeleting = ref(false);
const openForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    institution_name: '',
    degree: '',
    education_type: 'medical_school',
    specialty: '',
    start_date: '',
    completion_date: '',
    country: 'US',
    state: '',
    city: '',
    verification_status: 'unverified',
});

const educationTypes = [
    { value: 'medical_school', label: 'Medical School' },
    { value: 'residency', label: 'Residency' },
    { value: 'fellowship', label: 'Fellowship' },
    { value: 'internship', label: 'Internship' },
    { value: 'other_training', label: 'Other Professional Training' },
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
        institution_name: '',
        degree: '',
        education_type: 'medical_school',
        specialty: '',
        start_date: '',
        completion_date: '',
        country: 'US',
        state: '',
        city: '',
        verification_status: 'unverified',
    }).reset();
    openForm.value = true;
}

function openEditForm(education: any) {
    editingId.value = education.id;
    form.defaults({
        institution_name: education.institution_name,
        degree: education.degree,
        education_type: education.education_type,
        specialty: education.specialty ?? '',
        start_date: education.start_date ?? '',
        completion_date: education.completion_date ?? '',
        country: education.country ?? 'US',
        state: education.state ?? '',
        city: education.city ?? '',
        verification_status: education.verification_status,
    }).reset();
    openForm.value = true;
}

async function save() {
    isSaving.value = true;

    const url = editingId.value
        ? `/providers/${props.provider.id}/educations/${editingId.value}`
        : `/providers/${props.provider.id}/educations`;

    const options = {
        onSuccess: () => {
            toast({
                title: 'Saved',
                description: editingId.value
                    ? 'Education record updated successfully.'
                    : 'Education record added successfully.',
                variant: 'success',
            });
            openForm.value = false;
            editingId.value = null;
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to save education record. Please try again.',
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
            description: 'Failed to save education record. Please try again.',
            variant: 'error',
        });
    }
}

async function deleteEducation(id: number) {
    if (!confirm('Are you sure you want to delete this education record?')) {
        return;
    }

    isDeleting.value = true;

    try {
        await router.delete(`/providers/${props.provider.id}/educations/${id}`, {
            onSuccess: () => {
                toast({
                    title: 'Deleted',
                    description: 'Education record has been deleted.',
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
            description: 'Failed to delete education record.',
            variant: 'error',
        });
    }
}

function getTypeLabel(type: string): string {
    const found = educationTypes.find(t => t.value === type);
    return found ? found.label : type;
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
</script>

<template>
    <Head title="Education & Training" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Education & Training</h1>
                <p class="text-sm text-muted-foreground">
                    Manage provider educational and training background
                </p>
            </div>
            <Button
                variant="outline"
                size="sm"
                @click="openCreateForm"
                :disabled="isSaving"
            >
                <Plus class="size-4 mr-2" />
                Add Education Record
            </Button>
        </div>

        <!-- Education Table -->
        <Card class="shadow-none">
            <CardHeader>
                <CardTitle>Education Records</CardTitle>
                <CardDescription>
                    {{ educations.length }} record{{ educations.length !== 1 ? 's' : '' }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="educations.length === 0" class="p-8 text-center text-neutral-500">
                    <p>No education records found.</p>
                    <Button variant="outline" @click="openCreateForm">Add Education Record</Button>
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                            <th class="px-5 py-3 font-medium">Institution</th>
                            <th class="px-5 py-3 font-medium">Degree</th>
                            <th class="px-5 py-3 font-medium">Type</th>
                            <th class="px-5 py-3 font-medium">Specialty</th>
                            <th class="px-5 py-3 font-medium">Dates</th>
                            <th class="px-5 py-3 font-medium">Verification</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="edu in educations"
                            :key="edu.id"
                            class="border-b last:border-0 hover:bg-neutral-50"
                        >
                            <td class="px-5 py-3 font-medium">{{ edu.institution_name }}</td>
                            <td class="px-5 py-3 text-neutral-500">{{ edu.degree }}</td>
                            <td class="px-5 py-3 text-neutral-500 capitalize">{{ getTypeLabel(edu.education_type) }}</td>
                            <td class="px-5 py-3 text-neutral-500">{{ edu.specialty || '-' }}</td>
                            <td class="px-5 py-3 text-neutral-500">
                                <span v-if="edu.start_date">{{ new Date(edu.start_date).toLocaleDateString() }}</span>
                                <span v-if="edu.start_date && edu.completion_date"> to </span>
                                <span v-if="edu.completion_date">{{ new Date(edu.completion_date).toLocaleDateString() }}</span>
                                <span v-if="!edu.start_date && !edu.completion_date">-</span>
                            </td>
                            <td class="px-5 py-3">
                                <Badge
                                    :variant="getStatusBadge(edu.verification_status).variant"
                                    :class="getStatusBadge(edu.verification_status).class"
                                >
                                    {{ edu.verification_status }}
                                </Badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="openEditForm(edu)"
                                    class="text-primary"
                                >
                                    Edit
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive"
                                    @click="deleteEducation(edu.id)"
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

        <!-- Education Form -->
        <div v-if="openForm" class="max-w-2xl">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>
                        {{ editingId ? 'Edit Education Record' : 'Add Education Record' }}
                    </CardTitle>
                    <CardDescription>
                        Enter provider education and training details
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="save" class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="institution_name">Institution Name *</Label>
                                <Input
                                    id="institution_name"
                                    name="institution_name"
                                    v-model="form.institution_name"
                                    required
                                />
                                <InputError :message="form.errors.institution_name" />
                            </div>
                            <div>
                                <Label for="degree">Degree *</Label>
                                <Input
                                    id="degree"
                                    name="degree"
                                    v-model="form.degree"
                                    required
                                />
                                <InputError :message="form.errors.degree" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="education_type">Education Type *</Label>
                                <Select
                                    id="education_type"
                                    :model-value="form.education_type"
                                    @update:model-value="form.education_type = String($event ?? '')"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="type in educationTypes"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.education_type" />
                            </div>
                            <div>
                                <Label for="specialty">Specialty</Label>
                                <Input id="specialty" name="specialty" v-model="form.specialty" />
                                <InputError :message="form.errors.specialty" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="start_date">Start Date</Label>
                                <Input id="start_date" name="start_date" type="date" v-model="form.start_date" />
                                <InputError :message="form.errors.start_date" />
                            </div>
                            <div>
                                <Label for="completion_date">Completion Date</Label>
                                <Input
                                    id="completion_date"
                                    name="completion_date"
                                    type="date"
                                    v-model="form.completion_date"
                                />
                                <InputError :message="form.errors.completion_date" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="country">Country</Label>
                                <Input id="country" name="country" v-model="form.country" />
                                <InputError :message="form.errors.country" />
                            </div>
                            <div>
                                <Label for="state">State / Province</Label>
                                <Input id="state" name="state" v-model="form.state" />
                                <InputError :message="form.errors.state" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="city">City</Label>
                                <Input id="city" name="city" v-model="form.city" />
                                <InputError :message="form.errors.city" />
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
                                {{ editingId ? 'Update Education Record' : 'Add Education Record' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
