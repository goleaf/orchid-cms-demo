<?php

namespace App\Support\Website;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BranchLocationFilters
{
    /**
     * @return array{country: string, city: string}
     */
    public function fromRequest(Request $request): array
    {
        return [
            'country' => trim((string) $request->query('country', '')),
            'city' => trim((string) $request->query('city', '')),
        ];
    }

    /**
     * @param  array{country: string, city: string}  $filters
     * @param  Collection<int, Branch>  $branches
     * @return array{country: string, city: string}
     */
    public function normalize(array $filters, Collection $branches): array
    {
        if (blank($filters['country']) || blank($filters['city'])) {
            return $filters;
        }

        $cityBelongsToCountry = $branches->contains(
            fn (Branch $branch): bool => $branch->country === $filters['country']
                && $branch->city === $filters['city'],
        );

        if (! $cityBelongsToCountry) {
            $filters['city'] = '';
        }

        return $filters;
    }

    /**
     * @param  array{country?: string, city?: string}  $filters
     */
    public function hasActive(array $filters): bool
    {
        return filled($filters['country'] ?? null) || filled($filters['city'] ?? null);
    }

    /**
     * @param  array{country?: string, city?: string}  $filters
     */
    public function applyLocation(Builder $query, array $filters): Builder
    {
        return $query
            ->when(filled($filters['country'] ?? null), fn (Builder $query): Builder => $query->where('country', $filters['country']))
            ->when(filled($filters['city'] ?? null), fn (Builder $query): Builder => $query->where('city', $filters['city']));
    }

    /**
     * @param  array{country?: string, city?: string}  $filters
     */
    public function applyPublicLocation(Builder $query, array $filters): Builder
    {
        return $this->applyLocation(
            $query
                ->where('is_active', true)
                ->where('is_visible_on_site', true),
            $filters,
        );
    }

    /**
     * @param  Collection<int, Branch>  $branches
     * @param  array{country?: string, city?: string}  $filters
     * @return Collection<int, Branch>
     */
    public function filterBranches(Collection $branches, array $filters): Collection
    {
        return $branches
            ->filter(fn (Branch $branch): bool => (blank($filters['country'] ?? null) || $branch->country === $filters['country'])
                && (blank($filters['city'] ?? null) || $branch->city === $filters['city']))
            ->values();
    }

    /**
     * @param  Collection<int, Branch>  $branches
     * @param  array{country?: string, city?: string}  $filters
     * @return array{
     *     countries: Collection<int, array{value: string, label: string}>,
     *     cities: Collection<int, array{value: string, label: string, country: string}>
     * }
     */
    public function options(Collection $branches, array $filters = []): array
    {
        $cityBranches = filled($filters['country'] ?? null)
            ? $branches->filter(fn (Branch $branch): bool => $branch->country === $filters['country'])
            : $branches;

        return [
            'countries' => $this->countryOptions($branches),
            'cities' => $this->cityOptions($cityBranches),
        ];
    }

    /**
     * @param  Collection<int, Branch>  $branches
     * @return Collection<int, array{value: string, label: string}>
     */
    public function countryOptions(Collection $branches): Collection
    {
        return $branches
            ->filter(fn (Branch $branch): bool => filled($branch->country))
            ->map(fn (Branch $branch): array => [
                'value' => (string) $branch->country,
                'label' => $branch->displayCountry(),
            ])
            ->unique('value')
            ->sortBy('label')
            ->values();
    }

    /**
     * @param  Collection<int, Branch>  $branches
     * @return Collection<int, array{value: string, label: string, country: string}>
     */
    public function cityOptions(Collection $branches): Collection
    {
        return $branches
            ->filter(fn (Branch $branch): bool => filled($branch->city))
            ->map(fn (Branch $branch): array => [
                'value' => (string) $branch->city,
                'label' => $branch->displayCity(),
                'country' => (string) $branch->country,
            ])
            ->unique(fn (array $option): string => $option['country'].'|'.$option['value'])
            ->sortBy('label')
            ->values();
    }

    /**
     * @param  Collection<int, Branch>  $branches
     * @param  array{country?: string, city?: string}  $filters
     */
    public function selectedBranch(Collection $branches, array $filters): ?Branch
    {
        if (! $this->hasActive($filters)) {
            return null;
        }

        return $this->filterBranches($branches, $filters)->first();
    }
}
