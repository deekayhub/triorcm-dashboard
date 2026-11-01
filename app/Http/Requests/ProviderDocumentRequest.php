<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderDocumentRequest extends FormRequest
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
        $isStore = $this->isMethod('post');
        $providerRequired = $isStore && ! $this->route('provider');

        return [
            'provider_id' => [
                $providerRequired ? 'required' : 'nullable',
                'integer',
                Rule::exists('providers', 'id')->whereNull('deleted_at'),
            ],
            'provider_application_id' => [
                'nullable',
                'integer',
                Rule::exists('provider_applications', 'id'),
            ],
            'type' => [
                'required',
                Rule::in(['cv', 'license', 'dea', 'malpractice', 'education', 'certification', 'other']),
            ],
            'name' => ['required', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'file' => $isStore
                ? ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,tif,tiff,doc,docx']
                : ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,tif,tiff,doc,docx'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.max' => 'The document may not be larger than 10 MB.',
            'file.mimes' => 'The document must be a PDF, image (JPG, PNG, TIFF) or Word file.',
            'expiration_date.after_or_equal' => 'The expiration date must be on or after the issue date.',
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
            'provider_id' => 'provider',
            'provider_application_id' => 'application',
            'issue_date' => 'issue date',
            'expiration_date' => 'expiration date',
        ];
    }
}
