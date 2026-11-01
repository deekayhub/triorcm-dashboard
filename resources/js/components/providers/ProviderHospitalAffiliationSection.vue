<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, Building2, Stethoscope, CheckCircle2 } from '@lucide/vue';
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
    affiliations: Array<any>;
}>();

const { toast } = useToast();

const isSaving = ref(false);
const isDeleting = ref(false);
const openForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    facility_name: '',
    department: '',
    affiliation_type: 'other',
    start_date: '',
    end_date: '',
    status: 'active',
    contact_phone: '',
    contact_email: '',
});

const affiliationTypes = [
    { value: 'admitting', label: 'Admitting' },
    { value: 'courtesy', label: 'Courtesy' },
    { value: 'consulting', label: 'Consulting' },
    { value: 'active', label: 'Active' },
    { value: 'affiliate', label: 'Affiliate' },
    { value: 'other', label: 'Other' },
];

const statuses = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'terminated', label: 'Terminated' },
];

function openCreateForm() {
    editingId.value = null;
    form.defaults({
        facility_name: '',
        department: '',
        affiliation_type: 'other',
        start_date: '',
        end_date: '',
        status: 'active',
        contact_phone: '',
        contact_email: '',
    }).reset();
    openForm.value = true;
}

function openEditForm(affiliation: any) {
    editingId.value = affiliation.id;
    form.defaults({
        facility_name: affiliation.facility_name,
        department: affiliation.department ?? '',
        affiliation_type: affiliation.affiliation_type,
        start_date: affiliation.start_date ?? '',
        end_date: affiliation.end_date ?? '',
        status: affiliation.status,
        contact_phone: affiliation.contact_phone ?? '',
        contact_email: affiliation.contact_email ?? '',
    }).reset();
    openForm.value = true;
}

async function save() {
    isSaving.value = true;

    const url = editingId.value
        ? `/providers/${props.provider.id}/hospital-affiliations/${editingId.value}`
        : `/providers/${props.provider.id}/hospital-affiliations`;

    const options = {
        onSuccess: () => {
            toast({
                title: 'Saved',
                description: editingId.value
                    ? 'Hospital affiliation updated successfully.'
                    : 'Hospital affiliation added successfully.',
                variant: 'success',
            });
            openForm.value = false;
            editingId.value = null;
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to save hospital affiliation. Please try again.',
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
            description: 'Failed to save hospital affiliation. Please try again.',
            variant: 'error',
        });
    }
}

