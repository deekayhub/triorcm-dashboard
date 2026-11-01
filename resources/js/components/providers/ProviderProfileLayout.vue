<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Sidebar,
    SidebarContent,
    SidebarMenu,
    SidebarMenuItem,
    SidebarMenuButton,
    SidebarMenuSub,
    SidebarMenuSubItem,
    SidebarMenuSubButton,
} from '@/components/ui/sidebar';
import {
    User,
    Briefcase,
    ClipboardList,
    FileText,
    Shield,
    ChevronRight,
    ChevronDown,
    FileUser,
    MapPin,
    Stethoscope,
    Building,
    LayoutGrid,
    ClipboardCheck,
} from '@lucide/vue';

const props = defineProps<{
    provider: any;
    activeSection: string;
    expandedGroup: string | null;
    sectionGroups: Array<{
        key: string;
        title: string;
        sections: Array<{
            key: string;
            title: string;
            completed: boolean;
        }>;
    }>;
}>();

const emit = defineEmits(['section-change', 'group-toggle']);

const sectionIcons: Record<string, any> = {
    'personal-information': FileUser,
    'professional-ids': Stethoscope,
    'education-training': LayoutGrid,
    specialties: Stethoscope,
    'practice-locations': MapPin,
    'hospital-affiliations': Building,
    'credentialing-contacts': FileUser,
    'professional-liability': Stethoscope,
    employment: LayoutGrid,
    references: FileUser,
    disclosure: ClipboardCheck,
    documents: FileText,
    authorization: Stethoscope,
    caqh: LayoutGrid,
};

const groupIcons: Record<string, any> = {
    'profile-identity': User,
    'credentials-practice': Briefcase,
    'history-attestation': ClipboardList,
    documents: FileText,
    compliance: Shield,
};

const getSectionIcon = (key: string) => {
    return sectionIcons[key] || LayoutGrid;
};

const getGroupIcon = (key: string) => {
    return groupIcons[key] || LayoutGrid;
};

function toggleGroup(key: string) {
    emit('group-toggle', props.expandedGroup === key ? null : key);
}

function isGroupExpanded(key: string): boolean {
    return props.expandedGroup === key;
}

function getGroupCompletedCount(group: any): number {
    return group.sections.filter((s: any) => s.completed).length;
}

function getGroupTotalCount(group: any): number {
    return group.sections.length;
}

function isGroupComplete(group: any): boolean {
    return (
        group.sections.length > 0 &&
        group.sections.every((s: any) => s.completed)
    );
}
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <!-- Provider Header -->
        <div
            class="flex flex-col items-center justify-between gap-4 md:flex-row"
        >
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ provider.full_name }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ provider.email }}
                </p>
                <p class="text-xs text-muted-foreground">
                    NPI: {{ provider.npi || '-' }} &bull;
                    {{
                        provider.provider_type
                            ? provider.provider_type.replace('_', ' ')
                            : '-'
                    }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Badge variant="outline" class="capitalize">{{
                    provider.status
                }}</Badge>
            </div>
        </div>

        <div class="flex h-full min-h-[600px] gap-6">
            <!-- Left Sidebar: Section Navigation -->
            <aside class="flex w-72 shrink-0 flex-col">
                <div
                    class="mb-4 flex items-center gap-2 rounded-lg border border-border bg-neutral-50 px-3 py-2 text-sm font-medium text-neutral-900"
                >
                    <span>Sections</span>
                </div>
                <Sidebar>
                    <SidebarContent>
                        <SidebarMenu>
                            <SidebarMenuItem
                                v-for="group in sectionGroups"
                                :key="group.key"
                                class="mb-1"
                            >
                                <SidebarMenuButton
                                    as-child
                                    :is-active="activeSection === group.key"
                                    :class="[
                                        'transition-all',
                                        activeSection === group.key
                                            ? 'bg-primary text-primary-foreground'
                                            : 'text-neutral-600 hover:bg-neutral-100',
                                    ]"
                                    @click="toggleGroup(group.key)"
                                >
                                    <component
                                        :is="getGroupIcon(group.key)"
                                        class="size-4 shrink-0"
                                    />
                                    <span class="ml-2 leading-none truncate">
                                        {{ group.title }}
                                    </span>
                                    <span class="ml-auto text-xs font-medium text-neutral-400">
                                        {{
                                            getGroupCompletedCount(group)
                                        }}/{{
                                            getGroupTotalCount(group)
                                        }}
                                    </span>
                                    <component
                                        :is="
                                            isGroupExpanded(group.key)
                                                ? ChevronDown
                                                : ChevronRight
                                        "
                                        class="ml-1 size-3.5 shrink-0 transition-transform duration-200"
                                    />
                                </SidebarMenuButton>

                                <SidebarMenuSub
                                    v-if="isGroupExpanded(group.key)"
                                    class="mt-1"
                                >
                                    <SidebarMenuSubItem
                                        v-for="section in group.sections"
                                        :key="section.key"
                                    >
                                        <SidebarMenuSubButton
                                            as-child
                                            :is-active="
                                                activeSection === section.key
                                            "
                                            :class="[
                                                'transition-all',
                                                activeSection === section.key
                                                    ? 'bg-primary text-primary-foreground'
                                                    : 'text-neutral-500 hover:bg-neutral-100',
                                            ]"
                                            @click="
                                                emit(
                                                    'section-change',
                                                    section.key
                                                )
                                            "
                                        >
                                            <component
                                                :is="getSectionIcon(
                                                    section.key
                                                )"
                                                class="size-3.5 shrink-0"
                                            />
                                            <span class="ml-2 leading-none truncate">
                                                {{ section.title }}
                                            </span>
                                            <span
                                                v-if="section.completed"
                                                class="ml-auto inline-flex items-center justify-center rounded-full bg-emerald-100 px-2 py-0 text-xs font-medium text-emerald-700"
                                            >
                                                Complete
                                            </span>
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>
                                </SidebarMenuSub>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarContent>
                </Sidebar>
            </aside>

            <!-- Right Content Panel -->
            <div class="flex min-w-0 flex-1 flex-col">
                <slot name="content" />
            </div>
        </div>
    </div>
</template>
