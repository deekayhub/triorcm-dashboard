<?php

namespace App\Http\Requests;

use App\Models\Provider;
use App\Models\ProviderEducation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderEducationRequest extends FormRequest
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
        $education = $this->route('providerEducation');

        return [
            'provider_id' => ['sometimes', 'required', 'exists:providers,id'],
            'institution_name' => ['required', 'string', 'max:255'],
            'degree' => ['required', 'string', 'max:255'],
            'education_type' => ['required', Rule::in(['medical_school', 'residency', 'fellowship', 'internship', 'other_training'])],
            'specialty' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'completion_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'country' => ['nullable', 'string', 'max:2'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'verification_status' => ['required', Rule::in(['unverified', 'pending', 'verified', 'failed'])],
            'document_id' => ['nullable', 'exists:provider_documents,id'],
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
            'institution_name' => 'institution name',
            'education_type' => 'education type',
            'completion_date' => 'completion date',
            'verification_status' => 'verification status',
        ];
    }
}
