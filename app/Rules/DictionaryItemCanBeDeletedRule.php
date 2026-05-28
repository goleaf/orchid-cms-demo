<?php

namespace App\Rules;

use App\Support\Crm\LeadDictionaryRegistry;
use App\Support\Crm\LeadDictionaryUsage;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class DictionaryItemCanBeDeletedRule implements ValidationRule
{
    public function __construct(private readonly string $dictionary) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! filled($value)) {
            return;
        }

        $definition = LeadDictionaryRegistry::definition($this->dictionary);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $item = $modelClass::query()->find($value);

        if ($item === null) {
            $fail(tkey('crm.validation.dictionary_record_unavailable'));

            return;
        }

        if ((bool) $item->getAttribute('is_system')) {
            $fail(tkey('crm.validation.dictionary_system_record_locked'));

            return;
        }

        if ($this->dictionary === 'statuses' && (bool) $item->getAttribute('is_default') && ! $this->anotherDefaultStatusExists($item)) {
            $fail(tkey('crm.validation.dictionary_default_status_required'));

            return;
        }

        if (app(LeadDictionaryUsage::class)->isUsed($this->dictionary, $item)) {
            $fail(tkey('crm.dictionaries.messages.cannot_delete_used_item'));
        }
    }

    private function anotherDefaultStatusExists(Model $item): bool
    {
        return $item::query()
            ->whereKeyNot($item->getKey())
            ->where('is_default', true)
            ->exists();
    }
}
