<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, MapPin, CheckCircle2, Building2 } from '@lucide/vue';
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
    locations: Array<any>;
}>();

const { toast } = useToast();

const isSaving = ref(false);
const isDeleting = ref(false);
const openForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    location_name: '',
    practice_name: '',
    address: '',
    city: '',
    state: '',
    zip_code: '',
    country: 'US',
    phone: '',
    fax: '',
    email: '',
    office_hours: '',
    is_primary: false,
    is_billing_address: false,
    effective_start_date: '',
    effective_end_date: '',
    is_active: true,
});

const officeHoursString = ref('');

function openCreateForm() {
    editingId.value = null;
    form.defaults({
        location_name: '',
        practice_name: '',
        address: '',
        city: '',
        state: '',
        zip_code: '',
        country: 'US',
        phone: '',
        fax: '',
        email: '',
        office_hours: '',
        is_primary: false,
        is_billing_address: false,
        effective_start_date: '',
        effective_end_date: '',
        is_active: true,
    }).reset();
    officeHoursString.value = '';
    openForm.value = true;
}

function openEditForm(location: any) {
    editingId.value = location.id;
    form.defaults({
        location_name: location.location_name,
        practice_name: location.practice_name ?? '',
        address: location.address,
        city: location.city,
        state: location.state,
        zip_code: location.zip_code,
        country: location.country ?? 'US',
        phone: location.phone ?? '',
        fax: location.fax ?? '',
        email: location.email ?? '',
        office_hours: '',
        is_primary: location.is_primary,
        is_billing_address: location.is_billing_address,
        effective_start_date: location.effective_start_date ?? '',
        effective_end_date: location.effective_end_date ?? '',
        is_active: location.is_active,
    }).reset();
    officeHoursString.value = location.office_hours ? JSON.stringify(location.office_hours, null, 2) : '';
    openForm.value = true;
}

async function save() {
    isSaving.value = true;

    // Parse office hours from JSON string
    let officeHours = null;
    if (officeHoursString.value.trim()) {
        try {
            officeHours = JSON.parse(officeHoursString.value);
        } catch (e) {
            toast({
                title: 'Invalid JSON',
                description: 'Office hours must be valid JSON.',
                variant: 'error',
            });
            isSaving.value = false;
            return;
        }
    }

    const url = editingId.value
        ? `/providers/${props.provider.id}/locations/${editingId.value}`
        : `/providers/${props.provider.id}/locations`;

    const options = {
        onSuccess: () => {
            toast({
                title: 'Saved',
                description: editingId.value
                    ? 'Location updated successfully.'
                    : 'Location added successfully.',
                variant: 'success',
            });
            openForm.value = false;
            editingId.value = null;
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to save location. Please try again.',
                variant: 'error',
            });
        },
        onFinish: () => {
            isSaving.value = false;
        },
    };

    try {
        form.transform((data) => ({ ...data, office_hours: officeHours }));

        if (editingId.value) {
            form.patch(url, options);
        } else {
            form.post(url, options);
        }
    } catch (error) {
        isSaving.value = false;
        toast({
            title: 'Error',
            description: 'Failed to save location. Please try again.',
            variant: 'error',
        });
    }
}

