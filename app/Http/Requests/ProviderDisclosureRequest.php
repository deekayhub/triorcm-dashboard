<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderDisclosureRequest extends FormRequest
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
            'responses' => ['required', 'array', 'min:1'],
            'responses.*.disclosure_question_id' => [
                'required',
                'integer',
                Rule::exists('disclosure_questions', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'responses.*.response' => ['required', Rule::in(['yes', 'no', 'na'])],
            'responses.*.explanation' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Configure the validator instance with cross-field rules.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $responses = $this->input('responses', []);

            $questionIds = collect($responses)
                ->pluck('disclosure_question_id')
                ->filter()
                ->all();

            if (count($questionIds) !== count(array_unique($questionIds))) {
                $validator->errors()->add('responses', 'Each disclosure question may only be answered once.');
            }

            foreach ($responses as $index => $response) {
                if (($response['response'] ?? null) === 'yes' && blank($response['explanation'] ?? null)) {
                    $validator->errors()->add(
                        "responses.{$index}.explanation",
                        'An explanation is required when the answer is Yes.',
                    );
                }
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'responses' => 'disclosure responses',
            'responses.*.disclosure_question_id' => 'disclosure question',
            'responses.*.response' => 'response',
            'responses.*.explanation' => 'explanation',
        ];
    }
}
