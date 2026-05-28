<?php

namespace App\Rules;

use App\Support\Crm\LeadDictionaryRegistry;
use App\Support\Crm\LeadDictionaryUsage;
use App\Support\Students\StudentDictionaryRegistry;
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

        $definition = $this->definition();

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $item = $modelClass::query()->find($value);

        if ($item === null) {
            $fail(tkey((string) $definition['unavailable_key']));

            return;
        }

        if ((bool) $item->getAttribute('is_system')) {
            $fail(tkey((string) $definition['system_record_key']));

            return;
        }

        if ((bool) $item->getAttribute('is_default') && ! $this->anotherDefaultStatusExists($item)) {
            $fail(tkey((string) $definition['default_required_key']));

            return;
        }

        if ($this->isUsed($definition, $item)) {
            $fail(tkey((string) $definition['used_key']));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function definition(): array
    {
        if (array_key_exists($this->dictionary, LeadDictionaryRegistry::definitions())) {
            return [
                ...LeadDictionaryRegistry::definition($this->dictionary),
                'usage_relation' => null,
                'unavailable_key' => 'crm.validation.dictionary_record_unavailable',
                'system_record_key' => 'crm.validation.dictionary_system_record_locked',
                'default_required_key' => 'crm.validation.dictionary_default_status_required',
                'used_key' => 'crm.dictionaries.messages.cannot_delete_used_item',
            ];
        }

        return StudentDictionaryRegistry::definition($this->dictionary);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function isUsed(array $definition, Model $item): bool
    {
        if (array_key_exists($this->dictionary, LeadDictionaryRegistry::definitions())) {
            return app(LeadDictionaryUsage::class)->isUsed($this->dictionary, $item);
        }

        $relation = $definition['usage_relation'] ?? null;

        if (! is_string($relation) || ! method_exists($item, $relation)) {
            return false;
        }

        return $item->{$relation}()->exists();
    }

    private function anotherDefaultStatusExists(Model $item): bool
    {
        return $item::query()
            ->whereKeyNot($item->getKey())
            ->where('is_default', true)
            ->exists();
    }
}
