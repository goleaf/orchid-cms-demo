<?php

namespace App\Http\Requests\Marketing;

use App\Services\TranslatableContentManager;
use App\Support\Crm\LeadDictionaryRegistry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadDictionaryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'dictionary' => $this->route('dictionary'),
            'record' => $this->route('record'),
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
        $definition = LeadDictionaryRegistry::definition($this->dictionaryName());
        $keyColumn = (string) $definition['key_column'];

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $prototype = new $modelClass;

        return [
            'item.'.$keyColumn => [
                'required',
                'string',
                'max:120',
                Rule::unique($prototype->getTable(), $keyColumn)->ignore($this->recordId()),
            ],
            'item.name' => ['nullable', 'string', 'max:255'],
            'item.color' => ['nullable', 'string', 'max:32'],
            'item.is_active' => ['nullable', 'boolean'],
            'item.is_public' => ['nullable', 'boolean'],
            'item.is_default' => ['nullable', 'boolean'],
            'item.is_final' => ['nullable', 'boolean'],
            'item.is_success' => ['nullable', 'boolean'],
            'item.is_lost' => ['nullable', 'boolean'],
            'item.is_duplicate' => ['nullable', 'boolean'],
            'item.is_spam' => ['nullable', 'boolean'],
            'item.sort_order' => ['required', 'integer', 'min:0'],
            ...app(TranslatableContentManager::class)->validationRules(['name'], ['nullable', 'string', 'max:255']),
            ...app(TranslatableContentManager::class)->validationRules(['description'], ['nullable', 'string', 'max:1000']),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $keyColumn = (string) LeadDictionaryRegistry::definition($this->dictionaryName())['key_column'];

        return [
            'item.'.$keyColumn.'.required' => tkey('crm.validation.dictionary_key_required'),
            'item.'.$keyColumn.'.max' => tkey('crm.validation.dictionary_key_too_long'),
            'item.'.$keyColumn.'.unique' => tkey('crm.validation.dictionary_key_unique'),
            'item.name.max' => tkey('crm.validation.dictionary_name_too_long'),
            'item.color.max' => tkey('crm.validation.dictionary_color_too_long'),
            'item.sort_order.required' => tkey('crm.validation.dictionary_sort_order_required'),
            'item.sort_order.integer' => tkey('crm.validation.dictionary_sort_order_invalid'),
            'item.sort_order.min' => tkey('crm.validation.dictionary_sort_order_invalid'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dictionaryData(): array
    {
        $definition = LeadDictionaryRegistry::definition($this->dictionaryName());
        $keyColumn = (string) $definition['key_column'];
        $data = $this->validated();
        $item = $data['item'];
        $translations = app(TranslatableContentManager::class);

        $payload = [
            $keyColumn => $item[$keyColumn],
            'name' => $item['name'] ?? null,
            'color' => $item['color'] ?? null,
            ...$translations->extract($this, ['name']),
            ...$translations->extract($this, ['description']),
            'is_active' => (bool) ($item['is_active'] ?? false),
            'sort_order' => (int) $item['sort_order'],
        ];

        if ($this->dictionaryName() === 'statuses') {
            $payload = [
                ...$payload,
                'is_default' => (bool) ($item['is_default'] ?? false),
                'is_final' => (bool) ($item['is_final'] ?? false),
                'is_success' => (bool) ($item['is_success'] ?? false),
                'is_lost' => (bool) ($item['is_lost'] ?? false),
                'is_public' => (bool) ($item['is_public'] ?? false),
                'is_duplicate' => (bool) ($item['is_duplicate'] ?? false),
                'is_spam' => (bool) ($item['is_spam'] ?? false),
            ];
        }

        return $payload;
    }

    public function dictionaryName(): string
    {
        return (string) $this->input('dictionary');
    }

    public function recordId(): ?int
    {
        $record = $this->input('record');

        return filled($record) ? (int) $record : null;
    }
}
