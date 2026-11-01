<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    provider: any;
    references: Array<any>;
}>();

const { toast } = useToast();

const isSaving = ref(false);
const isDeleting = ref(false);
const openForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    reference_name: '',
    organization: '',
    designation: '',
    relationship: '',
    email: '',
    phone: '',
    reference_date: '',
    verification_status: 'unverified',
    notes: '',
});

const verificationStatuses = [
    { value: 'unverified', label: 'Unverified' },
    { value: 'pending', label: 'Pending' },
    { value: 'verified', label: 'Verified' },
    { value: 'failed', label: 'Failed' },
];

function blankDefaults() {
    return {
        reference_name: '',
        organization: '',
        designation: '',
        relationship: '',
        email: '',
        phone: '',
        reference_date: '',
        verification_status: 'unverified',
        notes: '',
    };
}

function openCreateForm() {
    editingId.value = null;
    form.defaults(blankDefaults()).reset();
    form.clearErrors();
    openForm.value = true;
}

function openEditForm(reference: any) {
    editingId.value = reference.id;
    form.defaults({
        reference_name: reference.reference_name,
        organization: reference.organization ?? '',
        designation: reference.designation ?? '',
        relationship: reference.relationship,
        email: reference.email ?? '',
        phone: reference.phone ?? '',
        reference_date: reference.reference_date ?? '',
        verification_status: reference.verification_status,
        notes: reference.notes ?? '',
    }).reset();
    form.clearErrors();
    openForm.value = true;
}

async function save() {
    isSaving.value = true;

    const url = editingId.value
        ? `/providers/${props.provider.id}/references/${editingId.value}`
        : `/providers/${props.provider.id}/references`;

    const options = {
        onSuccess: () => {
            toast({
                title: 'Saved',
                description: editingId.value
                    ? 'Professional reference updated successfully.'
                    : 'Professional reference added successfully.',
                variant: 'success',
            });
            openForm.value = false;
            editingId.value = null;
        },
        onError: () => {
            toast({
                title: 'Error',
                description:
                    'Failed to save professional reference. Please try again.',
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
            description:
                'Failed to save professional reference. Please try again.',
            variant: 'error',
        });
    }
}

async function deleteReference(id: number) {
    if (
        !confirm('Are you sure you want to remove this professional reference?')
    ) {
        return;
    }

    isDeleting.value = true;

    try {
        await router.delete(
            `/providers/${props.provider.id}/references/${id}`,
            {
                onSuccess: () => {
                    toast({
                        title: 'Removed',
                        description: 'Professional reference has been removed.',
                        variant: 'success',
                    });
                },
                onFinish: () => {
                    isDeleting.value = false;
                },
            },
        );
    } catch (error) {
        isDeleting.value = false;
        toast({
            title: 'Error',
            description: 'Failed to remove professional reference.',
            variant: 'error',
        });
    }
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
            return {
                variant: 'outline',
                class: 'bg-amber-100 text-amber-700 border-amber-200',
            };
        case 'failed':
            return { variant: 'destructive', class: '' };
        default:
            return {
                variant: 'secondary',
                class: 'bg-neutral-100 text-neutral-600',
            };
    }
}
</script>