async function deleteAffiliation(id: number) {
    if (!confirm('Are you sure you want to remove this hospital affiliation?')) {
        return;
    }

    isDeleting.value = true;

    try {
        await router.delete(`/providers/${props.provider.id}/hospital-affiliations/${id}`, {
            onSuccess: () => {
                toast({
                    title: 'Removed',
                    description: 'Hospital affiliation has been removed.',
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
            description: 'Failed to remove hospital affiliation.',
            variant: 'error',
        });
    }
}

function getTypeLabel(type: string): string {
    const found = affiliationTypes.find(t => t.value === type);
    return found ? found.label : type;
}

type BadgeVariant = 'default' | 'outline' | 'destructive' | 'secondary';

function getStatusBadge(status: string): { variant: BadgeVariant; class: string } {
    switch (status) {
        case 'active':
            return { variant: 'default', class: 'bg-green-100 text-green-700' };
        case 'inactive':
            return { variant: 'outline', class: 'bg-amber-100 text-amber-700 border-amber-200' };
        case 'terminated':
            return { variant: 'destructive', class: '' };
        default:
            return { variant: 'secondary', class: 'bg-neutral-100 text-neutral-600' };
    }
}
</script>

<template>
    <Head title="Hospital Affiliations" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Hospital Affiliations</h1>
                <p class="text-sm text-muted-foreground">
                    Manage provider hospital and healthcare facility affiliations
                </p>
            </div>
            <Button
                variant="outline"
                size="sm"
                @click="openCreateForm"
                :disabled="isSaving"
            >
                <Plus class="size-4 mr-2" />
                Add Affiliation
            </Button>
        </div>

        <!-- Affiliations Table -->
        <Card class="shadow-none">
            <CardHeader>
                <CardTitle>Hospital Affiliations</CardTitle>
                <CardDescription>
                    {{ affiliations.length }} affiliation{{ affiliations.length !== 1 ? 's' : '' }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="affiliations.length === 0" class="p-8 text-center text-neutral-500">
                    <p>No hospital affiliations found.</p>
                    <Button variant="outline" @click="openCreateForm">Add Affiliation</Button>
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                            <th class="px-5 py-3 font-medium">Facility</th>
                            <th class="px-5 py-3 font-medium">Department</th>
                            <th class="px-5 py-3 font-medium">Affiliation Type</th>
                            <th class="px-5 py-3 font-medium">Dates</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Contact</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="aff in affiliations"
                            :key="aff.id"
                            class="border-b last:border-0 hover:bg-neutral-50"
                        >
                            <td class="px-5 py-3 font-medium">{{ aff.facility_name }}</td>
                            <td class="px-5 py-3 text-neutral-500">{{ aff.department || '-' }}</td>
                            <td class="px-5 py-3 text-neutral-500 capitalize">{{ getTypeLabel(aff.affiliation_type) }}</td>
                            <td class="px-5 py-3 text-neutral-500">
                                <span v-if="aff.start_date">{{ new Date(aff.start_date).toLocaleDateString() }}</span>
                                <span v-if="aff.start_date && aff.end_date"> to </span>
                                <span v-if="aff.end_date">{{ new Date(aff.end_date).toLocaleDateString() }}</span>
                                <span v-if="!aff.start_date && !aff.end_date">-</span>
                            </td>
                            <td class="px-5 py-3">
                                <Badge
                                    :variant="getStatusBadge(aff.status).variant"
                                    :class="getStatusBadge(aff.status).class"
                                >
                                    {{ aff.status }}
                                </Badge>
                            </td>
                            <td class="px-5 py-3 text-neutral-500">
                                {{ aff.contact_phone || aff.contact_email || '-' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="openEditForm(aff)"
                                    class="text-primary"
                                >
                                    Edit
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive"
                                    @click="deleteAffiliation(aff.id)"
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

        <!-- Affiliation Form -->
        <div v-if="openForm" class="max-w-2xl">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>
                        {{ editingId ? 'Edit Affiliation' : 'Add Affiliation' }}
                    </CardTitle>
                    <CardDescription>
                        Enter provider hospital affiliation details
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="save" class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="facility_name">Facility Name *</Label>
                                <Input
                                    id="facility_name"
                                    name="facility_name"
                                    v-model="form.facility_name"
                                    required
                                />
                                <InputError :message="form.errors.facility_name" />
                            </div>
                            <div>
                                <Label for="department">Department</Label>
                                <Input id="department" name="department" v-model="form.department" />
                                <InputError :message="form.errors.department" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="affiliation_type">Affiliation Type *</Label>
                                <Select
                                    id="affiliation_type"
                                    :model-value="form.affiliation_type"
                                    @update:model-value="form.affiliation_type = String($event ?? '')"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="type in affiliationTypes"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.affiliation_type" />
                            </div>
                            <div>
                                <Label for="status">Status *</Label>
                                <Select
                                    id="status"
                                    :model-value="form.status"
                                    @update:model-value="form.status = String($event ?? '')"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="s in statuses"
                                            :key="s.value"
                                            :value="s.value"
                                        >
                                            {{ s.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="start_date">Start Date</Label>
                                <Input id="start_date" name="start_date" type="date" v-model="form.start_date" />
                                <InputError :message="form.errors.start_date" />
                            </div>
                            <div>
                                <Label for="end_date">End Date</Label>
                                <Input id="end_date" name="end_date" type="date" v-model="form.end_date" />
                                <InputError :message="form.errors.end_date" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="contact_phone">Contact Phone</Label>
                                <Input id="contact_phone" name="contact_phone" v-model="form.contact_phone" />
                                <InputError :message="form.errors.contact_phone" />
                            </div>
                            <div>
                                <Label for="contact_email">Contact Email</Label>
                                <Input id="contact_email" name="contact_email" type="email" v-model="form.contact_email" />
                                <InputError :message="form.errors.contact_email" />
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
                                {{ editingId ? 'Update Affiliation' : 'Add Affiliation' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
