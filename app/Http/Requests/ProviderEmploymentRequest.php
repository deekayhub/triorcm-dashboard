<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderEmploymentRequest extends FormRequest
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
        return [
            'employer' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'locum', 'contract', 'other'])],
            'address' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['sometimes', 'boolean'],
            'gap_explanation' => ['nullable', 'string'],
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
            'employer' => 'employer',
            'employment_type' => 'employment type',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'is_current' => 'current employment',
            'gap_explanation' => 'gap explanation',
            'verification_status' => 'verification status',
        ];
    }
}
