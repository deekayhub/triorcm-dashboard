<?php

namespace App\Http\Requests;

use App\Models\Provider;
use App\Models\ProviderInsurance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderInsuranceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('providers.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $insurance = $this->route('providerInsurance');

        return [
            'provider_id' => ['sometimes', 'required', 'exists:providers,id'],
            'carrier' => ['required', 'string', 'max:255'],
            'policy_number' => ['required', 'string', 'max:100'],
            'coverage_type' => ['required', Rule::in(['claims_made', 'occurrence'])],
            'per_claim_amount' => ['nullable', 'numeric', 'min:0'],
            'aggregate_amount' => ['nullable', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
            'expiration_date' => ['required', 'date', 'after_or_equal:effective_date'],
            'agent_contact' => ['nullable', 'string', 'max:255'],
            'agent_phone' => ['nullable', 'string', 'max:20'],
            'document_id' => ['nullable', 'exists:provider_documents,id'],
            'verification_status' => ['required', Rule::in(['unverified', 'pending', 'verified', 'failed'])],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'carrier' => 'insurance carrier',
            'policy_number' => 'policy number',
            'coverage_type' => 'coverage type',
            'per_claim_amount' => 'per-claim coverage amount',
            'aggregate_amount' => 'aggregate coverage amount',
            'effective_date' => 'effective date',
            'expiration_date' => 'expiration date',
            'agent_contact' => 'agent contact',
            'agent_phone' => 'agent phone',
            'document_id' => 'supporting document',
            'verification_status' => 'verification status',
        ];
    }
}
