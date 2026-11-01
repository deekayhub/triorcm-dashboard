<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, Stethoscope, CheckCircle2, Award } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    provider: any;
    specialties: Array<any>;
    availableSpecialties: Array<any>;
}>();

const { toast } = useToast();

const isSaving = ref(false);
const isDeleting = ref(false);
const openForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    specialty_id: '',
    is_primary: false,
    subspecialty: '',
    is_board_certified: false,
    board_certification_date: '',
    is_active: true,
});

function openCreateForm() {
    editingId.value = null;
    form.defaults({
        specialty_id: '',
        is_primary: false,
        subspecialty: '',
        is_board_certified: false,
        board_certification_date: '',
        is_active: true,
    }).reset();
    openForm.value = true;
}

function openEditForm(ps: any) {
    editingId.value = ps.id;
    form.defaults({
        specialty_id: ps.specialty_id,
        is_primary: ps.is_primary,
        subspecialty: ps.subspecialty ?? '',
        is_board_certified: ps.is_board_certified,
        board_certification_date: ps.board_certification_date ?? '',
        is_active: ps.is_active,
    }).reset();
    openForm.value = true;
}

async function save() {
    isSaving.value = true;

    const url = editingId.value
        ? `/providers/${props.provider.id}/specialties/${editingId.value}`
        : `/providers/${props.provider.id}/specialties`;

    const options = {
        onSuccess: () => {
            toast({
                title: 'Saved',
                description: editingId.value
                    ? 'Specialty updated successfully.'
                    : 'Specialty added successfully.',
                variant: 'success',
            });
            openForm.value = false;
            editingId.value = null;
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to save specialty. Please try again.',
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
            description: 'Failed to save specialty. Please try again.',
            variant: 'error',
        });
    }
}

