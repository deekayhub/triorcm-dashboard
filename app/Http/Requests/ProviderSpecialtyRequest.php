<?php

namespace App\Http\Requests;

use App\Models\Provider;
use App\Models\ProviderSpecialty;
use App\Models\Specialty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderSpecialtyRequest extends FormRequest
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
        $providerSpecialty = $this->route('providerSpecialty');

        return [
            'provider_id' => ['sometimes', 'required', 'exists:providers,id'],
            'specialty_id' => ['required', 'exists:specialties,id'],
            'is_primary' => ['sometimes', 'boolean'],
            'subspecialty' => ['nullable', 'string', 'max:255'],
            'is_board_certified' => ['sometimes', 'boolean'],
            'board_certification_date' => ['nullable', 'date'],
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
            'specialty_id' => 'specialty',
            'is_primary' => 'primary specialty',
            'subspecialty' => 'subspecialty',
            'is_board_certified' => 'board certified',
            'board_certification_date' => 'board certification date',
            'is_active' => 'active',
        ];
    }
}
