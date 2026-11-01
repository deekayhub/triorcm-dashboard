<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { CheckCircle2, ClipboardCheck, Clock, XCircle } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps<{
    provider: any;
    disclosures: Array<any>;
    questions: Array<any>;
}>();

const { toast } = useToast();
const page = usePage();

type DisclosureResponse = {
    disclosure_question_id: number;
    response: string;
    explanation: string | null;
};

const form = useForm({
    responses: [] as DisclosureResponse[],
});

const responseOptions = [
    { value: 'yes', label: 'Yes' },
    { value: 'no', label: 'No' },
    { value: 'na', label: 'N/A' },
];

const answers = ref<Record<number, { response: string; explanation: string }>>(
    {},
);
const submittedIndex = ref<Record<number, number>>({});
const reviewingId = ref<number | null>(null);

function initialState(): Record<
    number,
    { response: string; explanation: string }
> {
    const state: Record<number, { response: string; explanation: string }> = {};

    for (const question of props.questions ?? []) {
        state[question.id] = { response: '', explanation: '' };
    }

    for (const disclosure of props.disclosures ?? []) {
        if (state[disclosure.disclosure_question_id]) {
            state[disclosure.disclosure_question_id] = {
                response: disclosure.response,
                explanation: disclosure.explanation ?? '',
            };
        }
    }

    return state;
}

answers.value = initialState();

const orderedQuestions = computed(() =>
    [...(props.questions ?? [])].sort(
        (a, b) => (a.order ?? 0) - (b.order ?? 0) || a.id - b.id,
    ),
);

const groupedQuestions = computed(() => {
    const groups: Array<{ category: string; questions: Array<any> }> = [];

    for (const question of orderedQuestions.value) {
        const existing = groups.find(
            (group) => group.category === question.category,
        );

        if (existing) {
            existing.questions.push(question);
        } else {
            groups.push({ category: question.category, questions: [question] });
        }
    }

    return groups;
});

const disclosuresByQuestion = computed(() => {
    const map: Record<number, any> = {};

    for (const disclosure of props.disclosures ?? []) {
        map[disclosure.disclosure_question_id] = disclosure;
    }

    return map;
});

const totalQuestions = computed(() => orderedQuestions.value.length);
const answeredCount = computed(
    () =>
        orderedQuestions.value.filter(
            (question) => answers.value[question.id]?.response,
        ).length,
);
const requiredOutstanding = computed(
    () =>
        orderedQuestions.value.filter(
            (question) =>
                question.is_required && !answers.value[question.id]?.response,
        ).length,
);
const pendingReview = computed(
    () =>
        (props.disclosures ?? []).filter(
            (disclosure) => disclosure.review_status === 'pending',
        ).length,
);
const progressPercent = computed(() =>
    totalQuestions.value === 0
        ? 0
        : Math.round((answeredCount.value / totalQuestions.value) * 100),
);

const canReview = computed(() => {
    const auth = page.props.auth as { permissions?: Array<string> } | undefined;

    return auth?.permissions?.includes('providers.viewSensitive') ?? false;
});

function setResponse(questionId: number, response: string) {
    if (!answers.value[questionId]) {
        answers.value[questionId] = { response: '', explanation: '' };
    }

    answers.value[questionId].response = response;
}

function disclosureFor(questionId: number) {
    return disclosuresByQuestion.value[questionId];
}

function responseButtonClass(questionId: number, value: string): string {
    return answers.value[questionId]?.response === value
        ? 'bg-primary text-primary-foreground'
        : 'bg-white text-neutral-600 hover:bg-neutral-50';
}

function errorFor(
    questionId: number,
    field: 'response' | 'explanation',
): string | undefined {
    const index = submittedIndex.value[questionId];

    if (index === undefined) {
        return undefined;
    }

    return (form.errors as Record<string, string>)[
        `responses.${index}.${field}`
    ];
}

function buildResponses(): DisclosureResponse[] {
    const responses: DisclosureResponse[] = [];
    const index: Record<number, number> = {};

    for (const question of orderedQuestions.value) {
        const answer = answers.value[question.id];

        if (!answer?.response) {
            continue;
        }

        index[question.id] = responses.length;
        responses.push({
            disclosure_question_id: question.id,
            response: answer.response,
            explanation:
                answer.explanation.trim() === ''
                    ? null
                    : answer.explanation.trim(),
        });
    }

    submittedIndex.value = index;

    return responses;
}

function save() {
    const responses = buildResponses();

    if (responses.length === 0) {
        toast({
            title: 'Nothing to save',
            description:
                'Answer at least one disclosure question before saving.',
            variant: 'error',
        });

        return;
    }

    form.responses = responses;
    form.patch(`/providers/${props.provider.id}/disclosures`, {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: 'Saved',
                description: 'Disclosure responses saved successfully.',
                variant: 'success',
            });
        },
        onError: () => {
            toast({
                title: 'Error',
                description:
                    'Failed to save disclosure responses. Please review the highlighted fields.',
                variant: 'error',
            });
        },
    });
}

