<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { index as providersIndex } from '@/routes/providers';
import ProviderProfileLayout from '@/components/providers/ProviderProfileLayout.vue';
import PersonalInformationSection from '@/components/providers/PersonalInformationSection.vue';
import ProviderIdentifierSection from '@/components/providers/ProviderIdentifierSection.vue';
import ProviderEducationSection from '@/components/providers/ProviderEducationSection.vue';
import ProviderSpecialtySection from '@/components/providers/ProviderSpecialtySection.vue';
import ProviderLocationSection from '@/components/providers/ProviderLocationSection.vue';
import ProviderHospitalAffiliationSection from '@/components/providers/ProviderHospitalAffiliationSection.vue';
import ProviderContactSection from '@/components/providers/ProviderContactSection.vue';
import ProviderInsuranceSection from '@/components/providers/ProviderInsuranceSection.vue';
import ProviderEmploymentSection from '@/components/providers/ProviderEmploymentSection.vue';
import ProviderReferenceSection from '@/components/providers/ProviderReferenceSection.vue';
import ProviderDisclosureSection from '@/components/providers/ProviderDisclosureSection.vue';
import ProviderDocumentSection from '@/components/providers/ProviderDocumentSection.vue';

const props = defineProps<{
    provider: any;
    availableSpecialties: Array<any>;
    disclosureQuestions: Array<any>;
}>();

const activeSection = ref('personal-information');

const expandedGroup = ref<string | null>(null);

type Section = {
    key: string;
    title: string;
    completed: boolean;
};

type SectionGroup = {
    key: string;
    title: string;
    sections: Section[];
};

const sectionGroups: SectionGroup[] = [
    {
        key: 'profile-identity',
        title: 'Profile & Identity',
        sections: [
            { key: 'personal-information', title: 'Personal Information', completed: isSectionComplete('personal-information') },
            { key: 'professional-ids', title: 'Professional IDs', completed: isSectionComplete('professional-ids') },
        ],
    },
    {
        key: 'credentials-practice',
        title: 'Credentials & Practice',
        sections: [
            { key: 'education-training', title: 'Education & Training', completed: isSectionComplete('education-training') },
            { key: 'specialties', title: 'Specialties', completed: isSectionComplete('specialties') },
            { key: 'practice-locations', title: 'Practice Locations', completed: isSectionComplete('practice-locations') },
            { key: 'hospital-affiliations', title: 'Hospital Affiliations', completed: isSectionComplete('hospital-affiliations') },
            { key: 'credentialing-contacts', title: 'Credentialing Contacts', completed: isSectionComplete('credentialing-contacts') },
            { key: 'professional-liability', title: 'Professional Liability Insurance', completed: isSectionComplete('professional-liability') },
        ],
    },
    {
        key: 'history-attestation',
        title: 'History & Attestation',
        sections: [
            { key: 'employment', title: 'Employment Information', completed: isSectionComplete('employment') },
            { key: 'references', title: 'Professional References', completed: isSectionComplete('references') },
            { key: 'disclosure', title: 'Disclosure', completed: isSectionComplete('disclosure') },
        ],
    },
    {
        key: 'documents',
        title: 'Documents',
        sections: [
            { key: 'documents', title: 'Documents', completed: isSectionComplete('documents') },
        ],
    },
    {
        key: 'compliance',
        title: 'Compliance',
        sections: [
            { key: 'authorization', title: 'Authorization', completed: false },
            { key: 'caqh', title: 'CAQH Profile Tracking', completed: false },
        ],
    },
];

