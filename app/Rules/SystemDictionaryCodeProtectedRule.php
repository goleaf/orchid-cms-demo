<?php

namespace App\Rules;

use App\Support\Crm\LeadDictionaryRegistry;
use App\Support\Students\StudentDictionaryRegistry;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class SystemDictionaryCodeProtectedRule implements ValidationRule
{
    public function __construct(
        private readonly string $dictionary,
        private readonly ?int $recordId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->recordId === null) {
            return;
        }

        $definition = $this->definition();
        $keyColumn = (string) $definition['key_column'];

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $item = $modelClass::query()->find($this->recordId);

        if ($item === null || ! (bool) $item->getAttribute('is_system')) {
            return;
        }

        if ((string) $value !== (string) $item->getAttribute($keyColumn)) {
            $fail(tkey((string) $definition['system_code_key']));
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
                'system_code_key' => 'crm.validation.dictionary_system_code_locked',
            ];
        }

        return StudentDictionaryRegistry::definition($this->dictionary);
    }
}