async function deleteLocation(id: number) {
    if (!confirm('Are you sure you want to remove this location?')) {
        return;
    }

    isDeleting.value = true;

    try {
        await router.delete(`/providers/${props.provider.id}/locations/${id}`, {
            onSuccess: () => {
                toast({
                    title: 'Removed',
                    description: 'Location has been removed.',
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
            description: 'Failed to remove location.',
            variant: 'error',
        });
    }
}

type BadgeVariant = 'default' | 'outline' | 'destructive' | 'secondary';

function getStatusBadge(
    isActive: boolean,
    isPrimary: boolean,
): { variant: BadgeVariant; class: string; label: string } {
    if (!isActive) {
        return { variant: 'secondary', class: 'bg-neutral-100 text-neutral-600', label: 'Inactive' };
    }
    if (isPrimary) {
        return { variant: 'destructive', class: 'bg-primary text-primary-foreground', label: 'Primary' };
    }
    return { variant: 'outline', class: 'bg-blue-100 text-blue-700 border-blue-200', label: 'Active' };
}

function formatOfficeHours(oh: any): string {
    if (!oh || typeof oh !== 'object') return '-';
    const entries = Object.entries(oh);
    if (entries.length === 0) return '-';
    return entries.map(([day, hours]) => `${day}: ${hours}`).join(', ');
}
</script>

<template>
    <Head title="Practice Locations" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Practice Locations</h1>
                <p class="text-sm text-muted-foreground">
                    Manage provider practice locations
                </p>
            </div>
            <Button
                variant="outline"
                size="sm"
                @click="openCreateForm"
                :disabled="isSaving"
            >
                <Plus class="size-4 mr-2" />
                Add Location
            </Button>
        </div>

        <!-- Locations Table -->
        <Card class="shadow-none">
            <CardHeader>
                <CardTitle>Practice Locations</CardTitle>
                <CardDescription>
                    {{ locations.length }} location{{ locations.length !== 1 ? 's' : '' }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="locations.length === 0" class="p-8 text-center text-neutral-500">
                    <p>No locations found.</p>
                    <Button variant="outline" @click="openCreateForm">Add Location</Button>
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                            <th class="px-5 py-3 font-medium">Location</th>
                            <th class="px-5 py-3 font-medium">Practice</th>
                            <th class="px-5 py-3 font-medium">Address</th>
                            <th class="px-5 py-3 font-medium">Phone</th>
                            <th class="px-5 py-3 font-medium">Primary</th>
                            <th class="px-5 py-3 font-medium">Billing</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="loc in locations"
                            :key="loc.id"
                            class="border-b last:border-0 hover:bg-neutral-50"
                        >
                            <td class="px-5 py-3 font-medium">
                                {{ loc.location_name }}
                                <Badge v-if="loc.is_primary" variant="destructive" class="ml-2 text-xs">
                                    Primary
                                </Badge>
                            </td>
                            <td class="px-5 py-3 text-neutral-500">{{ loc.practice_name || '-' }}</td>
                            <td class="px-5 py-3 text-neutral-500">
                                {{ loc.address }}, {{ loc.city }}, {{ loc.state }} {{ loc.zip_code }}
                            </td>
                            <td class="px-5 py-3 text-neutral-500">{{ loc.phone || '-' }}</td>
                            <td class="px-5 py-3">
                                <Badge
                                    :variant="loc.is_billing_address ? 'outline' : 'secondary'"
                                    :class="loc.is_billing_address ? 'bg-amber-100 text-amber-700 border-amber-200' : 'hidden'"
                                >
                                    Billing
                                </Badge>
                                <span v-if="!loc.is_billing_address" class="text-neutral-400 text-xs">-</span>
                            </td>
                            <td class="px-5 py-3">
                                <Badge
                                    :variant="getStatusBadge(loc.is_active, loc.is_primary).variant"
                                    :class="getStatusBadge(loc.is_active, loc.is_primary).class"
                                >
                                    {{ getStatusBadge(loc.is_active, loc.is_primary).label }}
                                </Badge>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="openEditForm(loc)"
                                    class="text-primary"
                                >
                                    Edit
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive"
                                    @click="deleteLocation(loc.id)"
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

        <!-- Location Form -->
        <div v-if="openForm" class="max-w-2xl">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>
                        {{ editingId ? 'Edit Location' : 'Add Location' }}
                    </CardTitle>
                    <CardDescription>
                        Enter provider practice location details
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="save" class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="location_name">Location Name *</Label>
                                <Input
                                    id="location_name"
                                    name="location_name"
                                    v-model="form.location_name"
                                    required
                                />
                                <InputError :message="form.errors.location_name" />
                            </div>
                            <div>
                                <Label for="practice_name">Practice Name</Label>
                                <Input id="practice_name" name="practice_name" v-model="form.practice_name" />
                                <InputError :message="form.errors.practice_name" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="address">Address *</Label>
                                <Input id="address" name="address" v-model="form.address" required />
                                <InputError :message="form.errors.address" />
                            </div>
                            <div>
                                <Label for="city">City *</Label>
                                <Input id="city" name="city" v-model="form.city" required />
                                <InputError :message="form.errors.city" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="state">State *</Label>
                                <Input id="state" name="state" v-model="form.state" required />
                                <InputError :message="form.errors.state" />
                            </div>
                            <div>
                                <Label for="zip_code">ZIP Code *</Label>
                                <Input id="zip_code" name="zip_code" v-model="form.zip_code" required />
                                <InputError :message="form.errors.zip_code" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="country">Country</Label>
                                <Input id="country" name="country" v-model="form.country" />
                                <InputError :message="form.errors.country" />
                            </div>
                            <div>
                                <Label for="phone">Phone</Label>
                                <Input id="phone" name="phone" v-model="form.phone" />
                                <InputError :message="form.errors.phone" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="fax">Fax</Label>
                                <Input id="fax" name="fax" v-model="form.fax" />
                                <InputError :message="form.errors.fax" />
                            </div>
                            <div>
                                <Label for="email">Email</Label>
                                <Input id="email" name="email" type="email" v-model="form.email" />
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>

                        <div>
                            <Label for="office_hours">Office Hours</Label>
                            <p class="text-xs text-muted-foreground mb-1">
                                Enter office hours as JSON (e.g., {"monday": "9:00-17:00", "tuesday": "9:00-17:00"})
                            </p>
                            <Textarea
                                id="office_hours"
                                v-model="officeHoursString"
                                rows="4"
                                placeholder='{"monday": "9:00-17:00", "tuesday": "9:00-17:00"}'
                            />
                            <InputError :message="form.errors.office_hours" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="flex items-center gap-2">
                                <input
                                    id="is_primary"
                                    type="checkbox"
                                    v-model="form.is_primary"
                                    class="rounded border-neutral-300 text-primary focus:ring-primary"
                                />
                                <label for="is_primary" class="text-sm text-neutral-600">
                                    Primary Location
                                </label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input
                                    id="is_billing_address"
                                    type="checkbox"
                                    v-model="form.is_billing_address"
                                    class="rounded border-neutral-300 text-primary focus:ring-primary"
                                />
                                <label for="is_billing_address" class="text-sm text-neutral-600">
                                    Billing Address
                                </label>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="effective_start_date">Effective Start Date</Label>
                                <Input
                                    id="effective_start_date"
                                    name="effective_start_date"
                                    type="date"
                                    v-model="form.effective_start_date"
                                />
                                <InputError :message="form.errors.effective_start_date" />
                            </div>
                            <div>
                                <Label for="effective_end_date">Effective End Date</Label>
                                <Input
                                    id="effective_end_date"
                                    name="effective_end_date"
                                    type="date"
                                    v-model="form.effective_end_date"
                                />
                                <InputError :message="form.errors.effective_end_date" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="flex items-center gap-2">
                                <input
                                    id="is_active"
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="rounded border-neutral-300 text-primary focus:ring-primary"
                                />
                                <label for="is_active" class="text-sm text-neutral-600">
                                    Location is active
                                </label>
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
                                {{ editingId ? 'Update Location' : 'Add Location' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
