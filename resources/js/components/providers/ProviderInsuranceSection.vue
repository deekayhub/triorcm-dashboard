<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, ShieldCheck, DollarSign, Calendar, FileText } from '@lucide/vue';
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
    insurances: Array<any>;
}>();

const { toast } = useToast();

const isSaving = ref(false);
const isDeleting = ref(false);
const openForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    carrier: '',
    policy_number: '',
    coverage_type: 'claims_made',
    per_claim_amount: '',
    aggregate_amount: '',
    effective_date: '',
    expiration_date: '',
    agent_contact: '',
    agent_phone: '',
    verification_status: 'unverified',
});

const coverageTypes = [
    { value: 'claims_made', label: 'Claims Made' },
    { value: 'occurrence', label: 'Occurrence' },
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
        carrier: '',
        policy_number: '',
        coverage_type: 'claims_made',
        per_claim_amount: '',
        aggregate_amount: '',
        effective_date: '',
        expiration_date: '',
        agent_contact: '',
        agent_phone: '',
        verification_status: 'unverified',
    }).reset();
    openForm.value = true;
}

function openEditForm(insurance: any) {
    editingId.value = insurance.id;
    form.defaults({
        carrier: insurance.carrier,
        policy_number: insurance.policy_number,
        coverage_type: insurance.coverage_type,
        per_claim_amount: insurance.per_claim_amount ?? '',
        aggregate_amount: insurance.aggregate_amount ?? '',
        effective_date: insurance.effective_date ?? '',
        expiration_date: insurance.expiration_date ?? '',
        agent_contact: insurance.agent_contact ?? '',
        agent_phone: insurance.agent_phone ?? '',
        verification_status: insurance.verification_status,
    }).reset();
    openForm.value = true;
}

async function save() {
    isSaving.value = true;

    const url = editingId.value
        ? `/providers/${props.provider.id}/insurances/${editingId.value}`
        : `/providers/${props.provider.id}/insurances`;

    const options = {
        onSuccess: () => {
            toast({
                title: 'Saved',
                description: editingId.value
                    ? 'Insurance policy updated successfully.'
                    : 'Insurance policy added successfully.',
                variant: 'success',
            });
            openForm.value = false;
            editingId.value = null;
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to save insurance policy. Please try again.',
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
            description: 'Failed to save insurance policy. Please try again.',
            variant: 'error',
        });
    }
}

