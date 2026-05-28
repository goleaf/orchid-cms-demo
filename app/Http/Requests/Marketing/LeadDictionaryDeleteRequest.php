<?php

namespace App\Http\Requests\Marketing;

use App\Rules\DictionaryItemCanBeDeletedRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LeadDictionaryDeleteRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'dictionary' => $this->input('dictionary', $this->route('dictionary')),
            'record' => $this->input('record', $this->route('record')),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->hasAccess('crm.leads.manage_dictionaries') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dictionary' => ['required', 'string'],
            'record' => [
                'required',
                'integer',
                new DictionaryItemCanBeDeletedRule($this->dictionaryName()),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'record.required' => tkey('crm.validation.dictionary_record_required'),
            'record.integer' => tkey('crm.validation.dictionary_record_unavailable'),
        ];
    }

    public function dictionaryName(): string
    {
        return (string) $this->input('dictionary');
    }

    public function recordId(): int
    {
        return (int) $this->validated('record');
    }
}
