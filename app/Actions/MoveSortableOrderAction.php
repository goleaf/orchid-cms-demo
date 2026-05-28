<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MoveSortableOrderAction
{
    public const UP = 'up';

    public const DOWN = 'down';

    public function handle(Model $model, string $direction): void
    {
        $items = $this->orderedQuery($model::class)
            ->get()
            ->values();

        $currentIndex = $items->search(fn (Model $item): bool => $item->is($model));

        if ($currentIndex === false) {
            return;
        }

        $targetIndex = match ($direction) {
            self::UP => $currentIndex - 1,
            self::DOWN => $currentIndex + 1,
            default => $currentIndex,
        };

        if ($targetIndex < 0 || $targetIndex >= $items->count() || $targetIndex === $currentIndex) {
            return;
        }

        $orderedItems = $this->swap($items, $currentIndex, $targetIndex);

        DB::transaction(function () use ($orderedItems): void {
            $orderedItems->each(function (Model $item, int $index): void {
                $item->forceFill(['sort_order' => ($index + 1) * 10])->save();
            });
        });
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function orderedQuery(string $modelClass): Builder
    {
        /** @var Model $model */
        $model = new $modelClass;
        $query = $modelClass::query()->select([$model->getKeyName(), 'sort_order']);

        if (method_exists($modelClass, 'scopeOrdered')) {
            return $query->ordered();
        }

        return $query->orderBy('sort_order')->orderBy($model->getKeyName());
    }

    /**
     * @param  Collection<int, Model>  $items
     * @return Collection<int, Model>
     */
    private function swap(Collection $items, int $currentIndex, int $targetIndex): Collection
    {
        $orderedItems = $items->all();
        [$orderedItems[$currentIndex], $orderedItems[$targetIndex]] = [$orderedItems[$targetIndex], $orderedItems[$currentIndex]];

        return collect($orderedItems)->values();
    }
}
