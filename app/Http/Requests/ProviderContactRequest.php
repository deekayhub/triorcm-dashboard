<?php

namespace App\Http\Requests;

use App\Models\Provider;
use App\Models\ProviderContact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderContactRequest extends FormRequest
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
        $contact = $this->route('providerContact');

        return [
            'provider_id' => ['sometimes', 'required', 'exists:providers,id'],
            'contact_name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'contact_type' => ['required', Rule::in(['credentialing', 'billing', 'practice_manager', 'other'])],
            'preferred_method' => ['required', Rule::in(['email', 'phone', 'mail'])],
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
            'contact_name' => 'contact name',
            'contact_type' => 'contact type',
            'preferred_method' => 'preferred contact method',
        ];
    }
}
