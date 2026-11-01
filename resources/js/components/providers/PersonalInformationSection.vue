<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    provider: any;
    personalDetail: any;
}>();

const { toast } = useToast();

const isSaving = ref(false);

const form = useForm({
    first_name: props.provider?.first_name ?? '',
    middle_name: props.provider?.middle_name ?? '',
    last_name: props.provider?.last_name ?? '',
    email: props.provider?.email ?? '',
    phone: props.provider?.phone ?? '',
    alternate_phone: props.personalDetail?.alternate_phone ?? '',
    dob: props.provider?.dob ?? '',
    gender: props.provider?.gender ?? '',
    address: props.personalDetail?.address ?? '',
    city: props.personalDetail?.city ?? '',
    state: props.personalDetail?.state ?? '',
    zip_code: props.personalDetail?.zip_code ?? '',
    country: props.personalDetail?.country ?? 'US',
    languages: props.personalDetail?.languages ?? [],
    previous_names: props.personalDetail?.previous_names ?? [],
});

const languagesString = ref(props.personalDetail?.languages?.join(', ') ?? '');
const previousNamesString = ref(props.personalDetail?.previous_names?.join(', ') ?? '');

const save = async () => {
    isSaving.value = true;

    // Convert comma-separated strings to arrays
    form.languages = languagesString.value
        .split(',')
        .map((s: string) => s.trim())
        .filter((s: string) => s.length > 0);

    form.previous_names = previousNamesString.value
        .split(',')
        .map((s: string) => s.trim())
        .filter((s: string) => s.length > 0);

    try {
        form.patch(
            `/providers/${props.provider.id}/personal-details`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast({
                        title: 'Saved',
                        description: 'Personal information has been saved successfully.',
                        variant: 'success',
                    });
                },
                onError: () => {
                    toast({
                        title: 'Error',
                        description: 'Failed to save personal information. Please try again.',
                        variant: 'error',
                    });
                },
                onFinish: () => {
                    isSaving.value = false;
                },
            }
        );
    } catch (error) {
        isSaving.value = false;
        toast({
            title: 'Error',
            description: 'Failed to save personal information. Please try again.',
            variant: 'error',
        });
    }
};
</script>

<template>
    <Head title="Personal Information" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">Personal Information</h1>
                <p class="text-sm text-muted-foreground">
                    Manage core personal details for {{ provider.full_name }}
                </p>
            </div>
        </div>

        <Card class="max-w-4xl shadow-none">
            <CardHeader>
                <CardTitle>Personal Details</CardTitle>
                <CardDescription>
                    Update the provider's personal information. Required fields are marked with an asterisk.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="save" class="space-y-6">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label for="first_name">First Name *</Label>
                            <Input
                                id="first_name"
                                name="first_name"
                                v-model="form.first_name"
                                required
                            />
                            <InputError :message="form.errors.first_name" />
                        </div>
                        <div>
                            <Label for="middle_name">Middle Name</Label>
                            <Input id="middle_name" name="middle_name" v-model="form.middle_name" />
                            <InputError :message="form.errors.middle_name" />
                        </div>
                        <div>
                            <Label for="last_name">Last Name *</Label>
                            <Input id="last_name" name="last_name" v-model="form.last_name" required />
                            <InputError :message="form.errors.last_name" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label for="email">Email *</Label>
                            <Input id="email" name="email" type="email" v-model="form.email" required />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div>
                            <Label for="phone">Phone</Label>
                            <Input id="phone" name="phone" v-model="form.phone" />
                            <InputError :message="form.errors.phone" />
                        </div>
                        <div>
                            <Label for="alternate_phone">Alternate Phone</Label>
                            <Input
                                id="alternate_phone"
                                name="alternate_phone"
                                v-model="form.alternate_phone"
                            />
                            <InputError :message="form.errors.alternate_phone" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label for="dob">Date of Birth</Label>
                            <Input id="dob" name="dob" type="date" v-model="form.dob" />
                            <InputError :message="form.errors.dob" />
                        </div>
                        <div>
                            <Label for="gender">Gender</Label>
                            <select
                                id="gender"
                                name="gender"
                                v-model="form.gender"
                                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                            >
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="form.errors.gender" />
                        </div>
                        <div>
                            <Label for="country">Country</Label>
                            <select
                                id="country"
                                name="country"
                                v-model="form.country"
                                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                            >
                                <option value="US">United States</option>
                                <option value="CA">Canada</option>
                                <option value="GB">United Kingdom</option>
                                <option value="Other">Other</option>
                            </select>
                            <InputError :message="form.errors.country" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label for="address">Address</Label>
                            <Input id="address" name="address" v-model="form.address" />
                            <InputError :message="form.errors.address" />
                        </div>
                        <div>
                            <Label for="city">City</Label>
                            <Input id="city" name="city" v-model="form.city" />
                            <InputError :message="form.errors.city" />
                        </div>
                        <div>
                            <Label for="state">State / Province</Label>
                            <Input id="state" name="state" v-model="form.state" />
                            <InputError :message="form.errors.state" />
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <Label for="zip_code">ZIP / Postal Code</Label>
                            <Input id="zip_code" name="zip_code" v-model="form.zip_code" />
                            <InputError :message="form.errors.zip_code" />
                        </div>
                        <div>
                            <Label for="languages">Languages</Label>
                            <p class="text-xs text-muted-foreground mb-1">
                                Enter comma-separated languages (e.g., English, Spanish)
                            </p>
                            <Input
                                id="languages"
                                v-model="languagesString"
                                placeholder="English, Spanish, French"
                            />
                            <InputError :message="form.errors.languages" />
                        </div>
                        <div>
                            <Label for="previous_names">Previous Names</Label>
                            <p class="text-xs text-muted-foreground mb-1">
                                Enter comma-separated previous names (e.g., Jane Smith, J. Doe)
                            </p>
                            <Input
                                id="previous_names"
                                v-model="previousNamesString"
                                placeholder="Jane Smith, J. Doe"
                            />
                            <InputError :message="form.errors.previous_names" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <span class="text-xs text-muted-foreground">
                            Changes are saved immediately. Required fields: First Name, Last Name, Email.
                        </span>
                        <Button
                            type="submit"
                            :disabled="isSaving"
                            class="bg-primary text-white hover:bg-primary/90"
                        >
                            Save Personal Information
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
