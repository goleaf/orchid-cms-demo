<?php

namespace App\Actions;

use App\Models\LeadStatus;
use App\Support\Crm\LeadDictionaryRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class SaveLeadDictionaryAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(string $dictionary, int|string|null $record, array $data): Model
    {
        $definition = LeadDictionaryRegistry::definition($dictionary);

        /** @var class-string<Model> $modelClass */
        $modelClass = $definition['model'];
        $item = filled($record)
            ? $modelClass::query()->findOrFail($record)
            : new $modelClass;
        $keyColumn = (string) $definition['key_column'];

        $this->assertSaveAllowed($dictionary, $item, $data, $keyColumn);

        $item->fill($data);
        $item->save();

        if ($modelClass === LeadStatus::class && (bool) $item->getAttribute('is_default')) {
            $modelClass::query()
                ->whereKeyNot($item->getKey())
                ->update(['is_default' => false]);
        }

        return $item->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertSaveAllowed(string $dictionary, Model $item, array $data, string $keyColumn): void
    {
        if ($item->exists && (bool) $item->getAttribute('is_system') && array_key_exists($keyColumn, $data)) {
            $originalKey = (string) $item->getAttribute($keyColumn);

            if ((string) $data[$keyColumn] !== $originalKey) {
                throw ValidationException::withMessages([
                    'item.'.$keyColumn => tkey('crm.validation.dictionary_system_code_locked'),
                ]);
            }
        }

        if ($dictionary !== 'statuses') {
            return;
        }

        $isDefault = (bool) ($data['is_default'] ?? false);
        $isActive = (bool) ($data['is_active'] ?? false);

        if ($isDefault && ! $isActive) {
            throw ValidationException::withMessages([
                'item.is_default' => tkey('crm.validation.dictionary_default_status_inactive'),
            ]);
        }

        if ($item->exists && (bool) $item->getAttribute('is_default') && ! $isDefault && ! $this->anotherDefaultStatusExists($item)) {
            throw ValidationException::withMessages([
                'item.is_default' => tkey('crm.validation.dictionary_default_status_required'),
            ]);
        }

        if ($item->exists && (bool) $item->getAttribute('is_system') && (bool) $item->getAttribute('is_final')) {
            if (array_key_exists('is_final', $data) && ! (bool) $data['is_final']) {
                throw ValidationException::withMessages([
                    'item.is_final' => tkey('crm.validation.dictionary_final_status_locked'),
                ]);
            }

            if (array_key_exists('is_active', $data) && ! (bool) $data['is_active']) {
                throw ValidationException::withMessages([
                    'item.is_active' => tkey('crm.validation.dictionary_final_status_locked'),
                ]);
            }
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