function isSectionComplete(key: string): boolean {
    if (key === 'personal-information') {
        const p = props.provider;
        return !!(p.first_name && p.last_name && p.email);
    }
    if (key === 'professional-ids') {
        const identifiers = props.provider.identifiers ?? [];
        return identifiers.length > 0;
    }
    if (key === 'education-training') {
        const educations = props.provider.educations ?? [];
        return educations.length > 0;
    }
    if (key === 'specialties') {
        const specialties = props.provider.specialties ?? [];
        return specialties.length > 0;
    }
    if (key === 'practice-locations') {
        const locations = props.provider.locations ?? [];
        return locations.length > 0;
    }
    if (key === 'hospital-affiliations') {
        const affiliations = props.provider.hospital_affiliations ?? [];
        return affiliations.length > 0;
    }
    if (key === 'credentialing-contacts') {
        const contacts = props.provider.contacts ?? [];
        return contacts.length > 0;
    }
    if (key === 'professional-liability') {
        const insurances = props.provider.insurances ?? [];
        return insurances.length > 0;
    }
    if (key === 'employment') {
        const employments = props.provider.employments ?? [];
        return employments.length > 0;
    }
    if (key === 'references') {
        const references = props.provider.references ?? [];
        return references.length > 0;
    }
    if (key === 'disclosure') {
        const questions = props.disclosureQuestions ?? [];

        if (questions.length === 0) {
            return false;
        }

        const answered = new Set(
            (props.provider.disclosures ?? []).map(
                (disclosure: any) => disclosure.disclosure_question_id,
            ),
        );
        const required = questions.filter(
            (question: any) => question.is_required,
        );

        return (
            required.length > 0 &&
            required.every((question: any) => answered.has(question.id))
        );
    }
    if (key === 'documents') {
        const documents = props.provider.documents ?? [];

        return documents.some((document: any) => document.is_current);
    }
    return false;
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Providers', href: providersIndex() },
            { title: 'Profile', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`${provider.full_name} - Profile`" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1.5">
                <h1 class="text-2xl font-semibold tracking-tight">
                    Provider Profile
                </h1>
                <p class="text-sm text-muted-foreground">
                    Manage profile sections for {{ provider.full_name }}
                </p>
            </div>
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    @click="router.visit(providersIndex())"
                >
                    <ArrowLeft class="size-4" />
                    Back to Providers
                </Button>
                <Button @click="router.visit(`/providers/${provider.id}/edit`)">
                    Edit Provider
                </Button>
            </div>
        </div>

        <ProviderProfileLayout
            :provider="provider"
            :active-section="activeSection"
            :expanded-group="expandedGroup"
            :section-groups="sectionGroups"
            @section-change="activeSection = $event"
            @group-toggle="expandedGroup = $event"
        >
            <template #content>
                <div v-if="activeSection === 'personal-information'">
                    <PersonalInformationSection
                        :provider="provider"
                        :personal-detail="provider.personalDetail"
                    />
                </div>
                <div v-else-if="activeSection === 'professional-ids'">
                    <ProviderIdentifierSection
                        :provider="provider"
                        :identifiers="provider.identifiers ?? []"
                    />
                </div>
                <div v-else-if="activeSection === 'education-training'">
                    <ProviderEducationSection
                        :provider="provider"
                        :educations="provider.educations ?? []"
                    />
                </div>
                <div v-else-if="activeSection === 'specialties'">
                    <ProviderSpecialtySection
                        :provider="provider"
                        :specialties="provider.specialties ?? []"
                        :available-specialties="availableSpecialties"
                    />
                </div>
                <div v-else-if="activeSection === 'practice-locations'">
                    <ProviderLocationSection
                        :provider="provider"
                        :locations="provider.locations ?? []"
                    />
                </div>
                <div v-else-if="activeSection === 'hospital-affiliations'">
                    <ProviderHospitalAffiliationSection
                        :provider="provider"
                        :affiliations="provider.hospital_affiliations ?? []"
                    />
                </div>
                <div v-else-if="activeSection === 'credentialing-contacts'">
                    <ProviderContactSection
                        :provider="provider"
                        :contacts="provider.contacts ?? []"
                    />
                </div>
                <div v-else-if="activeSection === 'professional-liability'">
                    <ProviderInsuranceSection
                        :provider="provider"
                        :insurances="provider.insurances ?? []"
                    />
                </div>
                <div v-else-if="activeSection === 'employment'">
                    <ProviderEmploymentSection
                        :provider="provider"
                        :employments="provider.employments ?? []"
                    />
                </div>
                <div v-else-if="activeSection === 'references'">
                    <ProviderReferenceSection
                        :provider="provider"
                        :references="provider.references ?? []"
                    />
                </div>
                <div v-else-if="activeSection === 'disclosure'">
                    <ProviderDisclosureSection
                        :provider="provider"
                        :disclosures="provider.disclosures ?? []"
                        :questions="disclosureQuestions ?? []"
                    />
                </div>
                <div v-else-if="activeSection === 'documents'">
                    <ProviderDocumentSection
                        :provider="provider"
                        :documents="provider.documents ?? []"
                    />
                </div>
                <div
                    v-else
                    class="flex h-full flex-col items-center justify-center p-8 text-neutral-500"
                >
                    <p>This section is coming soon.</p>
                </div>
            </template>
        </ProviderProfileLayout>
    </div>
</template>
