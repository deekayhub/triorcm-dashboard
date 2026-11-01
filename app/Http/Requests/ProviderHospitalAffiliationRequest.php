<?php

namespace App\Http\Requests;

use App\Models\Provider;
use App\Models\ProviderHospitalAffiliation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderHospitalAffiliationRequest extends FormRequest
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
        $affiliation = $this->route('providerHospitalAffiliation');

        return [
            'provider_id' => ['sometimes', 'required', 'exists:providers,id'],
            'facility_name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'affiliation_type' => ['required', Rule::in(['admitting', 'courtesy', 'consulting', 'active', 'affiliate', 'other'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'terminated'])],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'evidence_document_id' => ['nullable', 'exists:provider_documents,id'],
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
            'facility_name' => 'facility name',
            'affiliation_type' => 'affiliation type',
            'contact_phone' => 'contact phone',
            'contact_email' => 'contact email',
            'evidence_document_id' => 'evidence document',
        ];
    }
}
