<?php

namespace App\Http\Requests;

use App\Models\Provider;
use App\Models\ProviderIdentifier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderIdentifierRequest extends FormRequest
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
        $identifier = $this->route('providerIdentifier');

        return [
            'provider_id' => ['sometimes', 'required', 'exists:providers,id'],
            'identifier_type' => ['required', Rule::in(['dea', 'taxonomy', 'clia', 'cds', 'other'])],
            'identifier_value' => [
                'required',
                'string',
                'max:100',
                Rule::unique(ProviderIdentifier::class, 'identifier_value')->ignore($identifier?->id),
            ],
            'issuing_state' => ['nullable', 'string', 'max:2'],
            'issue_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'verification_status' => ['required', Rule::in(['unverified', 'pending', 'verified', 'failed'])],
            'verification_notes' => ['nullable', 'string'],
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
            'identifier_type' => 'identifier type',
            'identifier_value' => 'identifier value',
            'issuing_state' => 'issuing state',
            'expiration_date' => 'expiration date',
            'verification_status' => 'verification status',
            'verification_notes' => 'verification notes',
        ];
    }
}
