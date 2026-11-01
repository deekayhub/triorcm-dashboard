<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderDisclosureRequest;
use App\Http\Requests\ProviderDisclosureReviewRequest;
use App\Models\Provider;
use App\Models\ProviderDisclosure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProviderDisclosureController extends Controller
{
    /**
     * Store or update the provider's disclosure questionnaire responses.
     */
    public function save(ProviderDisclosureRequest $request, Provider $provider): RedirectResponse
    {
        $responses = collect($request->validated('responses'));
        $changed = 0;

        DB::transaction(function () use ($provider, $responses, $request, &$changed) {
            foreach ($responses as $item) {
                $explanation = $item['explanation'] ?? null;

                $existing = $provider->disclosures()
                    ->withTrashed()
                    ->where('disclosure_question_id', $item['disclosure_question_id'])
                    ->first();

                $isUnchanged = $existing
                    && ! $existing->trashed()
                    && $existing->response === $item['response']
                    && $existing->explanation === $explanation;

                if ($isUnchanged) {
                    continue;
                }

                $payload = [
                    'response' => $item['response'],
                    'explanation' => $explanation,
                    // Any change resets the review decision so the answer is re-reviewed.
                    'review_status' => 'pending',
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ];

                if ($existing) {
                    if ($existing->trashed()) {
                        $existing->restore();
                    }

                    $existing->update($payload);
                } else {
                    $provider->disclosures()->create($payload + [
                        'disclosure_question_id' => $item['disclosure_question_id'],
                    ]);
                }

                $changed++;
            }

            if ($changed > 0) {
                activity()
                    ->causedBy($request->user())
                    ->performedOn($provider)
                    ->event('updated')
                    ->log('Provider disclosure responses saved');
            }
        });

        $message = $changed > 0
            ? 'Disclosure responses saved successfully.'
            : 'No changes were made to the disclosure responses.';

        return redirect()->route('providers.profile', $provider)->with('success', $message);
    }

    /**
     * Record a review decision for a disclosure response.
     */
    public function review(
        ProviderDisclosureReviewRequest $request,
        Provider $provider,
        ProviderDisclosure $providerDisclosure,
    ): RedirectResponse {
        abort_unless($providerDisclosure->provider_id === $provider->id, 404);

        $providerDisclosure->update([
            'review_status' => $request->validated('review_status'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($providerDisclosure)
            ->event('updated')
            ->log('Provider disclosure reviewed');

        $message = $providerDisclosure->review_status === 'approved'
            ? 'Disclosure response approved.'
            : 'Disclosure response rejected.';

        return redirect()->route('providers.profile', $provider)->with('success', $message);
    }
}