<template>
    <Head title="Professional References" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">
                    Professional References
                </h1>
                <p class="text-sm text-muted-foreground">
                    Track professional references for credentialing
                </p>
            </div>
            <Button
                variant="outline"
                size="sm"
                :disabled="isSaving"
                @click="openCreateForm"
            >
                <Plus class="mr-2 size-4" />
                Add Reference
            </Button>
        </div>

        <Card class="shadow-none">
            <CardHeader>
                <CardTitle>Professional References</CardTitle>
                <CardDescription>
                    {{ references.length }} reference{{
                        references.length !== 1 ? 's' : ''
                    }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    v-if="references.length === 0"
                    class="p-8 text-center text-neutral-500"
                >
                    <p>No professional references found.</p>
                    <Button variant="outline" @click="openCreateForm"
                        >Add Reference</Button
                    >
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead>
                            <tr
                                class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase"
                            >
                                <th scope="col" class="px-5 py-3 font-medium">
                                    Reference
                                </th>
                                <th scope="col" class="px-5 py-3 font-medium">
                                    Organization
                                </th>
                                <th scope="col" class="px-5 py-3 font-medium">
                                    Relationship
                                </th>
                                <th scope="col" class="px-5 py-3 font-medium">
                                    Contact
                                </th>
                                <th scope="col" class="px-5 py-3 font-medium">
                                    Date
                                </th>
                                <th scope="col" class="px-5 py-3 font-medium">
                                    Status
                                </th>
                                <th
                                    scope="col"
                                    class="px-5 py-3 text-right font-medium"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="reference in references"
                                :key="reference.id"
                                class="border-b last:border-0 hover:bg-neutral-50"
                            >
                                <td class="px-5 py-3">
                                    <p class="font-medium">
                                        {{ reference.reference_name }}
                                    </p>
                                    <p
                                        v-if="reference.designation"
                                        class="text-xs text-neutral-500"
                                    >
                                        {{ reference.designation }}
                                    </p>
                                </td>
                                <td class="px-5 py-3 text-neutral-500">
                                    {{ reference.organization || '-' }}
                                </td>
                                <td class="px-5 py-3 text-neutral-500">
                                    {{ reference.relationship }}
                                </td>
                                <td class="px-5 py-3 text-neutral-500">
                                    <p v-if="reference.email">
                                        {{ reference.email }}
                                    </p>
                                    <p v-if="reference.phone" class="text-xs">
                                        {{ reference.phone }}
                                    </p>
                                    <span
                                        v-if="
                                            !reference.email && !reference.phone
                                        "
                                        >-</span
                                    >
                                </td>
                                <td class="px-5 py-3 text-neutral-500">
                                    {{
                                        reference.reference_date
                                            ? new Date(
                                                  reference.reference_date,
                                              ).toLocaleDateString()
                                            : '-'
                                    }}
                                </td>
                                <td class="px-5 py-3">
                                    <Badge
                                        :variant="
                                            getStatusBadge(
                                                reference.verification_status,
                                            ).variant
                                        "
                                        :class="
                                            getStatusBadge(
                                                reference.verification_status,
                                            ).class
                                        "
                                    >
                                        {{ reference.verification_status }}
                                    </Badge>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="text-primary"
                                        @click="openEditForm(reference)"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="text-destructive"
                                        :disabled="isDeleting"
                                        @click="deleteReference(reference.id)"
                                    >
                                        Remove
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <div v-if="openForm" class="max-w-2xl">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>
                        {{
                            editingId
                                ? 'Edit Professional Reference'
                                : 'Add Professional Reference'
                        }}
                    </CardTitle>
                    <CardDescription>
                        Enter the reference's contact and professional details
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form class="space-y-6" @submit.prevent="save">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="reference_name"
                                    >Reference Name *</Label
                                >
                                <Input
                                    id="reference_name"
                                    v-model="form.reference_name"
                                    name="reference_name"
                                    required
                                />
                                <InputError
                                    :message="form.errors.reference_name"
                                />
                            </div>
                            <div>
                                <Label for="relationship">Relationship *</Label>
                                <Input
                                    id="relationship"
                                    v-model="form.relationship"
                                    name="relationship"
                                    required
                                />
                                <InputError
                                    :message="form.errors.relationship"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="organization">Organization</Label>
                                <Input
                                    id="organization"
                                    v-model="form.organization"
                                    name="organization"
                                />
                                <InputError
                                    :message="form.errors.organization"
                                />
                            </div>
                            <div>
                                <Label for="designation">Designation</Label>
                                <Input
                                    id="designation"
                                    v-model="form.designation"
                                    name="designation"
                                />
                                <InputError
                                    :message="form.errors.designation"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    name="email"
                                    type="email"
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div>
                                <Label for="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    name="phone"
                                    type="tel"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="reference_date"
                                    >Reference Date</Label
                                >
                                <Input
                                    id="reference_date"
                                    v-model="form.reference_date"
                                    name="reference_date"
                                    type="date"
                                />
                                <InputError
                                    :message="form.errors.reference_date"
                                />
                            </div>
                            <div>
                                <Label for="verification_status"
                                    >Verification Status *</Label
                                >
                                <Select
                                    id="verification_status"
                                    :model-value="form.verification_status"
                                    @update:model-value="
                                        form.verification_status = String(
                                            $event ?? '',
                                        )
                                    "
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue
                                            placeholder="Select status"
                                        />
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
                                <InputError
                                    :message="form.errors.verification_status"
                                />
                            </div>
                        </div>

                        <div>
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                name="notes"
                                rows="3"
                            />
                            <InputError :message="form.errors.notes" />
                        </div>

                        <div class="flex items-center justify-between pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                @click="openForm = false"
                            >
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="isSaving">
                                {{
                                    editingId
                                        ? 'Update Reference'
                                        : 'Add Reference'
                                }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
