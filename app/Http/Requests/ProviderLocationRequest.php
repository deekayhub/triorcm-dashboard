<?php

namespace App\Http\Requests;

use App\Models\Provider;
use App\Models\ProviderLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderLocationRequest extends FormRequest
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
        $location = $this->route('providerLocation');

        return [
            'provider_id' => ['sometimes', 'required', 'exists:providers,id'],
            'location_name' => ['required', 'string', 'max:255'],
            'practice_name' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:2'],
            'phone' => ['nullable', 'string', 'max:20'],
            'fax' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'office_hours' => ['nullable', 'array'],
            'is_primary' => ['sometimes', 'boolean'],
            'is_billing_address' => ['sometimes', 'boolean'],
            'effective_start_date' => ['nullable', 'date'],
            'effective_end_date' => ['nullable', 'date', 'after_or_equal:effective_start_date'],
            'is_active' => ['sometimes', 'boolean'],
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
            'location_name' => 'location name',
            'zip_code' => 'ZIP code',
            'is_primary' => 'primary location',
            'is_billing_address' => 'billing address',
            'effective_start_date' => 'effective start date',
            'effective_end_date' => 'effective end date',
        ];
    }
}
