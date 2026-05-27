<?php

namespace App\Actions;

use App\Models\LeadStatus;
use App\Support\Crm\LeadDictionaryRegistry;
use Illuminate\Database\Eloquent\Model;

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

        $item->fill($data);
        $item->save();

        if ($modelClass === LeadStatus::class && (bool) $item->getAttribute('is_default')) {
            $modelClass::query()
                ->whereKeyNot($item->getKey())
                ->update(['is_default' => false]);
        }

        return $item->refresh();
    }
}