function review(disclosureId: number, status: 'approved' | 'rejected') {
    reviewingId.value = disclosureId;

    router.patch(
        `/providers/${props.provider.id}/disclosures/${disclosureId}/review`,
        { review_status: status },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast({
                    title: status === 'approved' ? 'Approved' : 'Rejected',
                    description:
                        status === 'approved'
                            ? 'The disclosure response has been approved.'
                            : 'The disclosure response has been rejected.',
                    variant: 'success',
                });
            },
            onError: () => {
                toast({
                    title: 'Error',
                    description: 'Failed to record the review decision.',
                    variant: 'error',
                });
            },
            onFinish: () => {
                reviewingId.value = null;
            },
        },
    );
}

type BadgeVariant = 'default' | 'outline' | 'destructive' | 'secondary';

function reviewBadge(status: string): {
    label: string;
    variant: BadgeVariant;
    class: string;
} {
    switch (status) {
        case 'approved':
            return {
                label: 'Approved',
                variant: 'default',
                class: 'bg-emerald-100 text-emerald-700 border-emerald-200',
            };
        case 'rejected':
            return {
                label: 'Rejected',
                variant: 'destructive',
                class: '',
            };
        default:
            return {
                label: 'Pending review',
                variant: 'outline',
                class: 'bg-amber-100 text-amber-700 border-amber-200',
            };
    }
}

function formatDate(value?: string | null): string {
    return value ? new Date(value).toLocaleString() : '';
}
</script>