async function deleteInsurance(id: number) {
    if (!confirm('Are you sure you want to remove this insurance policy?')) {
        return;
    }

    isDeleting.value = true;

    try {
        await router.delete(`/providers/${props.provider.id}/insurances/${id}`, {
            onSuccess: () => {
                toast({
                    title: 'Removed',
                    description: 'Insurance policy has been removed.',
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
            description: 'Failed to remove insurance policy.',
            variant: 'error',
        });
    }
}

function getTypeLabel(type: string): string {
    const found = coverageTypes.find(t => t.value === type);
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

function isExpired(insurance: any): boolean {
    if (!insurance.expiration_date) return false;
    return new Date(insurance.expiration_date) < new Date();
}
</script>

<template>
    <Head title="Professional Liability Insurance" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Professional Liability Insurance</h1>
                <p class="text-sm text-muted-foreground">
                    Track provider malpractice coverage
                </p>
            </div>
            <Button
                variant="outline"
                size="sm"
                @click="openCreateForm"
                :disabled="isSaving"
            >
                <Plus class="size-4 mr-2" />
                Add Policy
            </Button>
        </div>

        <!-- Insurance Policies Table -->
        <Card class="shadow-none">
            <CardHeader>
                <CardTitle>Insurance Policies</CardTitle>
                <CardDescription>
                    {{ insurances.length }} policy{{ insurances.length !== 1 ? 'ies' : '' }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="insurances.length === 0" class="p-8 text-center text-neutral-500">
                    <p>No insurance policies found.</p>
                    <Button variant="outline" @click="openCreateForm">Add Policy</Button>
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                            <th class="px-5 py-3 font-medium">Carrier</th>
                            <th class="px-5 py-3 font-medium">Policy Number</th>
                            <th class="px-5 py-3 font-medium">Coverage Type</th>
                            <th class="px-5 py-3 font-medium">Per Claim</th>
                            <th class="px-5 py-3 font-medium">Aggregate</th>
                            <th class="px-5 py-3 font-medium">Expires</th>
                            <th class="px-5 py-3 font-medium">Verification</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="ins in insurances"
                            :key="ins.id"
                            class="border-b last:border-0 hover:bg-neutral-50"
                        >
                            <td class="px-5 py-3 font-medium">{{ ins.carrier }}</td>
                            <td class="px-5 py-3 text-neutral-500">{{ ins.policy_number }}</td>
                            <td class="px-5 py-3 text-neutral-500 capitalize">{{ getTypeLabel(ins.coverage_type) }}</td>
                            <td class="px-5 py-3 text-neutral-500">
                                ${{ ins.per_claim_amount ? Number(ins.per_claim_amount).toLocaleString() : '-' }}
                            </td>
                            <td class="px-5 py-3 text-neutral-500">
                                ${{ ins.aggregate_amount ? Number(ins.aggregate_amount).toLocaleString() : '-' }}
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    :class="
                                        isExpired(ins)
                                            ? 'text-red-600 font-medium'
                                            : 'text-neutral-500'
                                    "
                                >
                                    {{ ins.expiration_date ? new Date(ins.expiration_date).toLocaleDateString() : '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <Badge
                                    :variant="getStatusBadge(ins.verification_status).variant"
                                    :class="getStatusBadge(ins.verification_status).class"
                                >
                                    {{ ins.verification_status }}
                                </Badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="openEditForm(ins)"
                                    class="text-primary"
                                >
                                    Edit
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive"
                                    @click="deleteInsurance(ins.id)"
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

        <!-- Insurance Form -->
        <div v-if="openForm" class="max-w-2xl">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>
                        {{ editingId ? 'Edit Insurance Policy' : 'Add Insurance Policy' }}
                    </CardTitle>
                    <CardDescription>
                        Enter provider professional liability insurance details
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="save" class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="carrier">Insurance Carrier *</Label>
                                <Input
                                    id="carrier"
                                    name="carrier"
                                    v-model="form.carrier"
                                    required
                                />
                                <InputError :message="form.errors.carrier" />
                            </div>
                            <div>
                                <Label for="policy_number">Policy Number *</Label>
                                <Input
                                    id="policy_number"
                                    name="policy_number"
                                    v-model="form.policy_number"
                                    required
                                />
                                <InputError :message="form.errors.policy_number" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="coverage_type">Coverage Type *</Label>
                                <Select
                                    id="coverage_type"
                                    :model-value="form.coverage_type"
                                    @update:model-value="form.coverage_type = String($event ?? '')"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="type in coverageTypes"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.coverage_type" />
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

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="per_claim_amount">Per-Claim Coverage Amount ($)</Label>
                                <Input
                                    id="per_claim_amount"
                                    name="per_claim_amount"
                                    type="number"
                                    v-model="form.per_claim_amount"
                                />
                                <InputError :message="form.errors.per_claim_amount" />
                            </div>
                            <div>
                                <Label for="aggregate_amount">Aggregate Coverage Amount ($)</Label>
                                <Input
                                    id="aggregate_amount"
                                    name="aggregate_amount"
                                    type="number"
                                    v-model="form.aggregate_amount"
                                />
                                <InputError :message="form.errors.aggregate_amount" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="effective_date">Effective Date *</Label>
                                <Input
                                    id="effective_date"
                                    name="effective_date"
                                    type="date"
                                    v-model="form.effective_date"
                                    required
                                />
                                <InputError :message="form.errors.effective_date" />
                            </div>
                            <div>
                                <Label for="expiration_date">Expiration Date *</Label>
                                <Input
                                    id="expiration_date"
                                    name="expiration_date"
                                    type="date"
                                    v-model="form.expiration_date"
                                    required
                                />
                                <InputError :message="form.errors.expiration_date" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="agent_contact">Agent Contact</Label>
                                <Input id="agent_contact" name="agent_contact" v-model="form.agent_contact" />
                                <InputError :message="form.errors.agent_contact" />
                            </div>
                            <div>
                                <Label for="agent_phone">Agent Phone</Label>
                                <Input id="agent_phone" name="agent_phone" v-model="form.agent_phone" />
                                <InputError :message="form.errors.agent_phone" />
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
                                {{ editingId ? 'Update Policy' : 'Add Policy' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
