<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, Mail, Phone, Building2, User } from '@lucide/vue';
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
    contacts: Array<any>;
}>();

const { toast } = useToast();

const isSaving = ref(false);
const isDeleting = ref(false);
const openForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    contact_name: '',
    job_title: '',
    organization: '',
    email: '',
    phone: '',
    contact_type: 'credentialing',
    preferred_method: 'email',
    notes: '',
});

const contactTypes = [
    { value: 'credentialing', label: 'Credentialing' },
    { value: 'billing', label: 'Billing' },
    { value: 'practice_manager', label: 'Practice Manager' },
    { value: 'other', label: 'Other' },
];

const preferredMethods = [
    { value: 'email', label: 'Email' },
    { value: 'phone', label: 'Phone' },
    { value: 'mail', label: 'Mail' },
];

function openCreateForm() {
    editingId.value = null;
    form.defaults({
        contact_name: '',
        job_title: '',
        organization: '',
        email: '',
        phone: '',
        contact_type: 'credentialing',
        preferred_method: 'email',
        notes: '',
    }).reset();
    openForm.value = true;
}

function openEditForm(contact: any) {
    editingId.value = contact.id;
    form.defaults({
        contact_name: contact.contact_name,
        job_title: contact.job_title ?? '',
        organization: contact.organization ?? '',
        email: contact.email ?? '',
        phone: contact.phone ?? '',
        contact_type: contact.contact_type,
        preferred_method: contact.preferred_method,
        notes: contact.notes ?? '',
    }).reset();
    openForm.value = true;
}

async function save() {
    isSaving.value = true;

    const url = editingId.value
        ? `/providers/${props.provider.id}/contacts/${editingId.value}`
        : `/providers/${props.provider.id}/contacts`;

    const options = {
        onSuccess: () => {
            toast({
                title: 'Saved',
                description: editingId.value
                    ? 'Credentialing contact updated successfully.'
                    : 'Credentialing contact added successfully.',
                variant: 'success',
            });
            openForm.value = false;
            editingId.value = null;
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to save credentialing contact. Please try again.',
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
            description: 'Failed to save credentialing contact. Please try again.',
            variant: 'error',
        });
    }
}

async function deleteContact(id: number) {
    if (!confirm('Are you sure you want to remove this contact?')) {
        return;
    }

    isDeleting.value = true;

    try {
        await router.delete(`/providers/${props.provider.id}/contacts/${id}`, {
            onSuccess: () => {
                toast({
                    title: 'Removed',
                    description: 'Credentialing contact has been removed.',
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
            description: 'Failed to remove credentialing contact.',
            variant: 'error',
        });
    }
}

function getTypeLabel(type: string): string {
    const found = contactTypes.find(t => t.value === type);
    return found ? found.label : type;
}
</script>

<template>
    <Head title="Credentialing Contacts" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Credentialing Contacts</h1>
                <p class="text-sm text-muted-foreground">
                    Manage credentialing contacts for the provider
                </p>
            </div>
            <Button
                variant="outline"
                size="sm"
                @click="openCreateForm"
                :disabled="isSaving"
            >
                <Plus class="size-4 mr-2" />
                Add Contact
            </Button>
        </div>

        <!-- Contacts Table -->
        <Card class="shadow-none">
            <CardHeader>
                <CardTitle>Credentialing Contacts</CardTitle>
                <CardDescription>
                    {{ contacts.length }} contact{{ contacts.length !== 1 ? 's' : '' }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div v-if="contacts.length === 0" class="p-8 text-center text-neutral-500">
                    <p>No credentialing contacts found.</p>
                    <Button variant="outline" @click="openCreateForm">Add Contact</Button>
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b text-left text-[11px] tracking-[0.14em] text-muted-foreground uppercase">
                            <th class="px-5 py-3 font-medium">Contact Name</th>
                            <th class="px-5 py-3 font-medium">Job Title</th>
                            <th class="px-5 py-3 font-medium">Organization</th>
                            <th class="px-5 py-3 font-medium">Email</th>
                            <th class="px-5 py-3 font-medium">Phone</th>
                            <th class="px-5 py-3 font-medium">Contact Type</th>
                            <th class="px-5 py-3 font-medium">Preferred Method</th>
                            <th class="px-5 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="contact in contacts"
                            :key="contact.id"
                            class="border-b last:border-0 hover:bg-neutral-50"
                        >
                            <td class="px-5 py-3 font-medium">{{ contact.contact_name }}</td>
                            <td class="px-5 py-3 text-neutral-500">{{ contact.job_title || '-' }}</td>
                            <td class="px-5 py-3 text-neutral-500">{{ contact.organization || '-' }}</td>
                            <td class="px-5 py-3 text-neutral-500">{{ contact.email || '-' }}</td>
                            <td class="px-5 py-3 text-neutral-500">{{ contact.phone || '-' }}</td>
                            <td class="px-5 py-3 text-neutral-500 capitalize">{{ getTypeLabel(contact.contact_type) }}</td>
                            <td class="px-5 py-3 text-neutral-500 capitalize">{{ contact.preferred_method }}</td>
                            <td class="px-5 py-3 text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="openEditForm(contact)"
                                    class="text-primary"
                                >
                                    Edit
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive"
                                    @click="deleteContact(contact.id)"
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

        <!-- Contact Form -->
        <div v-if="openForm" class="max-w-2xl">
            <Card class="shadow-none">
                <CardHeader>
                    <CardTitle>
                        {{ editingId ? 'Edit Contact' : 'Add Contact' }}
                    </CardTitle>
                    <CardDescription>
                        Enter provider credentialing contact details
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="save" class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="contact_name">Contact Name *</Label>
                                <Input
                                    id="contact_name"
                                    name="contact_name"
                                    v-model="form.contact_name"
                                    required
                                />
                                <InputError :message="form.errors.contact_name" />
                            </div>
                            <div>
                                <Label for="job_title">Job Title</Label>
                                <Input id="job_title" name="job_title" v-model="form.job_title" />
                                <InputError :message="form.errors.job_title" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="organization">Organization</Label>
                                <Input id="organization" name="organization" v-model="form.organization" />
                                <InputError :message="form.errors.organization" />
                            </div>
                            <div>
                                <Label for="contact_type">Contact Type *</Label>
                                <Select
                                    id="contact_type"
                                    :model-value="form.contact_type"
                                    @update:model-value="form.contact_type = String($event ?? '')"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="type in contactTypes"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.contact_type" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="email">Email</Label>
                                <Input id="email" name="email" type="email" v-model="form.email" />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div>
                                <Label for="phone">Phone</Label>
                                <Input id="phone" name="phone" v-model="form.phone" />
                                <InputError :message="form.errors.phone" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <Label for="preferred_method">Preferred Contact Method *</Label>
                                <Select
                                    id="preferred_method"
                                    :model-value="form.preferred_method"
                                    @update:model-value="form.preferred_method = String($event ?? '')"
                                >
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Select method" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="method in preferredMethods"
                                            :key="method.value"
                                            :value="method.value"
                                        >
                                            {{ method.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.preferred_method" />
                            </div>
                            <div>
                                <Label for="notes">Notes</Label>
                                <Textarea id="notes" name="notes" v-model="form.notes" rows="3" />
                                <InputError :message="form.errors.notes" />
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
                                {{ editingId ? 'Update Contact' : 'Add Contact' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