<template>
    <Head title="Disclosure" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-clip p-4 md:p-6">
        <div class="space-y-1.5">
            <h1 class="text-2xl font-semibold tracking-tight">Disclosure</h1>
            <p class="text-sm text-muted-foreground">
                Complete the credentialing questionnaire and record review
                decisions.
            </p>
        </div>

        <Card class="shadow-none">
            <CardHeader class="gap-4">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-1">
                        <CardTitle>Disclosure Questionnaire</CardTitle>
                        <CardDescription>
                            {{ answeredCount }} of
                            {{ totalQuestions }} question{{
                                totalQuestions === 1 ? '' : 's'
                            }}
                            answered
                        </CardDescription>
                    </div>
                    <Button
                        :disabled="form.processing || totalQuestions === 0"
                        @click="save"
                    >
                        <ClipboardCheck class="mr-2 size-4" />
                        {{ form.processing ? 'Saving…' : 'Save Responses' }}
                    </Button>
                </div>

                <div
                    v-if="totalQuestions > 0"
                    class="h-1.5 w-full overflow-hidden rounded-full bg-neutral-100"
                    role="progressbar"
                    :aria-valuenow="progressPercent"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    :aria-label="`${progressPercent}% of disclosure questions answered`"
                >
                    <div
                        class="h-full rounded-full bg-primary transition-all"
                        :style="{ width: `${progressPercent}%` }"
                    />
                </div>

                <div
                    class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-neutral-500"
                >
                    <span
                        v-if="requiredOutstanding > 0"
                        class="inline-flex items-center gap-1.5 text-amber-700"
                    >
                        <Clock class="size-3.5" />
                        {{ requiredOutstanding }} required question{{
                            requiredOutstanding === 1 ? '' : 's'
                        }}
                        unanswered
                    </span>
                    <span
                        v-else-if="totalQuestions > 0"
                        class="inline-flex items-center gap-1.5 text-emerald-700"
                    >
                        <CheckCircle2 class="size-3.5" />
                        All required questions answered
                    </span>
                    <span
                        v-if="pendingReview > 0"
                        class="inline-flex items-center gap-1.5"
                    >
                        <Clock class="size-3.5" />
                        {{ pendingReview }} response{{
                            pendingReview === 1 ? '' : 's'
                        }}
                        pending review
                    </span>
                </div>

                <p v-if="form.errors.responses" class="text-sm text-red-600">
                    {{ form.errors.responses }}
                </p>
            </CardHeader>

            <CardContent>
                <div
                    v-if="totalQuestions === 0"
                    class="flex flex-col items-center gap-2 rounded-lg border border-dashed border-neutral-200 p-10 text-center"
                >
                    <ClipboardCheck class="size-8 text-neutral-300" />
                    <p class="text-sm font-medium text-neutral-700">
                        No disclosure questions configured
                    </p>
                    <p class="text-sm text-neutral-500">
                        Add active questions to the disclosure questionnaire to
                        start collecting responses.
                    </p>
                </div>

                <div v-else class="space-y-6">
                    <section
                        v-for="group in groupedQuestions"
                        :key="group.category"
                        class="space-y-3"
                    >
                        <h2
                            class="text-[11px] font-semibold tracking-[0.14em] text-muted-foreground uppercase"
                        >
                            {{ group.category }}
                        </h2>

                        <div
                            class="divide-y rounded-lg border border-neutral-200"
                        >
                            <div
                                v-for="question in group.questions"
                                :key="question.id"
                                class="p-4 md:p-5"
                            >
                                <div
                                    class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                                >
                                    <div class="min-w-0 md:max-w-[58%]">
                                        <p
                                            class="text-sm font-medium text-neutral-900"
                                        >
                                            {{ question.question }}
                                            <span
                                                v-if="question.is_required"
                                                class="text-destructive"
                                                aria-hidden="true"
                                                >*</span
                                            >
                                        </p>
                                        <p
                                            class="mt-1 text-xs text-neutral-500"
                                        >
                                            {{
                                                question.is_required
                                                    ? 'Required'
                                                    : 'Optional'
                                            }}
                                            &bull; Answer Yes, No or N/A
                                        </p>
                                    </div>

                                    <div
                                        class="flex shrink-0 flex-col items-start gap-2 md:items-end"
                                    >
                                        <div
                                            class="inline-flex overflow-hidden rounded-md border border-neutral-200"
                                            role="group"
                                            :aria-label="question.question"
                                        >
                                            <button
                                                v-for="option in responseOptions"
                                                :key="option.value"
                                                type="button"
                                                class="border-r border-neutral-200 px-3 py-1.5 text-xs font-medium transition-colors last:border-r-0 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                                :class="
                                                    responseButtonClass(
                                                        question.id,
                                                        option.value,
                                                    )
                                                "
                                                :aria-pressed="
                                                    answers[question.id]
                                                        ?.response ===
                                                    option.value
                                                "
                                                @click="
                                                    setResponse(
                                                        question.id,
                                                        option.value,
                                                    )
                                                "
                                            >
                                                {{ option.label }}
                                            </button>
                                        </div>

                                        <Badge
                                            v-if="disclosureFor(question.id)"
                                            :variant="
                                                reviewBadge(
                                                    disclosureFor(question.id)
                                                        .review_status,
                                                ).variant
                                            "
                                            :class="
                                                reviewBadge(
                                                    disclosureFor(question.id)
                                                        .review_status,
                                                ).class
                                            "
                                        >
                                            {{
                                                reviewBadge(
                                                    disclosureFor(question.id)
                                                        .review_status,
                                                ).label
                                            }}
                                        </Badge>
                                    </div>
                                </div>

                                <InputError
                                    :message="errorFor(question.id, 'response')"
                                />

                                <div
                                    v-if="answers[question.id]?.response"
                                    class="mt-4 space-y-2"
                                >
                                    <Label :for="`explanation-${question.id}`">
                                        Explanation
                                        <span
                                            v-if="
                                                answers[question.id]
                                                    .response === 'yes'
                                            "
                                            class="text-destructive"
                                            >*</span
                                        >
                                    </Label>
                                    <Textarea
                                        :id="`explanation-${question.id}`"
                                        v-model="
                                            answers[question.id].explanation
                                        "
                                        rows="2"
                                        placeholder="Provide context for this answer"
                                    />
                                    <InputError
                                        :message="
                                            errorFor(question.id, 'explanation')
                                        "
                                    />
                                </div>

                                <div
                                    v-if="disclosureFor(question.id)"
                                    class="mt-3 flex flex-wrap items-center justify-between gap-3"
                                >
                                    <p class="text-xs text-neutral-500">
                                        <template
                                            v-if="
                                                disclosureFor(question.id)
                                                    .reviewed_by
                                            "
                                        >
                                            Reviewed by
                                            {{
                                                disclosureFor(question.id)
                                                    .reviewed_by
                                            }}
                                            on
                                            {{
                                                formatDate(
                                                    disclosureFor(question.id)
                                                        .reviewed_at,
                                                )
                                            }}
                                        </template>
                                        <template v-else>
                                            Awaiting review by a credentialing
                                            reviewer
                                        </template>
                                    </p>

                                    <div v-if="canReview" class="flex gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            :disabled="
                                                reviewingId ===
                                                disclosureFor(question.id).id
                                            "
                                            @click="
                                                review(
                                                    disclosureFor(question.id)
                                                        .id,
                                                    'rejected',
                                                )
                                            "
                                        >
                                            <XCircle class="mr-1.5 size-3.5" />
                                            Reject
                                        </Button>
                                        <Button
                                            size="sm"
                                            :disabled="
                                                reviewingId ===
                                                disclosureFor(question.id).id
                                            "
                                            @click="
                                                review(
                                                    disclosureFor(question.id)
                                                        .id,
                                                    'approved',
                                                )
                                            "
                                        >
                                            <CheckCircle2
                                                class="mr-1.5 size-3.5"
                                            />
                                            Approve
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-t pt-4"
                    >
                        <p class="text-xs text-neutral-500">
                            Saving a changed answer resets its review decision
                            to pending.
                        </p>
                        <Button :disabled="form.processing" @click="save">
                            <ClipboardCheck class="mr-2 size-4" />
                            {{ form.processing ? 'Saving…' : 'Save Responses' }}
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
