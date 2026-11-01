<?php

namespace App\Http\Requests;

use App\Models\Provider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderUpdateRequest extends FormRequest
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
        $provider = $this->route('provider');

        return [
            'display_name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(Provider::class, 'email')->ignore($provider->id)],
            'npi' => ['nullable', 'string', 'max:10', Rule::unique(Provider::class, 'npi')->ignore($provider->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'fax' => ['nullable', 'string', 'max:20'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'provider_type' => ['required', Rule::in(['physician', 'nurse_practitioner', 'physician_assistant', 'other'])],
            'specialty' => ['nullable', 'string', 'max:255'],
            'taxonomy_code' => ['nullable', 'string', 'max:10'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'pending', 'suspended'])],
            'notes' => ['nullable', 'string'],
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
            'provider_type' => 'provider type',
            'taxonomy_code' => 'taxonomy code',
            'team_id' => 'team',
        ];
    }
}