async function deleteSpecialty(id: number) {
    if (!confirm('Are you sure you want to remove this specialty?')) {
        return;
    }

    isDeleting.value = true;

    try {
        await router.delete(`/providers/${props.provider.id}/specialties/${id}`, {
            onSuccess: () => {
                toast({
                    title: 'Removed',
                    description: 'Specialty has been removed.',
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
            description: 'Failed to remove specialty.',
            variant: 'error',
        });
    }
}

type BadgeVariant = 'default' | 'outline' | 'destructive' | 'secondary';

function getStatusBadge(
    isActive: boolean,
    isBoardCertified: boolean,
): { variant: BadgeVariant; class: string; label: string } {
    if (!isActive) {
        return { variant: 'secondary', class: 'bg-neutral-100 text-neutral-600', label: 'Inactive' };
    }
    if (isBoardCertified) {
        return { variant: 'default', class: 'bg-green-100 text-green-700', label: 'Board Certified' };
    }
    return { variant: 'outline', class: 'bg-blue-100 text-blue-700 border-blue-200', label: 'Active' };
}

function getPrimaryBadge(isPrimary: boolean) {
    return isPrimary
        ? { variant: 'destructive', class: 'bg-primary text-primary-foreground', label: 'Primary' }
        : { variant: 'outline', class: 'bg-neutral-100 text-neutral-600', label: '' };
}
</script>

<template>
    <Head title="Specialties" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Specialties</h1>
                <p class="text-sm text-muted-foreground">
                    Manage provider specialties and subspecialties
                </p>
            </div>
            <Button
                variant="outline"
                size="sm"
                @click="openCreateForm"
                :disabled="isSaving"
            >
                <Plus class="size-4 mr-2" />
                Add Specialty
            </Button>
        </div>

        <!-- Specialties Table -->
        <Card class="shadow-none">
            <CardHeader>
                <CardTitle>Provider Specialties</CardTitle>
                <CardDescription>
                    {{ specialties.length }} specialty{{ specialties.length !== 1 ? 'ies' : '' }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="specialties.length === 0" class="p-8 text-center text-neutral-500">
                    <p>No specialties found.</p>
                    <Button variant="outline" @click="openCreateForm">Add Specialty</Button>
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                            <th class="px-5 py-3 font-medium">Specialty</th>
                            <th class="px-5 py-3 font-medium">Code</th>
                            <th class="px-5 py-3 font-medium">Type</th>
                            <th class="px-5 py-3 font-medium">Subspecialty</th>
                            <th class="px-5 py-3 font-medium">Board Cert.</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="ps in specialties"
                            :key="ps.id"
                            class="border-b last:border-0 hover:bg-neutral-50"
                        >
                            <td class="px-5 py-3 font-medium">
                                {{ ps.specialty?.name || 'Unknown' }}
                                <Badge
                                    v-if="ps.is_primary"
                                    variant="destructive"
                                    class="ml-2 text-xs"
                                >
                                    Primary
                                </Badge>
                            </td>
                            <td class="px-5 py-3 text-neutral-500">
                                {{ ps.specialty?.code || '-' }}
                            </td>
                            <td class="px-5 py-3 text-neutral-500 capitalize">
                                {{ ps.is_primary ? 'Primary' : 'Secondary' }}
                            </td>
                            <td class="px-5 py-3 text-neutral-500">
                                {{ ps.subspecialty || '-' }}
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    v-if="ps.is_board_certified"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700"
                                >
                                    <Award class="size-3.5" />
                                    Board Certified
                                    <span v-if="ps.board_certification_date" class="ml-1">
                                        ({{
                                            new Date(ps.board_certification_date).toLocaleDateString()
                                        }})
                                    </span>
                                </span>
                                <span v-else class="text-neutral-400 text-xs">
                                    Not Certified
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <Badge
                                    :variant="getStatusBadge(ps.is_active, ps.is_board_certified).variant"
                                    :class="getStatusBadge(ps.is_active, ps.is_board_certified).class"
                                >
                                    {{ getStatusBadge(ps.is_active, ps.is_board_certified).label }}
                                </Badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="openEditForm(ps)"
                                    class="text-primary"
                                >
                                    Edit
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive"
                                    @click="deleteSpecialty(ps.id)"
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

        <!-- Specialty Form -->
        <div v-if="openForm" class="max-w-2xl">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>
                        {{ editingId ? 'Edit Specialty' : 'Add Specialty' }}
                    </CardTitle>
                    <CardDescription>
                        Select a specialty and configure board certification details
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="save" class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="specialty_id">Specialty *</Label>
                                <Select
                                    id="specialty_id"
                                    :model-value="form.specialty_id"
                                    @update:model-value="form.specialty_id = String($event ?? '')"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select specialty" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="s in availableSpecialties"
                                            :key="s.id"
                                            :value="s.id"
                                        >
                                            {{ s.name }}{{ s.code ? ' (' + s.code + ')' : '' }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.specialty_id" />
                            </div>
                            <div>
                                <Label for="is_primary">Primary Specialty</Label>
                                <div class="flex items-center gap-2">
                                    <input
                                        id="is_primary"
                                        type="checkbox"
                                        v-model="form.is_primary"
                                        class="rounded border-neutral-300 text-primary focus:ring-primary"
                                    />
                                    <label for="is_primary" class="text-sm text-neutral-600">
                                        Mark as primary specialty
                                    </label>
                                </div>
                                <p class="text-xs text-neutral-400 mt-1">
                                    A provider can have only one active primary specialty.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="subspecialty">Subspecialty</Label>
                                <Input
                                    id="subspecialty"
                                    name="subspecialty"
                                    v-model="form.subspecialty"
                                    placeholder="e.g., Interventional Cardiology"
                                />
                                <InputError :message="form.errors.subspecialty" />
                            </div>
                            <div>
                                <Label for="is_board_certified">Board Certified</Label>
                                <div class="flex items-center gap-2">
                                    <input
                                        id="is_board_certified"
                                        type="checkbox"
                                        v-model="form.is_board_certified"
                                        class="rounded border-neutral-300 text-primary focus:ring-primary"
                                    />
                                    <label for="is_board_certified" class="text-sm text-neutral-600">
                                        Board certified
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div v-if="form.is_board_certified">
                                <Label for="board_certification_date">Board Certification Date</Label>
                                <Input
                                    id="board_certification_date"
                                    name="board_certification_date"
                                    type="date"
                                    v-model="form.board_certification_date"
                                />
                                <InputError :message="form.errors.board_certification_date" />
                            </div>
                            <div>
                                <Label for="is_active">Active</Label>
                                <div class="flex items-center gap-2">
                                    <input
                                        id="is_active"
                                        type="checkbox"
                                        v-model="form.is_active"
                                        class="rounded border-neutral-300 text-primary focus:ring-primary"
                                    />
                                    <label for="is_active" class="text-sm text-neutral-600">
                                        Specialty is active
                                    </label>
                                </div>
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
                                {{ editingId ? 'Update Specialty' : 'Add Specialty' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
