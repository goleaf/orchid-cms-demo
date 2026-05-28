<?php

namespace App\Actions;

use App\Models\Faq;
use Illuminate\Support\Collection;

class MoveFaqOrderAction
{
    public const UP = 'up';

    public const DOWN = 'down';

    public function handle(Faq $faq, string $direction): void
    {
        $items = Faq::query()
            ->select(['id', 'uuid'])
            ->ordered()
            ->get()
            ->values();

        $currentIndex = $items->search(fn (Faq $item): bool => $item->is($faq));

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
        $now = now();

        Faq::query()->upsert(
            $orderedItems
                ->map(fn (Faq $item, int $index): array => [
                    'id' => $item->getKey(),
                    'uuid' => $item->uuid,
                    'sort_order' => ($index + 1) * 10,
                    'updated_at' => $now,
                ])
                ->all(),
            ['id'],
            ['sort_order', 'updated_at'],
        );
    }

    /**
     * @param  Collection<int, Faq>  $items
     * @return Collection<int, Faq>
     */
    private function swap(Collection $items, int $currentIndex, int $targetIndex): Collection
    {
        $orderedItems = $items->all();
        [$orderedItems[$currentIndex], $orderedItems[$targetIndex]] = [$orderedItems[$targetIndex], $orderedItems[$currentIndex]];

        return collect($orderedItems)->values();
    }
}
